<?php

namespace App\Services\Ai;

use App\Data\AiRecommendationResult;

final class AiRecommendationBaseline
{
    /**
     * This deliberately simple baseline understands only a "до N" budget.
     * It then returns up to three cheapest products.
     *
     * @param  list<array{id: int, price: int|float, ...}>  $products
     */
    public function recommend(array $products, string $question): AiRecommendationResult
    {
        $maxPrice = $this->maxPrice($question);

        $productIds = collect($products)
            ->when(
                $maxPrice !== null,
                fn ($products) => $products->filter(
                    fn (array $product): bool => (float) $product['price'] <= $maxPrice,
                ),
            )
            ->sort(function (array $left, array $right): int {
                $priceComparison = (float) $left['price'] <=> (float) $right['price'];

                return $priceComparison !== 0
                    ? $priceComparison
                    : $left['id'] <=> $right['id'];
            })
            ->take(3)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        return new AiRecommendationResult(
            message: $productIds === []
                ? 'Baseline не знайшов товарів у межах указаного бюджету.'
                : 'Baseline обрав найдешевші товари'.($maxPrice === null ? '.' : " до {$maxPrice} грн."),
            productIds: $productIds,
        );
    }

    private function maxPrice(string $question): ?float
    {
        if (! preg_match('/\bдо\s+(\d+(?:[.,]\d{1,2})?)\b/ui', $question, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }
}
