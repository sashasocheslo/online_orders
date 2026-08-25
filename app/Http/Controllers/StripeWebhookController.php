<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidWebhookException;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function __construct(
        private PaymentServiceInterface $paymentService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleWebhook(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
            );
        } catch (InvalidWebhookException) {
            return response()->json(['message' => 'Invalid Stripe webhook.'], 400);
        }

        return response()->json(['received' => true]);
    }
}
