<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case QRIS = 'qris';
    case TRANSFER = 'transfer';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Tunai',
            self::QRIS => 'QRIS',
            self::TRANSFER => 'Transfer Bank',
            self::OTHER => 'Lainnya',
        };
    }
}
