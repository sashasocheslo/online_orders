<?php

namespace App\Services\Contracts;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CartProductServiceInterface
{
    public function addProduct(User $user, Product $product): CartProduct;

    public function updateQuantity(CartProduct $cartProduct, int $quantity): CartProduct;

    public function removeProduct(CartProduct $cartProduct): void;

    public function findCart(User $user, Menu $menu): ?Cart;

    public function listCarts(User $user): Collection;
}
