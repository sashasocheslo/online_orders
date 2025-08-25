<?php

namespace App\Services;

interface OrderServiceInterface
{
    public function sendOrderConfirmation(array $data): void;
}
