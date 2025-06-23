<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function index(Request $request)
    {
        return view('cart_product.order_form', [
            'amount' => $request->amount,
            'quantity' => $request->quantity,
        ]);
    }

    public function create()
    {
        return view('cart_product.order_form');
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:255',
            'country' => 'required|string|max:100',
        ]);

        $email = Auth::user()->email;
        Mail::to($email)->send(new OrderConfirmation(
            $validated['phone_number'],
            $validated['delivery_address'],
            $validated['country']
        ));

        $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));

        $amountUah = $request->amount;
        $rate = 42.9549;
        $amountUsd = $amountUah / $rate;
        $amountCents = (int)($amountUsd * 100);

        $successUrl = route('stripe.payment.success') . '?session_id={CHECKOUT_SESSION_ID}';

        $response = $stripe->checkout->sessions->create([
            'success_url' => $successUrl,
            'cancel_url' => url('/cart'),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Замовлення з кошика',
                    ],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
        ]);

        return redirect($response['url']);
    }

    public function success(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env("STRIPE_SECRET"));

        $response = $stripe->checkout->sessions->retrieve(
            $request->session_id,[]);

            return redirect()->back()->with('success', 'Оплата пройшла успішно');
    }
}
