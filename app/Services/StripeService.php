<?php

namespace App\Services;

use App\Data\CheckoutSessionData;
use App\Data\StripeEventData;
use App\Exceptions\InvalidWebhookException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Contracts\PaymentGatewayInterface;
use LogicException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeService implements PaymentGatewayInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            throw new LogicException('STRIPE_SECRET is not configured.');
        }

        $this->stripe = new StripeClient($secret);
    }

    public function createCheckoutSession(Order $order, Payment $payment): CheckoutSessionData
    {
        $session = $this->stripe->checkout->sessions->create([
            'success_url' => route('orders.payment.return', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('orders.show', $order),
            'client_reference_id' => (string) $order->id,
            'metadata' => [
                'order_id' => (string) $order->id,
                'payment_id' => (string) $payment->id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'payment_id' => (string) $payment->id,
                ],
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => $payment->currency,
                    'product_data' => [
                        'name' => "Замовлення №{$order->id} — {$order->menu->name}",
                    ],
                    'unit_amount' => $payment->amount_minor,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'payment_method_types' => ['card'],
        ], [
            'idempotency_key' => $payment->idempotency_key,
        ]);

        return new CheckoutSessionData(
            sessionId: $session->id,
            checkoutUrl: (string) $session->url,
        );
    }

    public function constructWebhookEvent(string $payload, string $signature): StripeEventData
    {
        $webhookSecret = (string) config('services.stripe.webhook_secret');

        if ($webhookSecret === '') {
            throw new LogicException('STRIPE_WEBHOOK_SECRET is not configured.');
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            throw new InvalidWebhookException('Stripe webhook signature is invalid.', previous: $exception);
        }

        $session = $event->data->object;
        $paymentIntent = $session->payment_intent ?? null;

        if (is_object($paymentIntent)) {
            $paymentIntent = $paymentIntent->id ?? null;
        }

        return new StripeEventData(
            eventId: $event->id,
            type: $event->type,
            sessionId: isset($session->id) ? (string) $session->id : null,
            paymentIntentId: is_string($paymentIntent) ? $paymentIntent : null,
            paymentStatus: isset($session->payment_status) ? (string) $session->payment_status : null,
            amountTotal: isset($session->amount_total) ? (int) $session->amount_total : null,
            currency: isset($session->currency) ? strtolower((string) $session->currency) : null,
            orderId: isset($session->metadata->order_id) ? (int) $session->metadata->order_id : null,
            paymentId: isset($session->metadata->payment_id) ? (int) $session->metadata->payment_id : null,
        );
    }
}
