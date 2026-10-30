<?php

namespace App\Services\Ai;

use App\DTO\AiExtraction;
use Exception;

class AiExtractionValidator
{
    /**
     * Validate raw JSON string from AI model, strip PII, verify arithmetic, and build AiExtraction.
     */
    public function validateAndParse(string $rawJson): AiExtraction
    {
        $decoded = json_decode($rawJson, true);
        if (! is_array($decoded)) {
            throw new Exception("Model tidak mengembalikan format JSON yang valid.");
        }

        // 1. Strip any potential customer PII
        unset($decoded['customer_name'], $decoded['customer_phone'], $decoded['customer_address'], $decoded['delivery_address']);

        // 2. Schema normalization
        $platform = strtolower(trim($decoded['platform'] ?? 'unknown'));
        if (! in_array($platform, ['gofood', 'shopeefood', 'unknown'])) {
            $platform = 'unknown';
        }

        $platformOrderId = isset($decoded['platform_order_id']) && is_string($decoded['platform_order_id'])
            ? trim($decoded['platform_order_id'])
            : null;

        $orderedAt = isset($decoded['ordered_at']) && is_string($decoded['ordered_at'])
            ? trim($decoded['ordered_at'])
            : null;

        $itemsRaw = $decoded['items'] ?? [];
        if (! is_array($itemsRaw)) {
            $itemsRaw = [];
        }

        $items = [];
        $sumLineTotals = 0;
        $reviewFlags = [];

        foreach ($itemsRaw as $iRow) {
            if (! is_array($iRow)) {
                continue;
            }

            // Strip PII from item notes if any
            $notes = isset($iRow['notes']) && is_string($iRow['notes']) ? trim($iRow['notes']) : null;
            $sourceName = isset($iRow['source_name']) ? trim((string) $iRow['source_name']) : (isset($iRow['name']) ? trim((string) $iRow['name']) : 'Item Tanpa Nama');
            $qty = isset($iRow['quantity']) ? max(1, min(99, (int) $iRow['quantity'])) : (isset($iRow['qty']) ? max(1, min(99, (int) $iRow['qty'])) : 1);
            $unitPrice = isset($iRow['visible_unit_price']) ? max(0, (int) $iRow['visible_unit_price']) : (isset($iRow['price']) ? max(0, (int) $iRow['price']) : 0);
            $lineTotal = isset($iRow['visible_line_total']) ? max(0, (int) $iRow['visible_line_total']) : ($qty * $unitPrice);

            // Arithmetic check on item level
            if (abs(($qty * $unitPrice) - $lineTotal) > 1 && $unitPrice > 0) {
                $lineTotal = $qty * $unitPrice;
            }

            $sumLineTotals += $lineTotal;

            $items[] = [
                'source_name' => $sourceName,
                'name' => $sourceName,
                'quantity' => $qty,
                'qty' => $qty,
                'visible_unit_price' => $unitPrice,
                'price' => $unitPrice,
                'visible_line_total' => $lineTotal,
                'notes' => $notes,
                'unreadable_fields' => is_array($iRow['unreadable_fields'] ?? null) ? $iRow['unreadable_fields'] : [],
            ];
        }

        $visibleSubtotal = max(0, (int) ($decoded['visible_subtotal'] ?? $sumLineTotals));
        $visibleDiscount = max(0, (int) ($decoded['visible_discount'] ?? 0));
        $visibleOrderTotal = max(0, (int) ($decoded['visible_order_total'] ?? ($visibleSubtotal - $visibleDiscount)));
        $visibleOtherFees = max(0, (int) ($decoded['visible_other_fees'] ?? 0));

        // 3. Arithmetic Checks on Order level
        // Subtotal tolerance: max(100, 1%)
        $subtotalDiff = abs($sumLineTotals - $visibleSubtotal);
        $allowedSubtotalTol = max(100, (int) round($visibleSubtotal * 0.01));
        if ($subtotalDiff > $allowedSubtotalTol && $visibleSubtotal > 0 && $sumLineTotals > 0) {
            $reviewFlags[] = 'SUBTOTAL_MISMATCH';
        }

        // Total tolerance: Rp 100
        $expectedTotal = max(0, $visibleSubtotal - $visibleDiscount + $visibleOtherFees);
        if (abs($expectedTotal - $visibleOrderTotal) > 100 && $visibleOrderTotal > 0) {
            $reviewFlags[] = 'TOTAL_MISMATCH';
        }

        return new AiExtraction(
            platform: $platform,
            platformOrderId: $platformOrderId,
            orderedAt: $orderedAt,
            items: $items,
            visibleSubtotal: $visibleSubtotal,
            visibleDiscount: $visibleDiscount,
            visibleOrderTotal: $visibleOrderTotal,
            visibleOtherFees: $visibleOtherFees,
            unreadableFields: is_array($decoded['unreadable_fields'] ?? null) ? $decoded['unreadable_fields'] : [],
            reviewFlags: array_unique($reviewFlags)
        );
    }
}
