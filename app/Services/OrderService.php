<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

interface OrderServiceInterface
{
    public function sendOrderConfirmation(array $data): void;
}

class OrderService implements OrderServiceInterface
{
    public function sendOrderConfirmation(array $data): void
    {
        $email = Auth::user()->email;
        Mail::to($email)->send(new OrderConfirmation(
            $data['phone_number'],
            $data['delivery_address'],
            $data['country']
        ));
    }
}
