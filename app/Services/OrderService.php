<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService implements OrderServiceInterface
{
    public function getCheckoutCart(User $user, Menu $menu): Cart
    {
        return Cart::query()
            ->where('user_id', $user->id)
            ->where('menu_id', $menu->id)
            ->whereHas('cartProducts')
            ->with([
                'menu',
                'cartProducts.product.category',
            ])
            ->firstOrFail();
    }

    public function createFromCart(User $user, Menu $menu, array $deliveryData): Order
    {
        return DB::transaction(function () use ($user, $menu, $deliveryData): Order {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->where('menu_id', $menu->id)
                ->lockForUpdate()
                ->first();

            if ($cart === null) {
                throw ValidationException::withMessages([
                    'cart' => 'Кошик цього ресторану не знайдено.',
                ]);
            }

            $cartProducts = CartProduct::query()
                ->where('cart_id', $cart->id)
                ->with('product')
                ->lockForUpdate()
                ->get();

            if ($cartProducts->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Неможливо оформити порожній кошик.',
                ]);
            }

            $totalInCents = 0;
            $itemSnapshots = [];

            foreach ($cartProducts as $cartProduct) {
                $product = $cartProduct->product;

                abort_unless($product !== null && $product->menu_id === $menu->id, 409);

                $totalInCents += (int) round((float) $product->price * 100)
                    * $cartProduct->quantity;

                $itemSnapshots[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $cartProduct->quantity,
                    'image' => $product->image,
                ];
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'menu_id' => $menu->id,
                'status' => OrderStatus::PendingPayment,
                'total' => number_format($totalInCents / 100, 2, '.', ''),
                'phone_number' => $deliveryData['phone_number'],
                'delivery_address' => $deliveryData['delivery_address'],
                'country' => $deliveryData['country'],
            ]);

            $order->items()->createMany($itemSnapshots);

            $order->statusHistory()->create([
                'status' => OrderStatus::PendingPayment,
                'changed_by' => $user->id,
            ]);

            $cart->cartProducts()->delete();
            $cart->delete();

            return $this->loadDetails($order);
        }, 3);
    }

    public function listForUser(User $user): Collection
    {
        return Order::query()
            ->when(
                ! $user->isAdmin(),
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->with(['menu', 'user'])
            ->withCount('items')
            ->latest()
            ->get();
    }

    public function loadDetails(Order $order): Order
    {
        return $order->load([
            'menu',
            'user',
            'items',
            'payment',
            'statusHistory.changedBy',
        ]);
    }
}
