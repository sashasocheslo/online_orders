<?php

namespace App\Services\Contracts;

interface OrderServiceInterface
{
    public function sendOrderConfirmation(array $data): void;
}
