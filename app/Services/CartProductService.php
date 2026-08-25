<?php

namespace App\Services;

use App\Models\CartProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\CartProductServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class CartProductService implements CartProductServiceInterface
{
    public function addProduct(int $cartId, int $productId): void
    {
        $product = Product::findOrFail($productId);

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
        if ($cartProduct->quantity > 1) {
            $cartProduct->decrement('quantity');
        } else {
            $cartProduct->delete();
        }
    }

    public function listCartProducts(User $user): Collection
    {
        return CartProduct::query()
            ->whereHas('cart', fn ($query) => $query->where('user_id', $user->id))
            ->with('product')
            ->get();
    }
}
