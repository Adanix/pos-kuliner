<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Outlet;
use App\Services\SalesService;
use App\Services\ShiftService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function __construct(
        protected SalesService $salesService,
        protected ShiftService $shiftService
    ) {}

    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $menus = Menu::where('is_active', true)
            ->with(['category', 'recipe.items.ingredient'])
            ->orderBy('name')
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'category_id' => $menu->category_id,
                    'category_name' => $menu->category?->name ?? 'Umum',
                    'price' => $menu->price,
                    'formatted_price' => Money::format($menu->price),
                    'image_path' => $menu->image_path,
                    'description' => $menu->description,
                ];
            });

        $outlet = Outlet::first();
        $activeShift = $this->shiftService->getActiveSession();

        return view('pos.index', compact('categories', 'menus', 'outlet', 'activeShift'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:pos,gofood,shopeefood'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,other'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.line_discount' => ['nullable', 'integer', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['nullable', 'string', 'in:none,fixed,percent'],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'amount_paid' => ['nullable', 'integer', 'min:0'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Inject idempotency header if present
        if ($headerKey = $request->header('Idempotency-Key')) {
            $validated['idempotency_key'] = $headerKey;
        }

        try {
            $transaction = $this->salesService->storeTransaction($validated, auth()->id());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan.',
                    'transaction' => [
                        'id' => $transaction->id,
                        'number' => $transaction->number,
                        'total' => $transaction->total,
                        'formatted_total' => Money::format($transaction->total),
                        'change_amount' => $transaction->change_amount,
                        'formatted_change' => Money::format($transaction->change_amount),
                    ],
                ], 201);
            }

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Transaksi {$transaction->number} berhasil disimpan!");

        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
