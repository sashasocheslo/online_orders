<?php

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function createProductForAuthorizationTest(): array
{
    $menu = Menu::query()->create([
        'name' => 'Authorization Test Menu',
        'image' => 'menu.png',
    ]);

    $category = Category::query()->create([
        'name' => 'Authorization Test Category',
    ]);

    $product = Product::query()->create([
        'name' => 'Authorization Test Product',
        'price' => 100,
        'image' => 'product.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    return compact('menu', 'product');
}

test('guest is redirected from product management to login', function () {
    ['menu' => $menu] = createProductForAuthorizationTest();

    $this->get(route('menu.products.create', $menu))
        ->assertRedirect(route('login'));
});

test('authenticated user can log out through the web route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('auth.destroy'))
        ->assertRedirect(route('menu.index'));

    $this->assertGuest();
});

test('authenticated user can add a comment through the web route', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createProductForAuthorizationTest();

    $this->actingAs($user)
        ->post(route('products.comments.store', [$menu, $product]), [
            'content' => 'Comment created through the web route.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'content' => 'Comment created through the web route.',
    ]);
});

test('cart owner can increase and decrease product quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::query()->create(['user_id' => $user->id]);
    ['product' => $product] = createProductForAuthorizationTest();

    $cartProduct = CartProduct::query()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->post(route('cart_product.store'), [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ])
        ->assertRedirect();

    expect($cartProduct->refresh()->quantity)->toBe(2);

    $this->actingAs($user)
        ->delete(route('cart_product.destroy', $cartProduct))
        ->assertRedirect();

    expect($cartProduct->refresh()->quantity)->toBe(1);
});

test('user cannot change another users cart', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $cart = Cart::query()->create(['user_id' => $owner->id]);
    ['product' => $product] = createProductForAuthorizationTest();

    $cartProduct = CartProduct::query()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 1,
    ]);

    $this->actingAs($stranger)
        ->post(route('cart_product.store'), [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->delete(route('cart_product.destroy', $cartProduct))
        ->assertForbidden();

    $this->actingAs($stranger)
        ->get(route('cart_product.index'))
        ->assertOk()
        ->assertDontSeeText($product->name);

    expect($cartProduct->refresh()->quantity)->toBe(1);
});

test('regular user cannot manage products', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = createProductForAuthorizationTest();

    $this->actingAs($user)
        ->get(route('menu.products.edit', [$menu, $product]))
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('menu.products.destroy', [$menu, $product]))
        ->assertForbidden();

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('administrator can manage products', function () {
    $admin = User::factory()->admin()->create();
    ['menu' => $menu, 'product' => $product] = createProductForAuthorizationTest();

    $this->actingAs($admin)
        ->get(route('menu.products.edit', [$menu, $product]))
        ->assertOk();

    $this->actingAs($admin)
        ->delete(route('menu.products.destroy', [$menu, $product]))
        ->assertRedirect(route('menu.show', $menu));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('user can delete own comment', function () {
    $owner = User::factory()->create();
    ['product' => $product] = createProductForAuthorizationTest();

    $comment = Comment::query()->create([
        'content' => 'Own comment.',
        'user_id' => $owner->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($owner)
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect();

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('regular user cannot delete another users comment', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    ['product' => $product] = createProductForAuthorizationTest();

    $comment = Comment::query()->create([
        'content' => 'Protected comment.',
        'user_id' => $owner->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($stranger)
        ->delete(route('comments.destroy', $comment))
        ->assertForbidden();

    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
});

test('administrator can delete another users comment', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    ['product' => $product] = createProductForAuthorizationTest();

    $comment = Comment::query()->create([
        'content' => 'Comment for moderation.',
        'user_id' => $owner->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect();

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});
