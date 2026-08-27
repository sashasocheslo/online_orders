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

test('product belongs to its menu and category', function () {
    // Arrange: записи існують тільки у SQLite :memory:.
    $menu = Menu::query()->create([
        'name' => 'Тестове меню',
        'image' => 'menu.png',
    ]);

    $category = Category::query()->create([
        'name' => 'Тестова категорія',
    ]);

    $product = Product::query()->create([
        'name' => 'Тестовий продукт',
        'price' => 120,
        'description' => 'Тестовий опис.',
        'image' => 'product.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    // Assert: прямі та зворотні relationships.
    expect($product->menu->is($menu))->toBeTrue()
        ->and($product->category->is($category))->toBeTrue()
        ->and($menu->products->modelKeys())->toContain($product->id)
        ->and($category->products->modelKeys())->toContain($product->id)
        ->and($product->price)->toBe('120.00');
});

test('cart item and comment expose correct relationships and casts', function () {
    // Arrange.
    $user = User::factory()->create();

    $menu = Menu::query()->create([
        'name' => 'Тестове меню',
        'image' => 'menu.png',
    ]);

    $cart = Cart::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
    ]);

    $category = Category::query()->create([
        'name' => 'Тестова категорія',
    ]);

    $product = Product::query()->create([
        'name' => 'Тестовий продукт',
        'price' => 120,
        'image' => 'product.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $cartProduct = CartProduct::query()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'image' => $product->image,
        'quantity' => 3,
    ]);

    $comment = Comment::query()->create([
        'content' => 'Тестовий коментар.',
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    // Assert.
    expect($user->carts->modelKeys())->toContain($cart->id)
        ->and($cart->menu->is($menu))->toBeTrue()
        ->and($cartProduct->cart->is($cart))->toBeTrue()
        ->and($cartProduct->product->is($product))->toBeTrue()
        ->and($cartProduct->quantity)->toBe(3)
        ->and($product->cartProducts->modelKeys())->toContain($cartProduct->id)
        ->and($comment->user->is($user))->toBeTrue()
        ->and($comment->product->is($product))->toBeTrue();
});
