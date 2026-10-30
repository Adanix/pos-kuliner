<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(string $action, string $entityType, ?int $entityId = null, ?array $summary = null, ?string $reason = null, ?int $userId = null): void
    {
        try {
            AuditLog::create([
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'summary' => $summary,
                'reason' => $reason,
                'ip' => Request::ip(),
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
        } catch (\Throwable $e) {
            // Do not crash primary application if audit logging fails
        }
    }
}
