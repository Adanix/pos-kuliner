<?php

namespace App\Models;

use App\Enums\AiImportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel_selected',
        'channel_detected',
        'image_path',
        'image_hash',
        'image_bytes',
        'status',
        'platform_order_id',
        'extracted',
        'schema_version',
        'prompt_version',
        'model',
        'tokens_in',
        'tokens_out',
        'latency_ms',
        'error_code',
        'error_message',
        'duplicate_of_import_id',
        'transaction_id',
        'reviewed_by',
        'reviewed_at',
        'retention_delete_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiImportStatus::class,
            'extracted' => 'array',
            'reviewed_at' => 'datetime',
            'retention_delete_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_import_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getNormalizedExtractedAttribute(): array
    {
        $data = $this->extracted ?? [];
        if (! is_array($data)) {
            $data = [];
        }

        $items = $data['items'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }

        $normalizedItems = [];
        foreach ($items as $idx => $item) {
            if (! is_array($item)) {
                continue;
            }
            $sourceName = $item['source_name'] ?? $item['name'] ?? ('Item #' . ($idx + 1));
            $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 1);
            $price = (int) ($item['visible_unit_price'] ?? $item['price'] ?? 0);
            $lineTotal = (int) ($item['visible_line_total'] ?? ($qty * $price));

            $normalizedItems[] = array_merge($item, [
                'source_name' => $sourceName,
                'name' => $sourceName,
                'quantity' => $qty,
                'qty' => $qty,
                'visible_unit_price' => $price,
                'price' => $price,
                'visible_line_total' => $lineTotal,
            ]);
        }

        $data['items'] = $normalizedItems;
        $data['platform'] = $data['platform'] ?? $data['channel'] ?? $this->channel_detected ?? $this->channel_selected ?? 'gofood';
        $data['channel'] = $data['platform'];
        $data['platform_order_id'] = $data['platform_order_id'] ?? $data['order_id'] ?? $this->platform_order_id ?? '';
        $data['order_id'] = $data['platform_order_id'];
        $data['visible_subtotal'] = (int) ($data['visible_subtotal'] ?? $data['subtotal'] ?? 0);
        $data['subtotal'] = $data['visible_subtotal'];
        $data['visible_discount'] = (int) ($data['visible_discount'] ?? $data['discount'] ?? 0);
        $data['visible_order_total'] = (int) ($data['visible_order_total'] ?? $data['total'] ?? $data['net_amount'] ?? 0);

        return $data;
    }
}
