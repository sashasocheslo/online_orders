<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CartProduct;

interface CartProductServiceInterface
{
    public function addProduct(int $cartId, int $productId): void;
    public function removeProduct(CartProduct $cartProduct): void;
    public function listCartProducts(): iterable;
}
