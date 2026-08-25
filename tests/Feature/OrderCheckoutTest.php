<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

pest()->use(RefreshDatabase::class);

function createOrderCheckoutProduct(
    Menu $menu,
    string $name,
    string $price,
): Product {
    $category = Category::query()->create([
        'name' => $name.' Category',
    ]);

    return Product::query()->create([
        'name' => $name,
        'price' => $price,
        'description' => $name.' description',
        'image' => 'products/'.str($name)->slug().'.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);
}

function createOrderRecord(User $user, Menu $menu, string $total = '10.00'): Order
{
    return Order::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
        'status' => OrderStatus::PendingPayment,
        'total' => $total,
        'phone_number' => '+380501234567',
        'delivery_address' => 'Тестова адреса, 1',
        'country' => 'Україна',
    ]);
}

test('order is created from one restaurant cart using server calculated total and snapshots', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $menu = Menu::query()->create(['name' => 'Checkout Restaurant', 'image' => 'checkout.png']);
    $otherMenu = Menu::query()->create(['name' => 'Other Restaurant', 'image' => 'other.png']);

    $firstProduct = createOrderCheckoutProduct($menu, 'Checkout Burger', '10.25');
    $secondProduct = createOrderCheckoutProduct($menu, 'Checkout Drink', '3.10');
    $otherProduct = createOrderCheckoutProduct($otherMenu, 'Other Product', '500.00');

    $cart = Cart::query()->create(['user_id' => $user->id, 'menu_id' => $menu->id]);
    $cart->cartProducts()->create([
        'product_id' => $firstProduct->id,
        'image' => $firstProduct->image,
        'quantity' => 2,
    ]);
    $cart->cartProducts()->create([
        'product_id' => $secondProduct->id,
        'image' => $secondProduct->image,
        'quantity' => 3,
    ]);

    $otherCart = Cart::query()->create(['user_id' => $user->id, 'menu_id' => $otherMenu->id]);
    $otherCart->cartProducts()->create([
        'product_id' => $otherProduct->id,
        'image' => $otherProduct->image,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)
        ->post(route('menu.orders.store', $menu), [
            'phone_number' => '+380501234567',
            'delivery_address' => 'Київ, Тестова 1',
            'country' => 'Україна',
            'amount' => '0.50',
            'user_id' => $otherUser->id,
            'menu_id' => $otherMenu->id,
            'status' => OrderStatus::Completed->value,
        ]);

    $order = $user->orders()->with(['items', 'statusHistory'])->sole();

    $response->assertRedirect(route('orders.show', $order));

    expect($order->user_id)->toBe($user->id)
        ->and($order->menu_id)->toBe($menu->id)
        ->and($order->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->total)->toBe('29.80')
        ->and($order->items)->toHaveCount(2)
        ->and($order->statusHistory)->toHaveCount(1)
        ->and($order->statusHistory->first()->status)->toBe(OrderStatus::PendingPayment);

    $firstItem = $order->items->firstWhere('product_id', $firstProduct->id);

    expect($firstItem->product_name)->toBe('Checkout Burger')
        ->and($firstItem->unit_price)->toBe('10.25')
        ->and($firstItem->quantity)->toBe(2);

    $firstProduct->update(['name' => 'Changed Name', 'price' => '99.99']);

    expect($firstItem->refresh()->product_name)->toBe('Checkout Burger')
        ->and($firstItem->unit_price)->toBe('10.25');

    $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    $this->assertDatabaseHas('carts', ['id' => $otherCart->id]);
    $this->assertDatabaseHas('cart_products', [
        'cart_id' => $otherCart->id,
        'product_id' => $otherProduct->id,
    ]);
});

test('checkout form contains delivery fields but does not contain a client supplied amount', function () {
    $user = User::factory()->create();
    $menu = Menu::query()->create(['name' => 'Form Restaurant', 'image' => 'form.png']);
    $product = createOrderCheckoutProduct($menu, 'Form Product', '15.00');
    $cart = Cart::query()->create(['user_id' => $user->id, 'menu_id' => $menu->id]);
    $cart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('menu.orders.create', $menu))
        ->assertOk()
        ->assertSee('name="phone_number"', false)
        ->assertSee('name="delivery_address"', false)
        ->assertSee('name="country"', false)
        ->assertDontSee('name="amount"', false);
});

test('delivery data is validated before an order is created', function () {
    $user = User::factory()->create();
    $menu = Menu::query()->create(['name' => 'Validation Restaurant', 'image' => 'validation.png']);
    $product = createOrderCheckoutProduct($menu, 'Validation Product', '20.00');
    $cart = Cart::query()->create(['user_id' => $user->id, 'menu_id' => $menu->id]);
    $cart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->post(route('menu.orders.store', $menu), [])
        ->assertSessionHasErrors(['phone_number', 'delivery_address', 'country']);

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('carts', ['id' => $cart->id]);
});

test('empty or another users cart cannot be checked out', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $menu = Menu::query()->create(['name' => 'Protected Checkout', 'image' => 'protected.png']);
    $product = createOrderCheckoutProduct($menu, 'Protected Product', '30.00');
    $ownerCart = Cart::query()->create(['user_id' => $owner->id, 'menu_id' => $menu->id]);
    $ownerCart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $deliveryData = [
        'phone_number' => '+380501234567',
        'delivery_address' => 'Тестова адреса',
        'country' => 'Україна',
    ];

    $this->actingAs($stranger)
        ->get(route('menu.orders.create', $menu))
        ->assertNotFound();

    $this->actingAs($stranger)
        ->post(route('menu.orders.store', $menu), $deliveryData)
        ->assertSessionHasErrors('cart');

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('carts', ['id' => $ownerCart->id]);
});

test('user cannot view another users order while administrator can', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $menu = Menu::query()->create(['name' => 'Policy Restaurant', 'image' => 'policy.png']);
    $order = createOrderRecord($owner, $menu);

    $this->actingAs($owner)
        ->get(route('orders.show', $order))
        ->assertOk();

    $this->actingAs($stranger)
        ->get(route('orders.show', $order))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('orders.show', $order))
        ->assertOk();
});

test('order history contains only current users orders', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $userMenu = Menu::query()->create(['name' => 'Visible Restaurant', 'image' => 'visible.png']);
    $strangerMenu = Menu::query()->create(['name' => 'Hidden Restaurant', 'image' => 'hidden.png']);

    createOrderRecord($user, $userMenu);
    createOrderRecord($stranger, $strangerMenu);

    $this->actingAs($user)
        ->get(route('orders.index'))
        ->assertOk()
        ->assertSeeText('Visible Restaurant')
        ->assertDontSeeText('Hidden Restaurant');
});

test('order owner can delete an order with all related records', function () {
    $owner = User::factory()->create();
    $menu = Menu::query()->create(['name' => 'Deletable Restaurant', 'image' => 'delete.png']);
    $order = createOrderRecord($owner, $menu);

    $item = $order->items()->create([
        'product_name' => 'Deleted snapshot',
        'unit_price' => '10.00',
        'quantity' => 1,
    ]);

    $history = $order->statusHistory()->create([
        'status' => OrderStatus::PendingPayment,
        'changed_by' => $owner->id,
    ]);

    $payment = $order->payment()->create([
        'provider' => 'stripe',
        'status' => PaymentStatus::Pending,
        'amount_minor' => 1000,
        'currency' => 'uah',
        'idempotency_key' => (string) Str::uuid(),
    ]);

    $this->actingAs($owner)
        ->delete(route('orders.destroy', $order))
        ->assertRedirect(route('orders.index'))
        ->assertSessionHas('success', 'Замовлення видалено.');

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    $this->assertDatabaseMissing('order_status_histories', ['id' => $history->id]);
    $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
});

test('user cannot delete another users order while administrator can', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $menu = Menu::query()->create(['name' => 'Managed Restaurant', 'image' => 'managed.png']);
    $order = createOrderRecord($owner, $menu);

    $this->actingAs($stranger)
        ->delete(route('orders.destroy', $order))
        ->assertForbidden();

    $this->assertDatabaseHas('orders', ['id' => $order->id]);

    $this->actingAs($admin)
        ->delete(route('orders.destroy', $order))
        ->assertRedirect(route('orders.index'));

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
});
