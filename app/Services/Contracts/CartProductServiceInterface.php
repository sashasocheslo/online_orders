<?php

namespace App\Services\Contracts;

use App\Models\CartProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CartProductServiceInterface
{
    public function addProduct(int $cartId, int $productId): void;

    public function removeProduct(CartProduct $cartProduct): void;

    public function listCartProducts(User $user): Collection;
}
