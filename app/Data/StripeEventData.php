<?php

namespace App\Data;

final readonly class StripeEventData
{
    public function __construct(
        public string $eventId,
        public string $type,
        public ?string $sessionId,
        public ?string $paymentIntentId,
        public ?string $paymentStatus,
        public ?int $amountTotal,
        public ?string $currency,
        public ?int $orderId,
        public ?int $paymentId,
    ) {}
}
