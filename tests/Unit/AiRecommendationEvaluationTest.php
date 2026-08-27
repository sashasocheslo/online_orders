<?php

use App\Data\AiRecommendationResult;
use App\Services\Ai\AiRecommendationBaseline;
use App\Services\Ai\AiRecommendationEvaluator;

test('evaluation baseline respects an explicit budget and picks the cheapest products', function () {
    $products = [
        ['id' => 1, 'price' => 120],
        ['id' => 2, 'price' => 70],
        ['id' => 3, 'price' => 95],
        ['id' => 4, 'price' => 40],
    ];

    $result = (new AiRecommendationBaseline)->recommend(
        $products,
        'Порадь щось до 100,00 грн',
    );

    expect($result->productIds)->toBe([4, 2, 3])
        ->and($result->message)->toContain('до 100 грн');
});

test('evaluation baseline is deterministic when a budget is absent', function () {
    $products = [
        ['id' => 3, 'price' => 90],
        ['id' => 2, 'price' => 70],
        ['id' => 1, 'price' => 70],
        ['id' => 4, 'price' => 100],
    ];
    $baseline = new AiRecommendationBaseline;

    $first = $baseline->recommend($products, 'Порадь основну страву');
    $second = $baseline->recommend(array_reverse($products), 'Порадь основну страву');

    expect($first->productIds)->toBe([1, 2, 3])
        ->and($second->productIds)->toBe($first->productIds);
});

test('evaluator calculates annotated quality metrics without hiding failures', function () {
    $evaluator = new AiRecommendationEvaluator;
    $scenario = [
        'id' => 'vegetarian-main',
        'allowed_product_ids' => [3],
        'expected_product_ids' => [3],
    ];

    $matching = $evaluator->observation(
        scenario: $scenario,
        knownProductIds: [1, 2, 3],
        method: 'ai',
        result: new AiRecommendationResult('Влучна рекомендація.', [3]),
        validResponse: true,
        durationMs: 12.3456,
        externalCalls: 1,
    );
    $unknown = $evaluator->observation(
        scenario: $scenario,
        knownProductIds: [1, 2, 3],
        method: 'ai',
        result: new AiRecommendationResult('Невідомий товар.', [999]),
        validResponse: true,
        durationMs: 20,
        externalCalls: 1,
    );
    $invalid = $evaluator->observation(
        scenario: $scenario,
        knownProductIds: [1, 2, 3],
        method: 'ai',
        result: null,
        validResponse: false,
        durationMs: 30,
        externalCalls: 1,
        error: 'InvalidPayload',
    );

    $summary = $evaluator->summary([$matching, $unknown, $invalid]);

    expect($matching['known_products'])->toBeTrue()
        ->and($matching['constraints_matched'])->toBeTrue()
        ->and($matching['expected_match'])->toBeTrue()
        ->and($matching['duration_ms'])->toBe(12.346)
        ->and($unknown['known_products'])->toBeFalse()
        ->and($unknown['constraints_matched'])->toBeFalse()
        ->and($unknown['expected_match'])->toBeFalse()
        ->and($invalid['success'])->toBeFalse()
        ->and($summary['scenario_count'])->toBe(3)
        ->and($summary['successful_runs'])->toBe(2)
        ->and($summary['valid_response_rate'])->toBe(66.67)
        ->and($summary['known_product_rate'])->toBe(33.33)
        ->and($summary['constraint_match_rate'])->toBe(33.33)
        ->and($summary['expected_match_rate'])->toBe(33.33)
        ->and($summary['total_external_calls'])->toBe(3)
        ->and($summary['total_input_tokens'])->toBeNull()
        ->and($summary['total_output_tokens'])->toBeNull();
});

test('an empty recommendation is correct only when the annotation expects no match', function () {
    $evaluator = new AiRecommendationEvaluator;
    $observation = $evaluator->observation(
        scenario: [
            'id' => 'no-match',
            'allowed_product_ids' => [],
            'expected_product_ids' => [],
        ],
        knownProductIds: [1],
        method: 'ai',
        result: new AiRecommendationResult('Немає відповідного товару.', []),
        validResponse: true,
        durationMs: 1,
        externalCalls: 1,
    );

    expect($observation['constraints_matched'])->toBeTrue()
        ->and($observation['expected_match'])->toBeTrue();
});
