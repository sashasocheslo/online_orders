<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayInterface;
use App\Services\OrderServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    private PaymentGatewayInterface $paymentGateway;
    private OrderServiceInterface $orderService;

    public function __construct(PaymentGatewayInterface $paymentGateway, OrderServiceInterface $orderService)
    {
        $this->paymentGateway = $paymentGateway;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Not supported for API. Use POST /payments'
            ], 405);
        }

        return view('cart_product.order_form', [
            'amount' => $request->amount,
            'quantity' => $request->quantity,
        ]);
    }

    public function create(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Not supported for API. Use POST /payments'
            ], 405);
        }
        return view('cart_product.order_form');
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.5',
        ]);

        $this->orderService->sendOrderConfirmation($validated);

        $checkoutUrl = $this->paymentGateway->createPayment($request->amount);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payment created',
                'checkout_url' => $checkoutUrl,
            ], 201);
        }

        return redirect($checkoutUrl);
    }

    public function success(Request $request)
    {
        $this->paymentGateway->confirmPayment($request->session_id);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Payment confirmed'], 200);
        }

        return redirect()->back()->with('success', 'Оплата пройшла успішно');
    }
}
