<?php

namespace App\Services;

use App\Data\StripeEventData;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidWebhookException;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
    ) {}

    public function start(User $user, Order $order): Payment
    {
        abort_unless($order->user_id === $user->id, 403);

        $amountMinor = $this->amountMinor($order);
        $currency = strtolower((string) config('services.stripe.currency', 'uah'));

        $payment = DB::transaction(function () use ($order, $amountMinor, $currency): Payment {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $lockedOrder->status === OrderStatus::PendingPayment,
                422,
                'Це замовлення вже не очікує оплати.',
            );

            $payment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                return Payment::query()->create([
                    'order_id' => $lockedOrder->id,
                    'provider' => 'stripe',
                    'status' => PaymentStatus::Pending,
                    'amount_minor' => $amountMinor,
                    'currency' => $currency,
                    'idempotency_key' => (string) Str::uuid(),
                ]);
            }

            abort_unless(
                $payment->amount_minor === $amountMinor && $payment->currency === $currency,
                409,
                'Сума або валюта платежу не відповідає замовленню.',
            );

            if (in_array($payment->status, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
                $payment->update([
                    'status' => PaymentStatus::Pending,
                    'provider_session_id' => null,
                    'provider_payment_intent_id' => null,
                    'idempotency_key' => (string) Str::uuid(),
                    'checkout_url' => null,
                    'paid_at' => null,
                ]);
            }

            abort_unless($payment->status === PaymentStatus::Pending, 422, 'Платіж уже завершено.');

            return $payment->refresh();
        }, 3);

        if ($payment->checkout_url !== null && $payment->provider_session_id !== null) {
            return $payment;
        }

        $order->loadMissing('menu');
        $checkoutSession = $this->paymentGateway->createCheckoutSession($order, $payment);

        return DB::transaction(function () use ($payment, $checkoutSession): Payment {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->provider_session_id === null) {
                $lockedPayment->update([
                    'provider_session_id' => $checkoutSession->sessionId,
                    'checkout_url' => $checkoutSession->checkoutUrl,
                ]);
            }

            return $lockedPayment->refresh();
        }, 3);
    }

    public function handleWebhook(string $payload, string $signature): void
    {
        $event = $this->paymentGateway->constructWebhookEvent($payload, $signature);

        if (! in_array($event->type, [
            'checkout.session.completed',
            'checkout.session.expired',
        ], true)) {
            return;
        }

        $paidOrder = DB::transaction(
            fn (): ?Order => $this->applyStripeEvent($event),
            3,
        );

        if ($paidOrder !== null) {
            $paidOrder->loadMissing('user');

            Mail::to($paidOrder->user->email)->send(new OrderConfirmation(
                $paidOrder->phone_number,
                $paidOrder->delivery_address,
                $paidOrder->country,
            ));
        }
    }

    private function applyStripeEvent(StripeEventData $event): ?Order
    {
        if ($event->sessionId === null || $event->paymentId === null || $event->orderId === null) {
            throw new InvalidWebhookException('Stripe webhook metadata is incomplete.');
        }

        $payment = Payment::query()
            ->where('provider_session_id', $event->sessionId)
            ->lockForUpdate()
            ->first();

        if ($payment === null
            || $payment->id !== $event->paymentId
            || $payment->order_id !== $event->orderId
        ) {
            throw new InvalidWebhookException('Stripe webhook does not match a local payment.');
        }

        if ($event->type === 'checkout.session.expired') {
            if ($payment->status === PaymentStatus::Pending) {
                $payment->update(['status' => PaymentStatus::Expired]);
            }

            return null;
        }

        if ($event->paymentStatus !== 'paid') {
            return null;
        }

        if ($event->paymentIntentId === null) {
            throw new InvalidWebhookException('Stripe webhook payment intent is missing.');
        }

        if ($event->amountTotal !== $payment->amount_minor
            || $event->currency !== $payment->currency
        ) {
            throw new InvalidWebhookException('Stripe webhook amount or currency is invalid.');
        }

        if ($payment->status === PaymentStatus::Paid) {
            return null;
        }

        $order = Order::query()
            ->whereKey($payment->order_id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($order->status !== OrderStatus::PendingPayment) {
            throw new InvalidWebhookException('Order is not awaiting payment.');
        }

        $payment->update([
            'status' => PaymentStatus::Paid,
            'provider_payment_intent_id' => $event->paymentIntentId,
            'paid_at' => now(),
        ]);

        $order->update(['status' => OrderStatus::Paid]);
        $order->statusHistory()->create([
            'status' => OrderStatus::Paid,
            'changed_by' => null,
        ]);

        return $order;
    }

    private function amountMinor(Order $order): int
    {
        return (int) round((float) $order->total * 100);
    }
}
