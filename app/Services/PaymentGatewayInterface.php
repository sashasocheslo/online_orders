<?php

namespace App\Services;

interface PaymentGatewayInterface
{
    public function createPayment(float $amount): string;
    public function confirmPayment(string $sessionId): void;
}
