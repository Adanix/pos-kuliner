<?php

namespace App\Services\Ai;

use App\Models\AiImport;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class DuplicateDetector
{
    /**
     * Check if the image hash is already uploaded.
     */
    public function checkImageHash(string $imageHash): ?AiImport
    {
        return AiImport::where('image_hash', $imageHash)
            ->whereIn('status', ['ready', 'saved', 'needs_review'])
            ->first();
    }

    /**
     * Check if platform order ID already exists in transactions or saved imports.
     */
    public function checkPlatformOrderId(string $channel, ?string $platformOrderId): ?Transaction
    {
        if (! $platformOrderId) {
            return null;
        }

        return Transaction::where('channel', $channel)
            ->where('platform_order_id', $platformOrderId)
            ->first();
    }

    /**
     * Check similarity against recent transactions: channel + total + date (within 1 day).
     */
    public function checkSimilarity(string $channel, int $total, ?string $dateStr = null): array
    {
        $date = $dateStr ? Carbon::parse($dateStr, 'Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $start = $date->copy()->subDay()->startOfDay();
        $end = $date->copy()->addDay()->endOfDay();

        $candidates = Transaction::where('channel', $channel)
            ->whereBetween('occurred_at', [$start, $end])
            ->where('total', $total)
            ->get();

        $warnings = [];
        if ($candidates->isNotEmpty()) {
            foreach ($candidates as $cand) {
                $warnings[] = [
                    'transaction_number' => $cand->number,
                    'occurred_at' => $cand->occurred_at->format('d/m/Y H:i'),
                    'total' => $cand->total,
                    'note' => "Pesanan serupa ({$cand->number}) ditemukan pada tanggal yang berdekatan dengan total yang sama.",
                ];
            }
        }

        return $warnings;
    }
}
