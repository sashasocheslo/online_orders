<?php

namespace App\Services\Contracts;

use App\Data\CheckoutSessionData;
use App\Data\StripeEventData;
use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createCheckoutSession(Order $order, Payment $payment): CheckoutSessionData;

    public function constructWebhookEvent(string $payload, string $signature): StripeEventData;
}
