<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService implements PaymentGatewayInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env("STRIPE_SECRET"));
    }

    public function createPayment(float $amount): string
    {
        $rate = 42.9549;
        $amountCents = (int)(($amount / $rate) * 100);

        $session = $this->stripe->checkout->sessions->create([
            'success_url' => route('stripe.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/cart'),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Замовлення з кошика'],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
        ]);

        return $session['url'];
    }

    public function confirmPayment(string $sessionId): void
    {
        $this->stripe->checkout->sessions->retrieve($sessionId, []);
    }
}
