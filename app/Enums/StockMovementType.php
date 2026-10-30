<?php

namespace App\Enums;

enum StockMovementType: string
{
    case OPENING = 'opening';
    case PURCHASE = 'purchase';
    case USAGE = 'usage';
    case WASTE = 'waste';
    case ADJUSTMENT = 'adjustment';
    case REVERSAL = 'reversal';

    public function label(): string
    {
        return match($this) {
            self::OPENING => 'Saldo Awal',
            self::PURCHASE => 'Pembelian Masuk',
            self::USAGE => 'Pemakaian Penjualan',
            self::WASTE => 'Terbuang / Rusak (Waste)',
            self::ADJUSTMENT => 'Penyesuaian (Opname)',
            self::REVERSAL => 'Pengembalian Void',
        };
    }
}
