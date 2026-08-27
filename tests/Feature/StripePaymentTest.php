<?php

use App\Data\StripeEventData;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderConfirmation;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakeStripePaymentGateway;

pest()->use(RefreshDatabase::class);

function createStripePaymentOrder(
    User $user,
    string $total = '29.80',
    OrderStatus $status = OrderStatus::PendingPayment,
): Order {
    $menu = Menu::query()->create([
        'name' => 'Stripe Test Restaurant',
        'image' => 'stripe-test.png',
    ]);

    $order = Order::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
        'status' => $status,
        'total' => $total,
        'phone_number' => '+380501234567',
        'delivery_address' => 'Київ, Тестова 1',
        'country' => 'Україна',
    ]);

    $order->statusHistory()->create([
        'status' => $status,
        'changed_by' => $user->id,
    ]);

    return $order;
}

function bindFakeStripeGateway(): FakeStripePaymentGateway
{
    $gateway = new FakeStripePaymentGateway;
    app()->instance(PaymentGatewayInterface::class, $gateway);

    return $gateway;
}

function completedStripeEvent(Payment $payment, ?int $amountTotal = null): StripeEventData
{
    return new StripeEventData(
        eventId: 'evt_test_'.$payment->id,
        type: 'checkout.session.completed',
        sessionId: $payment->provider_session_id,
        paymentIntentId: 'pi_test_'.$payment->id,
        paymentStatus: 'paid',
        amountTotal: $amountTotal ?? $payment->amount_minor,
        currency: $payment->currency,
        orderId: $payment->order_id,
        paymentId: $payment->id,
    );
}

test('checkout uses the server order total and ignores a forged amount', function () {
    $gateway = bindFakeStripeGateway();
    $user = User::factory()->create();
    $order = createStripePaymentOrder($user);

    $response = $this->actingAs($user)
        ->post(route('orders.payment.store', $order), [
            'amount' => '0.01',
            'currency' => 'usd',
            'status' => OrderStatus::Paid->value,
        ]);

    $payment = $order->payment()->sole();

    $response->assertRedirect('https://checkout.stripe.test/session/'.$payment->id);

    expect($payment->amount_minor)->toBe(2980)
        ->and($payment->currency)->toBe('uah')
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and($gateway->checkoutCalls)->toHaveCount(1)
        ->and($gateway->checkoutCalls[0]['amount_minor'])->toBe(2980);
});

test('repeated payment request reuses the same payment and checkout session', function () {
    $gateway = bindFakeStripeGateway();
    $user = User::factory()->create();
    $order = createStripePaymentOrder($user);

    $this->actingAs($user)->post(route('orders.payment.store', $order))->assertRedirect();
    $this->actingAs($user)->post(route('orders.payment.store', $order))->assertRedirect();

    expect(Payment::query()->where('order_id', $order->id)->count())->toBe(1)
        ->and($gateway->checkoutCalls)->toHaveCount(1);
});

test('user cannot pay another users order or an order with a final status', function () {
    bindFakeStripeGateway();
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $pendingOrder = createStripePaymentOrder($owner);
    $paidOrder = createStripePaymentOrder($owner, '15.00', OrderStatus::Paid);

    $this->actingAs($stranger)
        ->post(route('orders.payment.store', $pendingOrder))
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('orders.payment.store', $paidOrder))
        ->assertUnprocessable();

    $this->assertDatabaseCount('payments', 0);
});

test('return from Stripe does not mark an order as paid', function () {
    bindFakeStripeGateway();
    $user = User::factory()->create();
    $order = createStripePaymentOrder($user);

    $this->actingAs($user)
        ->get(route('orders.payment.return', $order))
        ->assertRedirect(route('orders.show', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::PendingPayment);
});

test('invalid webhook signature is rejected', function () {
    $gateway = bindFakeStripeGateway();
    $gateway->rejectWebhook = true;

    $this->postJson(route('stripe.webhook'), [], ['Stripe-Signature' => 'invalid'])
        ->assertBadRequest();
});

test('valid completed webhook marks payment and order as paid exactly once', function () {
    Mail::fake();
    $gateway = bindFakeStripeGateway();
    $user = User::factory()->create();
    $order = createStripePaymentOrder($user);

    $this->actingAs($user)->post(route('orders.payment.store', $order));

    $payment = $order->payment()->sole();
    $gateway->nextEvent = completedStripeEvent($payment);

    $this->postJson(route('stripe.webhook'), [], ['Stripe-Signature' => 'valid'])
        ->assertOk();
    $this->postJson(route('stripe.webhook'), [], ['Stripe-Signature' => 'valid'])
        ->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Paid)
        ->and($payment->provider_payment_intent_id)->toBe('pi_test_'.$payment->id)
        ->and($payment->paid_at)->not->toBeNull()
        ->and($order->refresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->statusHistory()->where('status', OrderStatus::Paid->value)->count())->toBe(1);

    Mail::assertSent(OrderConfirmation::class, 1);
});

test('webhook with a forged amount is rejected without changing the order', function () {
    $gateway = bindFakeStripeGateway();
    $user = User::factory()->create();
    $order = createStripePaymentOrder($user);

    $this->actingAs($user)->post(route('orders.payment.store', $order));

    $payment = $order->payment()->sole();
    $gateway->nextEvent = completedStripeEvent($payment, 1);

    $this->postJson(route('stripe.webhook'), [], ['Stripe-Signature' => 'valid'])
        ->assertBadRequest();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->refresh()->status)->toBe(OrderStatus::PendingPayment);
});

test('expired checkout session expires only the payment attempt', function () {
    $gateway = bindFakeStripeGateway();
    $user = User::factory()->create();
    $order = createStripePaymentOrder($user);

    $this->actingAs($user)->post(route('orders.payment.store', $order));

    $payment = $order->payment()->sole();
    $gateway->nextEvent = new StripeEventData(
        eventId: 'evt_expired_'.$payment->id,
        type: 'checkout.session.expired',
        sessionId: $payment->provider_session_id,
        paymentIntentId: null,
        paymentStatus: 'unpaid',
        amountTotal: $payment->amount_minor,
        currency: $payment->currency,
        orderId: $payment->order_id,
        paymentId: $payment->id,
    );

    $this->postJson(route('stripe.webhook'), [], ['Stripe-Signature' => 'valid'])
        ->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Expired)
        ->and($order->refresh()->status)->toBe(OrderStatus::PendingPayment);
});
