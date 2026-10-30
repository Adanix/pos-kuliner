<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(protected ReceiptService $receiptService) {}

    public function show(Transaction $transaction)
    {
        $receipt = $this->receiptService->formatReceipt($transaction);

        return view('transactions.receipt', compact('receipt', 'transaction'));
    }
}
