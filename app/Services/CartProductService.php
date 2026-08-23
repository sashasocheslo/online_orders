<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\CartProductServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartProductService implements CartProductServiceInterface
{
    private const MAX_QUANTITY = 99;

    public function addProduct(User $user, Product $product): CartProduct
    {
        return DB::transaction(function () use ($user, $product): CartProduct {
            $cart = $user->carts()->firstOrCreate([
                'menu_id' => $product->menu_id,
            ]);

            $cartProduct = $cart->cartProducts()->firstOrCreate(
                ['product_id' => $product->id],
                ['image' => $product->image, 'quantity' => 0],
            );

            $cartProduct = CartProduct::query()
                ->lockForUpdate()
                ->findOrFail($cartProduct->id);

            if ($cartProduct->quantity >= self::MAX_QUANTITY) {
                throw ValidationException::withMessages([
                    'quantity' => 'Максимальна кількість товару — 99.',
                ]);
            }

            $cartProduct->increment('quantity');

            return $cartProduct->refresh();
        }, 3);
    }

    public function updateQuantity(CartProduct $cartProduct, int $quantity): CartProduct
    {
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages([
                'quantity' => 'Кількість товару повинна бути від 1 до 99.',
            ]);
        }

        return DB::transaction(function () use ($cartProduct, $quantity): CartProduct {
            $cartProduct = CartProduct::query()
                ->lockForUpdate()
                ->findOrFail($cartProduct->id);

            $cartProduct->update(['quantity' => $quantity]);

            return $cartProduct->refresh();
        }, 3);
    }

    public function removeProduct(CartProduct $cartProduct): void
    {
        DB::transaction(function () use ($cartProduct): void {
            $cartProduct = CartProduct::query()
                ->lockForUpdate()
                ->findOrFail($cartProduct->id);

            $cart = $cartProduct->cart;
            $cartProduct->delete();

            if ($cart !== null && ! $cart->cartProducts()->exists()) {
                $cart->delete();
            }
        }, 3);
    }

    public function findCart(User $user, Menu $menu): ?Cart
    {
        return $user->carts()
            ->where('menu_id', $menu->id)
            ->whereHas('cartProducts')
            ->with([
                'menu',
                'cartProducts.product.category',
                'cartProducts.product.comments.user',
            ])
            ->first();
    }

    public function listCarts(User $user): Collection
    {
        return $user->carts()
            ->whereHas('cartProducts')
            ->with([
                'menu',
                'cartProducts.product.category',
                'cartProducts.product.comments.user',
            ])
            ->orderBy('menu_id')
            ->get();
    }
}
