<?php

namespace Tests\Fixtures;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;

final class AiRecommendationFixture
{
    /**
     * @return array{menu: Menu, category: Category, product: Product}
     */
    public static function catalog(
        string $menuName = "McDonald's",
        string $productName = 'Тестовий бургер AI',
        int $price = 120,
    ): array {
        $menu = Menu::query()->create([
            'name' => $menuName,
            'image' => 'menus/ai-test.png',
        ]);

        $category = Category::query()->create([
            'name' => 'AI тестова категорія',
        ]);

        $product = Product::query()->create([
            'name' => $productName,
            'price' => $price,
            'description' => 'Ситний негострий тестовий бургер.',
            'size' => 'Стандартний',
            'image' => 'products/ai-test.png',
            'menu_id' => $menu->id,
            'category_id' => $category->id,
        ]);

        return compact('menu', 'category', 'product');
    }

    /**
     * @param  list<int>  $productIds
     */
    public static function response(
        string $message = 'Тестова AI-рекомендація.',
        array $productIds = [],
    ): string {
        return json_encode([
            'message' => $message,
            'product_ids' => $productIds,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public static function malformedResponse(): string
    {
        return 'Це не JSON';
    }

    public static function responseWithExtraField(): string
    {
        return json_encode([
            'message' => 'Відповідь із зайвим полем.',
            'product_ids' => [],
            'debug' => 'internal-data',
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
