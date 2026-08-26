<?php

namespace App\Services\Ai;

use App\Data\AiRecommendationResult;
use App\Models\Product;
use Illuminate\Support\Collection;

final class LocalAiRecommendationFallback
{
    /**
     * @param  Collection<int, Product>  $products
     */
    public function recommend(Collection $products): AiRecommendationResult
    {
        $productIds = $products
            ->sortBy(fn (Product $product): float => (float) $product->price)
            ->take(3)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        return new AiRecommendationResult(
            message: 'AI-сервіс тимчасово недоступний. Ось кілька найдоступніших товарів із цього меню.',
            productIds: $productIds,
        );
    }
}
