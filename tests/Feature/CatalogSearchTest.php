<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('catalog search filters products by text and price and sorts results', function () {
    // Arrange: готуємо незалежні тестові дані.
    $menu = Menu::query()->create([
        'name' => 'Тестовий ресторан',
        'image' => 'restaurants/test.png',
    ]);

    $category = Category::query()->create([
        'name' => 'Тестові бургери',
    ]);

    $unrelatedCategory = Category::query()->create([
        'name' => 'Тестові піци',
    ]);

    $cheaperBurger = Product::query()->create([
        'name' => 'бургер класичний',
        'price' => 120,
        'description' => 'Тестовий класичний бургер.',
        'image' => 'products/classic.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $expensiveMatchingBurger = Product::query()->create([
        'name' => 'бургер подвійний',
        'price' => 180,
        'description' => 'Тестовий подвійний бургер.',
        'image' => 'products/double.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $overBudgetBurger = Product::query()->create([
        'name' => 'бургер преміум',
        'price' => 250,
        'description' => 'Бургер поза встановленим бюджетом.',
        'image' => 'products/premium.png',
        'menu_id' => $menu->id,
        'category_id' => $category->id,
    ]);

    $unrelatedProduct = Product::query()->create([
        'name' => 'піца маргарита',
        'price' => 100,
        'description' => 'Не відповідає текстовому запиту.',
        'image' => 'products/pizza.png',
        'menu_id' => $menu->id,
        'category_id' => $unrelatedCategory->id,
    ]);

    // Act: імітуємо GET-запит користувача.
    $response = $this->get(route('catalog.search', [
        'query' => 'бургер',
        'max_price' => 200,
        'sort' => 'price_desc',
    ]));

    // Assert: перевіряємо статус, view, склад і порядок результатів.
    $response
        ->assertOk()
        ->assertViewIs('catalog.search')
        ->assertViewHas(
            'products',
            fn ($products) => $products->total() === 2,
        )
        ->assertSeeTextInOrder([
            $expensiveMatchingBurger->name,
            $cheaperBurger->name,
        ])
        ->assertDontSeeText($overBudgetBurger->name)
        ->assertDontSeeText($unrelatedProduct->name);
});

test('catalog search rejects a maximum price lower than the minimum price', function () {
    // Arrange.
    $searchPage = route('catalog.search');

    // Act.
    $response = $this
        ->from($searchPage)
        ->get(route('catalog.search', [
            'min_price' => 300,
            'max_price' => 100,
        ]));

    // Assert.
    $response
        ->assertRedirect($searchPage)
        ->assertSessionHasErrors([
            'max_price' => 'Максимальна ціна повинна бути не меншою за мінімальну.',
        ]);
});
