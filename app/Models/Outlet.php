<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'receipt_footer',
        'tax_percent',
        'service_charge_percent',
        'rounding_mode',
        'require_open_shift',
        'allow_negative_stock',
    ];

    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
            'service_charge_percent' => 'decimal:2',
            'require_open_shift' => 'boolean',
            'allow_negative_stock' => 'boolean',
        ];
    }
}
