<?php

namespace App\Services\Ai;

use App\Data\AiRecommendationResult;

final class AiRecommendationEvaluator
{
    /**
     * @param  array{id: string, allowed_product_ids: list<int>, expected_product_ids: list<int>, ...}  $scenario
     * @param  list<int>  $knownProductIds
     * @return array{
     *     scenario: string,
     *     method: string,
     *     success: bool,
     *     valid_response: bool,
     *     known_products: bool,
     *     constraints_matched: bool,
     *     expected_match: bool,
     *     recommended_product_ids: list<int>,
     *     duration_ms: float,
     *     external_calls: int,
     *     input_tokens: int|null,
     *     output_tokens: int|null,
     *     error: string|null
     * }
     */
    public function observation(
        array $scenario,
        array $knownProductIds,
        string $method,
        ?AiRecommendationResult $result,
        bool $validResponse,
        float $durationMs,
        int $externalCalls,
        ?string $error = null,
    ): array {
        $recommendedIds = $result?->productIds ?? [];
        $knownProducts = $validResponse && array_diff($recommendedIds, $knownProductIds) === [];

        return [
            'scenario' => $scenario['id'],
            'method' => $method,
            'success' => $validResponse && $error === null,
            'valid_response' => $validResponse,
            'known_products' => $knownProducts,
            'constraints_matched' => $knownProducts && $this->matchesAnnotation(
                $recommendedIds,
                $scenario['allowed_product_ids'],
            ),
            'expected_match' => $knownProducts && $this->matchesExpected(
                $recommendedIds,
                $scenario['expected_product_ids'],
            ),
            'recommended_product_ids' => $recommendedIds,
            'duration_ms' => round($durationMs, 3),
            'external_calls' => $externalCalls,
            'input_tokens' => null,
            'output_tokens' => null,
            'error' => $error,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $observations
     * @return array<string, int|float|null>
     */
    public function summary(array $observations): array
    {
        $count = count($observations);

        return [
            'scenario_count' => $count,
            'successful_runs' => collect($observations)->where('success', true)->count(),
            'valid_response_rate' => $this->rate($observations, 'valid_response'),
            'known_product_rate' => $this->rate($observations, 'known_products'),
            'constraint_match_rate' => $this->rate($observations, 'constraints_matched'),
            'expected_match_rate' => $this->rate($observations, 'expected_match'),
            'average_duration_ms' => $count === 0
                ? 0.0
                : round((float) collect($observations)->avg('duration_ms'), 3),
            'total_external_calls' => (int) collect($observations)->sum('external_calls'),
            'total_input_tokens' => null,
            'total_output_tokens' => null,
        ];
    }

    /**
     * @param  list<int>  $recommendedIds
     * @param  list<int>  $allowedIds
     */
    private function matchesAnnotation(array $recommendedIds, array $allowedIds): bool
    {
        if ($allowedIds === []) {
            return $recommendedIds === [];
        }

        return $recommendedIds !== [] && array_diff($recommendedIds, $allowedIds) === [];
    }

    /**
     * @param  list<int>  $recommendedIds
     * @param  list<int>  $expectedIds
     */
    private function matchesExpected(array $recommendedIds, array $expectedIds): bool
    {
        if ($expectedIds === []) {
            return $recommendedIds === [];
        }

        return array_intersect($recommendedIds, $expectedIds) !== [];
    }

    /** @param  list<array<string, mixed>>  $observations */
    private function rate(array $observations, string $metric): float
    {
        if ($observations === []) {
            return 0.0;
        }

        $passed = collect($observations)->filter(
            fn (array $observation): bool => ($observation[$metric] ?? false) === true,
        )->count();

        return round($passed / count($observations) * 100, 2);
    }
}
