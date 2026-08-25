<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceInterface $paymentService,
    ) {}

    public function store(Order $order, Request $request): RedirectResponse
    {
        Gate::authorize('pay', $order);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $payment = $this->paymentService->start($user, $order);
        abort_if($payment->checkout_url === null, 503, 'Stripe Checkout тимчасово недоступний.');

        return redirect()->away($payment->checkout_url);
    }

    public function returnFromStripe(Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        return redirect()
            ->route('orders.show', $order)
            ->with('info', 'Stripe повернув вас на сайт. Остаточний статус оновить захищений webhook.');
    }
}
