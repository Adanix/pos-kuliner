<?php

namespace App\Models;

use App\Enums\Channel;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'outlet_id',
        'channel',
        'status',
        'occurred_at',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'service_charge',
        'tax_amount',
        'rounding_amount',
        'total',
        'payment_method',
        'amount_paid',
        'change_amount',
        'platform_fee',
        'platform_order_id',
        'ai_import_id',
        'cash_session_id',
        'user_id',
        'idempotency_key',
        'note',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'status' => TransactionStatus::class,
            'payment_method' => PaymentMethod::class,
            'occurred_at' => 'datetime',
            'voided_at' => 'datetime',
            'subtotal' => 'integer',
            'discount_value' => 'integer',
            'discount_amount' => 'integer',
            'service_charge' => 'integer',
            'tax_amount' => 'integer',
            'rounding_amount' => 'integer',
            'total' => 'integer',
            'amount_paid' => 'integer',
            'change_amount' => 'integer',
            'platform_fee' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function aiImport(): BelongsTo
    {
        return $this->belongsTo(AiImport::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED;
    }

    public function isVoided(): bool
    {
        return $this->status === TransactionStatus::VOIDED;
    }
}
