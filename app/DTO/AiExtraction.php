<?php

namespace App\DTO;

class AiExtraction
{
    public function __construct(
        public string $platform,
        public ?string $platformOrderId,
        public ?string $orderedAt,
        public array $items,
        public int $visibleSubtotal,
        public int $visibleDiscount,
        public int $visibleOrderTotal,
        public int $visibleOtherFees,
        public array $unreadableFields = [],
        public array $reviewFlags = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            platform: $data['platform'] ?? 'unknown',
            platformOrderId: $data['platform_order_id'] ?? null,
            orderedAt: $data['ordered_at'] ?? null,
            items: $data['items'] ?? [],
            visibleSubtotal: (int) ($data['visible_subtotal'] ?? 0),
            visibleDiscount: (int) ($data['visible_discount'] ?? 0),
            visibleOrderTotal: (int) ($data['visible_order_total'] ?? 0),
            visibleOtherFees: (int) ($data['visible_other_fees'] ?? 0),
            unreadableFields: $data['unreadable_fields'] ?? [],
            reviewFlags: $data['review_flags'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'channel' => $this->platform,
            'platform_order_id' => $this->platformOrderId,
            'order_id' => $this->platformOrderId,
            'ordered_at' => $this->orderedAt,
            'items' => $this->items,
            'visible_subtotal' => $this->visibleSubtotal,
            'subtotal' => $this->visibleSubtotal,
            'visible_discount' => $this->visibleDiscount,
            'discount' => $this->visibleDiscount,
            'visible_order_total' => $this->visibleOrderTotal,
            'total' => $this->visibleOrderTotal,
            'visible_other_fees' => $this->visibleOtherFees,
            'unreadable_fields' => $this->unreadableFields,
            'review_flags' => $this->reviewFlags,
        ];
    }
}
