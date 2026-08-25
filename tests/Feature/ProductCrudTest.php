<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

function createProductCrudFixtures(): array
{
    $menu = Menu::query()->create([
        'name' => 'CRUD Test Menu',
        'image' => 'menus/crud.png',
    ]);

    $category = Category::query()->create([
        'name' => 'CRUD Test Category',
    ]);

    return compact('menu', 'category');
}

test('administrator can create a valid product with an image', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    ['menu' => $menu, 'category' => $category] = createProductCrudFixtures();

    $response = $this->actingAs($admin)
        ->post(route('menu.products.store', $menu), [
            'name' => 'Новий бургер',
            'price' => '149.99',
            'description' => 'Опис нового бургера.',
            'size' => 'L',
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('burger.png'),
        ]);

    $response->assertRedirect(route('menu.show', $menu));

    $product = Product::query()->where('name', 'Новий бургер')->firstOrFail();

    expect($product->menu->is($menu))->toBeTrue()
        ->and($product->category->is($category))->toBeTrue()
        ->and($product->price)->toBe('149.99');

    Storage::disk('public')->assertExists($product->image);
});

test('invalid product data is rejected', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    ['menu' => $menu] = createProductCrudFixtures();

    $response = $this->actingAs($admin)
        ->from(route('menu.products.create', $menu))
        ->post(route('menu.products.store', $menu), [
            'name' => '',
            'price' => 0,
            'category_id' => 999999,
            'image' => UploadedFile::fake()->create('menu.txt', 10, 'text/plain'),
        ]);

    $response
        ->assertRedirect(route('menu.products.create', $menu))
        ->assertSessionHasErrors(['name', 'price', 'category_id', 'image']);

    $this->assertDatabaseCount('products', 0);
    Storage::disk('public')->assertEmpty();
});

test('administrator can update a product and replace its image', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    ['menu' => $menu, 'category' => $category] = createProductCrudFixtures();

    Storage::disk('public')->put('products/old.jpg', 'old image');

    $product = Product::query()->create([
        'name' => 'Стара назва',
        'price' => 100,
        'description' => 'Старий опис.',
        'image' => 'products/old.jpg',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($admin)
        ->put(route('menu.products.update', [$menu, $product]), [
            'name' => 'Оновлена назва',
            'price' => '120.50',
            'description' => 'Оновлений опис.',
            'size' => 'XL',
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('new.png'),
        ])
        ->assertRedirect(route('menu.show', $menu));

    $product->refresh();

    expect($product->name)->toBe('Оновлена назва')
        ->and($product->price)->toBe('120.50')
        ->and($product->size)->toBe('XL');

    Storage::disk('public')->assertMissing('products/old.jpg');
    Storage::disk('public')->assertExists($product->image);
});

test('product cannot be managed through another menu URL', function () {
    $admin = User::factory()->admin()->create();
    ['menu' => $menu, 'category' => $category] = createProductCrudFixtures();

    $otherMenu = Menu::query()->create([
        'name' => 'Other Menu',
        'image' => 'menus/other.png',
    ]);

    $product = Product::query()->create([
        'name' => 'Scoped product',
        'price' => 100,
        'image' => 'products/scoped.jpg',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($admin)
        ->get(route('menu.products.edit', [$otherMenu, $product]))
        ->assertNotFound();
});

test('administrator deletes a product and its stored image', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    ['menu' => $menu, 'category' => $category] = createProductCrudFixtures();

    Storage::disk('public')->put('products/delete.jpg', 'image');

    $product = Product::query()->create([
        'name' => 'Product to delete',
        'price' => 100,
        'image' => 'products/delete.jpg',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('menu.products.destroy', [$menu, $product]))
        ->assertRedirect(route('menu.show', $menu));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing('products/delete.jpg');
});
