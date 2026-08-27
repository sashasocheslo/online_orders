<?php

namespace App\Services\Contracts;

use App\Models\Cart;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface OrderServiceInterface
{
    public function getCheckoutCart(User $user, Menu $menu): Cart;

    public function createFromCart(User $user, Menu $menu, array $deliveryData): Order;

    public function listForUser(User $user): Collection;

    public function loadDetails(Order $order): Order;

    public function deleteOrder(Order $order): void;
}
