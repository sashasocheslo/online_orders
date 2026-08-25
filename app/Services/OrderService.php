<?php

namespace App\Services;

use App\Mail\OrderConfirmation;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
