<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItemIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_item_id',
        'ingredient_id',
        'ingredient_name',
        'qty_base',
        'unit_cost',
        'cost_total',
    ];

    protected function casts(): array
    {
        return [
            'qty_base' => 'decimal:4',
            'unit_cost' => 'decimal:6',
            'cost_total' => 'integer',
        ];
    }

    public function transactionItem(): BelongsTo
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
