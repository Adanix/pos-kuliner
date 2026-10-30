<?php

namespace App\Services;

use App\Models\Outlet;
use App\Models\Transaction;
use App\Support\Money;

class ReceiptService
{
    /**
     * Prepare printable payload for 58mm receipt.
     */
    public function formatReceipt(Transaction $transaction): array
    {
        $outlet = Outlet::first();
        $items = $transaction->items()->get();

        return [
            'outlet_name' => $outlet?->name ?? 'Ciora Kuliner',
            'outlet_address' => $outlet?->address ?? '',
            'outlet_phone' => $outlet?->phone ?? '',
            'receipt_footer' => $outlet?->receipt_footer ?? 'Terima kasih atas kunjungan Anda!',
            'transaction_number' => $transaction->number,
            'date_time' => $transaction->occurred_at ? $transaction->occurred_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '',
            'cashier_name' => $transaction->user?->name ?? 'Kasir',
            'channel' => $transaction->channel->label(),
            'items' => $items->map(function ($item) {
                return [
                    'name' => $item->menu_name,
                    'qty' => $item->qty,
                    'unit_price' => Money::format($item->unit_price),
                    'line_total' => Money::format($item->line_total),
                    'line_discount' => $item->line_discount > 0 ? Money::format($item->line_discount) : null,
                    'note' => $item->note,
                ];
            }),
            'subtotal' => Money::format($transaction->subtotal),
            'discount_amount' => $transaction->discount_amount > 0 ? Money::format($transaction->discount_amount) : null,
            'service_charge' => $transaction->service_charge > 0 ? Money::format($transaction->service_charge) : null,
            'tax_amount' => $transaction->tax_amount > 0 ? Money::format($transaction->tax_amount) : null,
            'rounding_amount' => $transaction->rounding_amount != 0 ? Money::format($transaction->rounding_amount) : null,
            'total' => Money::format($transaction->total),
            'payment_method' => $transaction->payment_method->label(),
            'amount_paid' => Money::format($transaction->amount_paid),
            'change_amount' => Money::format($transaction->change_amount),
            'is_voided' => $transaction->isVoided(),
            'void_reason' => $transaction->void_reason,
        ];
    }
}
