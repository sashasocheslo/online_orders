<?php

namespace Tests\Fakes;

use App\Data\CheckoutSessionData;
use App\Data\StripeEventData;
use App\Exceptions\InvalidWebhookException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Contracts\PaymentGatewayInterface;

class FakeStripePaymentGateway implements PaymentGatewayInterface
{
    /** @var array<int, array<string, int|string>> */
    public array $checkoutCalls = [];

    public ?StripeEventData $nextEvent = null;

    public bool $rejectWebhook = false;

    public function createCheckoutSession(Order $order, Payment $payment): CheckoutSessionData
    {
        $this->checkoutCalls[] = [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'idempotency_key' => $payment->idempotency_key,
        ];

        return new CheckoutSessionData(
            sessionId: 'cs_test_'.$payment->id,
            checkoutUrl: 'https://checkout.stripe.test/session/'.$payment->id,
        );
    }

    public function constructWebhookEvent(string $payload, string $signature): StripeEventData
    {
        if ($this->rejectWebhook) {
            throw new InvalidWebhookException('Invalid test signature.');
        }

        if ($this->nextEvent === null) {
            throw new InvalidWebhookException('No fake event configured.');
        }

        return $this->nextEvent;
    }
}
