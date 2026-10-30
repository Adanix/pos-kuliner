<?php

namespace App\Http\Controllers;

use App\Enums\AiImportStatus;
use App\Jobs\ProcessAiImport;
use App\Models\AiImport;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\Ai\DuplicateDetector;
use App\Services\Ai\MenuMatcher;
use App\Services\SalesService;
use App\Support\Money;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiImportController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
        protected MenuMatcher $menuMatcher,
        protected DuplicateDetector $duplicateDetector
    ) {}

    public function index()
    {
        $imports = AiImport::with(['user', 'transaction'])
            ->latest()
            ->paginate(15);

        // Monthly quota calculation
        $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth();
        $monthlyUsage = AiImport::where('created_at', '>=', $startOfMonth)
            ->whereIn('status', [AiImportStatus::SAVED, AiImportStatus::READY, AiImportStatus::NEEDS_REVIEW])
            ->count();
        $monthlyQuota = (int) Setting::get('ai.monthly_quota', config('ai.monthly_quota', 300));

        return view('ai-imports.index', compact('imports', 'monthlyUsage', 'monthlyQuota'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'screenshot' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
            'channel' => ['nullable', 'string', 'in:auto,gofood,shopeefood'],
        ]);

        // Monthly quota check
        $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth();
        $monthlyUsage = AiImport::where('created_at', '>=', $startOfMonth)->count();
        $monthlyQuota = (int) Setting::get('ai.monthly_quota', config('ai.monthly_quota', 300));

        if ($monthlyUsage >= $monthlyQuota) {
            return back()->with('error', "Kuota pemakaian AI bulanan ({$monthlyQuota} impor) telah habis. Silakan input transaksi manual atau hubungi pemilik.");
        }

        $file = $request->file('screenshot');
        $bytes = $file->getSize();
        $contents = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $contents);

        // Duplicate check via image hash
        $duplicate = $this->duplicateDetector->checkImageHash($hash);

        $now = Carbon::now();
        $subDir = "private/ai-imports/{$now->format('Y')}/{$now->format('m')}";
        $ext = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $ext;
        $savedPath = $file->storeAs($subDir, $filename);
        $fullPath = storage_path('app/' . $savedPath);

        $aiImport = AiImport::create([
            'user_id' => auth()->id(),
            'channel_selected' => $request->input('channel') === 'auto' ? null : $request->input('channel'),
            'image_path' => $fullPath,
            'image_hash' => $hash,
            'image_bytes' => $bytes,
            'status' => $duplicate ? AiImportStatus::DUPLICATE : AiImportStatus::QUEUED,
            'duplicate_of_import_id' => $duplicate?->id,
            'retention_delete_at' => $now->copy()->addDays(config('ai.retention_days', 30)),
        ]);

        if ($duplicate) {
            return redirect()->route('impor.review', $aiImport)
                ->with('warning', "Gambar ini identik dengan impor sebelumnya (ID #{$duplicate->id}). Periksa kembali sebelum menyimpan.");
        }

        // Dispatch processing job immediately or synchronous fallback
        ProcessAiImport::dispatchSync($aiImport);

        return redirect()->route('impor.review', $aiImport)
            ->with('success', 'Gambar berhasil diunggah dan diekstrak oleh AI. Silakan periksa hasilnya.');
    }

    public function review(AiImport $aiImport)
    {
        $allMenus = Menu::where('is_active', true)->orderBy('name')->get();

        return view('ai-imports.review', compact('aiImport', 'allMenus'));
    }

    public function image(AiImport $aiImport)
    {
        if (! file_exists($aiImport->image_path)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        $mime = mime_content_type($aiImport->image_path);
        return response()->file($aiImport->image_path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-cache',
        ]);
    }

    public function saveTransaction(Request $request, AiImport $aiImport)
    {
        if ($aiImport->status === AiImportStatus::SAVED) {
            return redirect()->route('transactions.show', $aiImport->transaction_id)
                ->with('info', 'Impor ini sudah pernah disimpan sebagai transaksi.');
        }

        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:gofood,shopeefood'],
            'platform_order_id' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.source_name' => ['nullable', 'string'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // 1. Hard check: platform order ID unique
        if (! empty($validated['platform_order_id'])) {
            $existing = Transaction::where('channel', $validated['channel'])
                ->where('platform_order_id', $validated['platform_order_id'])
                ->first();

            if ($existing) {
                return back()->with('error', "Pesanan ID {$validated['platform_order_id']} sudah pernah tercatat pada transaksi {$existing->number}. Tidak dapat disimpan ganda.");
            }
        }

        // 2. Save aliases for manually mapped items
        foreach ($validated['items'] as $itemRow) {
            if (! empty($itemRow['source_name'])) {
                $this->menuMatcher->saveAlias($itemRow['menu_id'], $itemRow['source_name'], $validated['channel']);
            }
        }

        // 3. Assemble payload for SalesService
        $salesPayload = [
            'channel' => $validated['channel'],
            'payment_method' => 'other', // Online platform settlement
            'platform_order_id' => $validated['platform_order_id'] ?? null,
            'ai_import_id' => $aiImport->id,
            'discount_type' => ($validated['discount_amount'] ?? 0) > 0 ? 'fixed' : 'none',
            'discount_value' => $validated['discount_amount'] ?? 0,
            'items' => array_map(function ($row) {
                return [
                    'menu_id' => $row['menu_id'],
                    'qty' => $row['qty'],
                    'note' => $row['note'] ?? null,
                ];
            }, $validated['items']),
            'note' => $validated['note'] ?? ("Impor AI dari " . ucfirst($validated['channel']) . ($validated['platform_order_id'] ? " #" . $validated['platform_order_id'] : '')),
        ];

        try {
            $transaction = $this->salesService->storeTransaction($salesPayload, auth()->id());

            // Mark import as saved
            $aiImport->status = AiImportStatus::SAVED;
            $aiImport->transaction_id = $transaction->id;
            $aiImport->reviewed_by = auth()->id();
            $aiImport->reviewed_at = Carbon::now('Asia/Jakarta');
            $aiImport->save();

            // Option to delete screenshot immediately if privacy flag set
            if (config('ai.delete_after_save', false) && file_exists($aiImport->image_path)) {
                @unlink($aiImport->image_path);
                $aiImport->image_path = '[DELETED_ON_SAVE]';
                $aiImport->save();
            }

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Transaksi online {$transaction->number} berhasil disimpan!");

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }
}
