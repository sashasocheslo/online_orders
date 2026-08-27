<?php

namespace App\Services\Contracts;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

interface PaymentServiceInterface
{
    public function start(User $user, Order $order): Payment;

    public function handleWebhook(string $payload, string $signature): void;
}
