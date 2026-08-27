<?php

namespace App\Services\Ai;

use App\Services\Contracts\AiProviderInterface;
use Throwable;

final class AiEvaluationRunner
{
    public function __construct(
        private readonly AiRecommendationBaseline $baseline,
        private readonly AiRecommendationEvaluator $evaluator,
        private readonly AiRecommendationParser $parser,
        private readonly AiRecommendationPromptFactory $prompts,
    ) {}

    /**
     * @param  array{products: list<array{id: int, ...}>, scenarios: list<array{id: string, question: string, allowed_product_ids: list<int>, expected_product_ids: list<int>}>, ...}  $dataset
     * @return array{method: string, summary: array<string, int|float|null>, observations: list<array<string, mixed>>}
     */
    public function baseline(array $dataset): array
    {
        $knownProductIds = collect($dataset['products'])->pluck('id')->all();
        $observations = [];

        foreach ($dataset['scenarios'] as $scenario) {
            $startedAt = hrtime(true);
            $result = $this->baseline->recommend($dataset['products'], $scenario['question']);

            $observations[] = $this->evaluator->observation(
                scenario: $scenario,
                knownProductIds: $knownProductIds,
                method: 'baseline',
                result: $result,
                validResponse: true,
                durationMs: $this->durationMs($startedAt),
                externalCalls: 0,
            );
        }

        return $this->report('baseline', $observations);
    }

    /**
     * @param  array{restaurant: string, products: list<array{id: int, ...}>, scenarios: list<array{id: string, question: string, allowed_product_ids: list<int>, expected_product_ids: list<int>}>, ...}  $dataset
     * @return array{method: string, summary: array<string, int|float|null>, observations: list<array<string, mixed>>}
     */
    public function provider(array $dataset, AiProviderInterface $provider): array
    {
        $knownProductIds = collect($dataset['products'])->pluck('id')->all();
        $observations = [];

        foreach ($dataset['scenarios'] as $scenario) {
            $startedAt = hrtime(true);

            try {
                $rawAnswer = $provider->generate(
                    $this->prompts->systemPrompt(),
                    $this->prompts->userPrompt(
                        $dataset['restaurant'],
                        $dataset['products'],
                        $scenario['question'],
                    ),
                );
                $result = $this->parser->parse($rawAnswer);
                $validResponse = true;
                $error = null;
            } catch (Throwable $exception) {
                $result = null;
                $validResponse = false;
                $error = $exception::class;
            }

            $observations[] = $this->evaluator->observation(
                scenario: $scenario,
                knownProductIds: $knownProductIds,
                method: $provider->provider()->value,
                result: $result,
                validResponse: $validResponse,
                durationMs: $this->durationMs($startedAt),
                externalCalls: 1,
                error: $error,
            );
        }

        return $this->report($provider->provider()->value, $observations);
    }

    /**
     * @param  list<array<string, mixed>>  $observations
     * @return array{method: string, summary: array<string, int|float|null>, observations: list<array<string, mixed>>}
     */
    private function report(string $method, array $observations): array
    {
        return [
            'method' => $method,
            'summary' => $this->evaluator->summary($observations),
            'observations' => $observations,
        ];
    }

    private function durationMs(int $startedAt): float
    {
        return (hrtime(true) - $startedAt) / 1_000_000;
    }
}
