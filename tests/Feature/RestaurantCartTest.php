<?php

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function createRestaurantCartProduct(
    string $menuName,
    string $productName,
    string $price = '100.00',
): array {
    $menu = Menu::query()->create([
        'name' => $menuName,
        'image' => 'menus/'.str($menuName)->slug().'.png',
    ]);

    $category = Category::query()->create([
        'name' => $menuName.' Category',
    ]);

    $product = Product::query()->create([
        'name' => $productName,
        'price' => $price,
        'description' => 'Restaurant cart test product.',
        'image' => 'products/'.str($productName)->slug().'.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    return compact('menu', 'category', 'product');
}

test('products from two restaurants create two separate carts', function () {
    $user = User::factory()->create();
    ['menu' => $firstMenu, 'product' => $firstProduct] = createRestaurantCartProduct(
        'First Restaurant',
        'First Product',
    );
    ['menu' => $secondMenu, 'product' => $secondProduct] = createRestaurantCartProduct(
        'Second Restaurant',
        'Second Product',
    );

    $this->actingAs($user)
        ->post(route('cart_product.store'), ['product_id' => $firstProduct->id])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('cart_product.store'), ['product_id' => $secondProduct->id])
        ->assertRedirect();

    expect($user->carts()->count())->toBe(2);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'menu_id' => $firstMenu->id,
    ]);
    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'menu_id' => $secondMenu->id,
    ]);
});

test('adding the same product increments quantity without duplicate rows', function () {
    $user = User::factory()->create();
    ['product' => $product] = createRestaurantCartProduct(
        'Quantity Restaurant',
        'Quantity Product',
    );

    $this->actingAs($user)
        ->post(route('cart_product.store'), ['product_id' => $product->id])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('cart_product.store'), ['product_id' => $product->id])
        ->assertRedirect();

    $cart = $user->carts()->sole();

    expect($cart->cartProducts()->count())->toBe(1)
        ->and($cart->cartProducts()->sole()->quantity)->toBe(2);
});

test('client supplied cart id cannot select another users cart', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createRestaurantCartProduct(
        'Protected Restaurant',
        'Protected Product',
    );

    $ownerCart = Cart::query()->create([
        'user_id' => $owner->id,
        'menu_id' => $menu->id,
    ]);

    $this->actingAs($stranger)
        ->post(route('cart_product.store'), [
            'product_id' => $product->id,
            'cart_id' => $ownerCart->id,
        ])
        ->assertRedirect();

    expect($ownerCart->cartProducts()->count())->toBe(0);

    $strangerCart = $stranger->carts()->sole();

    expect($strangerCart->menu_id)->toBe($menu->id)
        ->and($strangerCart->cartProducts()->sole()->product_id)->toBe($product->id);
});

test('user cannot update or delete another users cart item', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createRestaurantCartProduct(
        'Owner Restaurant',
        'Owner Product',
    );

    $cart = Cart::query()->create([
        'user_id' => $owner->id,
        'menu_id' => $menu->id,
    ]);
    $cartProduct = $cart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($stranger)
        ->patch(route('cart_product.update', $cartProduct), ['quantity' => 2])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->delete(route('cart_product.destroy', $cartProduct))
        ->assertForbidden();

    expect($cartProduct->refresh()->quantity)->toBe(1);
});

test('quantity outside the range from one to ninety nine is rejected', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createRestaurantCartProduct(
        'Validation Restaurant',
        'Validation Product',
    );

    $cart = Cart::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
    ]);
    $cartProduct = $cart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->patch(route('cart_product.update', $cartProduct), ['quantity' => 0])
        ->assertSessionHasErrors('quantity');

    $this->actingAs($user)
        ->patch(route('cart_product.update', $cartProduct), ['quantity' => 100])
        ->assertSessionHasErrors('quantity');

    expect($cartProduct->refresh()->quantity)->toBe(1);
});

test('cart owner can update quantity within the allowed range', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createRestaurantCartProduct(
        'Update Restaurant',
        'Update Product',
    );

    $cart = Cart::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
    ]);
    $cartProduct = $cart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->patch(route('cart_product.update', $cartProduct), ['quantity' => 5])
        ->assertRedirect();

    expect($cartProduct->refresh()->quantity)->toBe(5);
});

test('deleting the last item also deletes its empty cart', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createRestaurantCartProduct(
        'Delete Restaurant',
        'Delete Product',
    );

    $cart = Cart::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
    ]);
    $cartProduct = $cart->cartProducts()->create([
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->delete(route('cart_product.destroy', $cartProduct))
        ->assertRedirect();

    $this->assertDatabaseMissing('cart_products', ['id' => $cartProduct->id]);
    $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
});

test('restaurant cart subtotal uses current prices and quantities', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $firstProduct] = createRestaurantCartProduct(
        'Subtotal Restaurant',
        'Subtotal First Product',
        '10.25',
    );

    $category = Category::query()->create(['name' => 'Subtotal Second Category']);
    $secondProduct = Product::query()->create([
        'name' => 'Subtotal Second Product',
        'price' => '3.10',
        'image' => 'products/subtotal-second.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $cart = Cart::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
    ]);
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

    $cart->load('cartProducts.product');

    expect($cart->subtotal())->toBe(29.8);
});

test('restaurant cart page displays only the selected restaurants cart', function () {
    $user = User::factory()->create();
    ['menu' => $firstMenu, 'product' => $firstProduct] = createRestaurantCartProduct(
        'Index First Restaurant',
        'Index First Product',
    );
    ['menu' => $secondMenu, 'product' => $secondProduct] = createRestaurantCartProduct(
        'Index Second Restaurant',
        'Index Second Product',
    );

    foreach ([[$firstMenu, $firstProduct], [$secondMenu, $secondProduct]] as [$menu, $product]) {
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
        ]);
        $cart->cartProducts()->create([
            'product_id' => $product->id,
            'image' => $product->image,
            'quantity' => 1,
        ]);
    }

    $this->actingAs($user)
        ->get(route('menu.cart.index', $firstMenu))
        ->assertOk()
        ->assertViewHas('menu', fn (Menu $menu) => $menu->is($firstMenu))
        ->assertViewHas('cart', fn (Cart $cart) => $cart->menu_id === $firstMenu->id
            && $cart->relationLoaded('menu')
            && $cart->relationLoaded('cartProducts')
            && $cart->cartProducts->every(
                fn (CartProduct $cartProduct) => $cartProduct->relationLoaded('product'),
            ))
        ->assertSeeText($firstMenu->name)
        ->assertSeeText($firstProduct->name)
        ->assertDontSeeText($secondMenu->name)
        ->assertDontSeeText($secondProduct->name);

    $this->actingAs($user)
        ->get(route('menu.cart.index', $secondMenu))
        ->assertOk()
        ->assertViewHas('menu', fn (Menu $menu) => $menu->is($secondMenu))
        ->assertViewHas('cart', fn (Cart $cart) => $cart->menu_id === $secondMenu->id)
        ->assertSeeText($secondMenu->name)
        ->assertSeeText($secondProduct->name);
});
