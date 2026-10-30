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
}
