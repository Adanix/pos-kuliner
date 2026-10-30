<?php

namespace App\Enums;

enum Channel: string
{
    case POS = 'pos';
    case GOFOOD = 'gofood';
    case SHOPEEFOOD = 'shopeefood';

    public function label(): string
    {
        return match($this) {
            self::POS => 'Dine-in / Kasir (POS)',
            self::GOFOOD => 'GoFood',
            self::SHOPEEFOOD => 'ShopeeFood',
        };
    }
}
