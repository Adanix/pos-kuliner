<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\AuditService;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function __construct(protected SalesService $salesService) {}

    public function index(Request $request)
    {
        $query = Transaction::with(['items', 'user'])
            ->latest('occurred_at');

        if ($channel = $request->input('channel')) {
            $query->where('channel', $channel);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($date = $request->input('date')) {
            $parsedDate = Carbon::parse($date, 'Asia/Jakarta');
            $query->whereBetween('occurred_at', [
                $parsedDate->copy()->startOfDay(),
                $parsedDate->copy()->endOfDay(),
            ]);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('platform_order_id', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(25)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.itemIngredients.ingredient', 'user', 'voidedByUser', 'cashSession']);

        return view('transactions.show', compact('transaction'));
    }

    public function void(Request $request, Transaction $transaction)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'return_stock' => ['nullable', 'boolean'],
        ]);

        try {
            $returnStock = $request->boolean('return_stock');
            $voided = $this->salesService->voidTransaction($transaction, $request->reason, $returnStock, auth()->id());

            AuditService::log(
                'void_transaction',
                'Transaction',
                $voided->id,
                ['number' => $voided->number, 'total' => $voided->total, 'return_stock' => $returnStock],
                $request->reason
            );

            return back()->with('success', "Transaksi {$voided->number} berhasil dibatalkan (void).");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
