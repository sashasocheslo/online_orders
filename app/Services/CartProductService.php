<?php

namespace App\Services;

use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;

class CartProductService implements CartProductServiceInterface
{
    public function addProduct(int $cartId, int $productId): void
    {
        $product = Product::findOrFail($productId);
        Gate::authorize('add', $product);

        $cartProduct = CartProduct::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($cartProduct) {
            $cartProduct->increment('quantity');
        } else {
            CartProduct::create([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'image' => $product->image,
                'quantity' => 1,
            ]);
        }
    }

    public function removeProduct(CartProduct $cartProduct): void
    {
        $product = $cartProduct->product;
        Gate::authorize('add', $product);

        if ($cartProduct->quantity > 1) {
            $cartProduct->decrement('quantity');
        } else {
            $cartProduct->delete();
        }
    }

    public function listCartProducts(): iterable
    {
        return CartProduct::all();
    }
}
