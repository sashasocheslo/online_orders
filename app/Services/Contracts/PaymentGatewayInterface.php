<?php

namespace App\Services\Contracts;

interface PaymentGatewayInterface
{
    public function createPayment(float $amount): string;

    public function confirmPayment(string $sessionId): void;
}
