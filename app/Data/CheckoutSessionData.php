<?php

namespace App\Data;

final readonly class CheckoutSessionData
{
    public function __construct(
        public string $sessionId,
        public string $checkoutUrl,
    ) {}
}
