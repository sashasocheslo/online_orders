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

test('deleting a product also removes its cart items and comments', function () {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'product-deletion@example.com',
        'password' => 'password',
    ]);

    $menu = Menu::query()->create([
        'name' => 'Test Restaurant',
        'image' => 'restaurants/test.png',
    ]);

    $category = Category::query()->create([
        'name' => 'Test Category',
    ]);

    $product = Product::query()->create([
        'name' => 'Test Product',
        'price' => 100,
        'description' => 'Product used to verify cascade deletion.',
        'image' => 'products/test.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $cart = Cart::query()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
    ]);

    $cartProduct = CartProduct::query()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'image' => $product->image,
    ]);

    $comment = Comment::query()->create([
        'content' => 'Test comment.',
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    expect($product->delete())->toBeTrue();

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->assertDatabaseMissing('cart_products', ['id' => $cartProduct->id]);
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    $this->assertDatabaseHas('menus', ['id' => $menu->id]);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
