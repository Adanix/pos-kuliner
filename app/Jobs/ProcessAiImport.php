<?php

namespace App\Jobs;

use App\Enums\AiImportStatus;
use App\Models\AiImport;
use App\Services\Ai\AiExtractionValidator;
use App\Services\Ai\CioraClient;
use App\Services\Ai\DuplicateDetector;
use App\Services\Ai\MenuMatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 90;

    public function __construct(public AiImport $aiImport)
    {
        $this->onQueue('ai-imports');
    }

    public function handle(
        CioraClient $client,
        AiExtractionValidator $validator,
        MenuMatcher $matcher,
        DuplicateDetector $duplicateDetector
    ): void {
        $import = $this->aiImport->fresh();
        if (! $import || $import->status === AiImportStatus::SAVED) {
            return;
        }

        $import->status = AiImportStatus::PROCESSING;
        $import->save();

        try {
            $extractedResponse = $client->extractFromImage($import->image_path);

            $dto = $validator->validateAndParse($extractedResponse['raw_json']);

            // Detect channel if user hadn't selected one
            $channel = $import->channel_selected;
            if (empty($channel) || $channel === 'auto') {
                $channel = ($dto->platform === 'shopeefood') ? 'shopeefood' : 'gofood';
            }
            $import->channel_detected = $dto->platform;

            // Check hard platform order duplicate
            if ($dto->platformOrderId) {
                $existingTx = $duplicateDetector->checkPlatformOrderId($channel, $dto->platformOrderId);
                if ($existingTx) {
                    $import->status = AiImportStatus::DUPLICATE;
                    $import->platform_order_id = $dto->platformOrderId;
                    $import->error_message = "Pesanan dengan ID Platform {$dto->platformOrderId} sudah pernah tercatat pada transaksi {$existingTx->number}.";
                    $import->save();
                    return;
                }
            }

            // Match items against master menus
            $matchedItems = [];
            foreach ($dto->items as $item) {
                $match = $matcher->matchItem($item['source_name'], $channel);
                $matchedItems[] = array_merge($item, [
                    'matched_menu_id' => $match['menu_id'],
                    'matched_menu_name' => $match['menu']?->name,
                    'master_price' => $match['menu']?->price,
                    'match_type' => $match['match_type'],
                    'suggestions' => $match['suggestions'],
                ]);
            }

            // Check similarity warnings
            $warnings = $duplicateDetector->checkSimilarity($channel, $dto->visibleOrderTotal, $dto->orderedAt);
            $reviewFlags = array_merge($dto->reviewFlags, ! empty($warnings) ? ['SIMILAR_ORDER_WARNING'] : []);

            $extractionPayload = array_merge($dto->toArray(), [
                'items' => $matchedItems,
                'similarity_warnings' => $warnings,
                'review_flags' => array_unique($reviewFlags),
            ]);

            $import->status = AiImportStatus::NEEDS_REVIEW;
            $import->platform_order_id = $dto->platformOrderId;
            $import->extracted = $extractionPayload;
            $import->model = $extractedResponse['model'] ?? null;
            $import->tokens_in = $extractedResponse['tokens_in'] ?? null;
            $import->tokens_out = $extractedResponse['tokens_out'] ?? null;
            $import->latency_ms = $extractedResponse['latency_ms'] ?? null;
            $import->save();

        } catch (\Throwable $e) {
            Log::error("AI Import Processing Failed [ID: {$import->id}]: " . $e->getMessage());
            $import->status = AiImportStatus::FAILED;
            $import->error_code = 'AI_PROCESSING_ERROR';
            $import->error_message = $e->getMessage();
            $import->save();
        }
    }
}
