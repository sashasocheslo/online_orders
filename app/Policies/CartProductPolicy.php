<?php

namespace App\Policies;

use App\Models\CartProduct;
use App\Models\User;

class CartProductPolicy
{
    public function update(User $user, CartProduct $cartProduct): bool
    {
        return $user->is($cartProduct->cart?->user);
    }

    public function delete(User $user, CartProduct $cartProduct): bool
    {
        return $user->is($cartProduct->cart?->user);
    }
}
