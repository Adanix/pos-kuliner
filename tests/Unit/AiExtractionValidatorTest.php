<?php

namespace Tests\Unit;

use App\Services\Ai\AiExtractionValidator;
use PHPUnit\Framework\TestCase;

class AiExtractionValidatorTest extends TestCase
{
    public function test_valid_json_parses_and_strips_pii(): void
    {
        $validator = new AiExtractionValidator();

        $rawJson = json_encode([
            'platform' => 'gofood',
            'platform_order_id' => 'GF-9988',
            'customer_name' => 'John Doe (PII)',
            'customer_phone' => '081234567890 (PII)',
            'customer_address' => 'Jl. Rahasia No. 1 (PII)',
            'items' => [
                [
                    'source_name' => 'Ayam Geprek',
                    'quantity' => 2,
                    'visible_unit_price' => 15000,
                    'visible_line_total' => 30000,
                    'notes' => 'Level 5',
                ],
            ],
            'visible_subtotal' => 30000,
            'visible_discount' => 5000,
            'visible_order_total' => 25000,
            'visible_other_fees' => 0,
        ]);

        $dto = $validator->validateAndParse($rawJson);

        $this->assertEquals('gofood', $dto->platform);
        $this->assertEquals('GF-9988', $dto->platformOrderId);
        $this->assertCount(1, $dto->items);
        $this->assertEquals(30000, $dto->visibleSubtotal);
        $this->assertEquals(25000, $dto->visibleOrderTotal);
        $this->assertEmpty($dto->reviewFlags);

        // Verify PII is completely stripped
        $arr = $dto->toArray();
        $this->assertArrayNotHasKey('customer_name', $arr);
        $this->assertArrayNotHasKey('customer_phone', $arr);
        $this->assertArrayNotHasKey('customer_address', $arr);
    }

    public function test_arithmetic_subtotal_mismatch_flagged(): void
    {
        $validator = new AiExtractionValidator();

        $rawJson = json_encode([
            'platform' => 'shopeefood',
            'items' => [
                [
                    'source_name' => 'Bebek Goreng',
                    'quantity' => 1,
                    'visible_unit_price' => 30000,
                    'visible_line_total' => 30000,
                ],
            ],
            // Mismatch: 30000 items vs 50000 claimed subtotal
            'visible_subtotal' => 50000,
            'visible_discount' => 0,
            'visible_order_total' => 50000,
        ]);

        $dto = $validator->validateAndParse($rawJson);
        $this->assertContains('SUBTOTAL_MISMATCH', $dto->reviewFlags);
    }
}
