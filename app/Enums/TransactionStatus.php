<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case COMPLETED = 'completed';
    case VOIDED = 'voided';

    public function label(): string
    {
        return match($this) {
            self::COMPLETED => 'Selesai',
            self::VOIDED => 'Dibatalkan (Void)',
        };
    }
}
