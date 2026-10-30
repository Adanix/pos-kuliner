<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'menu_id',
        'menu_name',
        'qty',
        'unit_price',
        'line_discount',
        'line_total',
        'unit_hpp',
        'hpp_complete',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'integer',
            'line_discount' => 'integer',
            'line_total' => 'integer',
            'unit_hpp' => 'integer',
            'hpp_complete' => 'boolean',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function itemIngredients(): HasMany
    {
        return $this->hasMany(TransactionItemIngredient::class);
    }
}
