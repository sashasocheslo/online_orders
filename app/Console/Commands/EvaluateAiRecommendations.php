<?php

namespace App\Console\Commands;

use App\Enums\AiProvider;
use App\Services\Ai\AiEvaluationDataset;
use App\Services\Ai\AiEvaluationRunner;
use App\Services\Ai\AiProviderRegistry;
use Illuminate\Console\Command;

class EvaluateAiRecommendations extends Command
{
    protected $signature = 'ai:evaluate
        {--provider= : Optional live provider: gemini, openai or claude}
        {--allow-network : Explicitly allow paid external AI requests}';

    protected $description = 'Compare the deterministic baseline with optional live AI recommendations';

    public function handle(
        AiEvaluationDataset $datasets,
        AiEvaluationRunner $runner,
        AiProviderRegistry $providers,
    ): int {
        $datasetPath = resource_path('ai/evaluation-dataset.json');
        $dataset = $datasets->load($datasetPath);
        $providerName = $this->option('provider');

        if ($providerName !== null && ! is_string($providerName)) {
            $this->error('Назва AI-провайдера має бути рядком.');

            return self::FAILURE;
        }

        $provider = $providerName === null ? null : AiProvider::tryFrom($providerName);

        if ($providerName !== null && $provider === null) {
            $this->error('Невідомий AI-провайдер. Доступні: gemini, openai, claude.');

            return self::FAILURE;
        }

        if ($provider !== null && ! $this->option('allow-network')) {
            $this->error('Live AI-виклики заблоковано. Додайте --allow-network, якщо усвідомлюєте можливу вартість.');

            return self::FAILURE;
        }

        $providerAdapter = $provider === null ? null : $providers->resolve($provider);

        if ($providerAdapter !== null && ! $providerAdapter->configured()) {
            $this->error("{$provider->label()} не налаштовано.");

            return self::FAILURE;
        }

        $baseline = $runner->baseline($dataset);
        $ai = $providerAdapter === null ? null : $runner->provider($dataset, $providerAdapter);
        $report = [
            'dataset' => [
                'version' => $dataset['version'],
                'sha256' => hash_file('sha256', $datasetPath),
                'scenario_count' => count($dataset['scenarios']),
            ],
            'baseline' => $baseline,
            'ai' => $ai,
            'comparison' => $ai === null ? null : $this->comparison($baseline['summary'], $ai['summary']),
            'notes' => [
                'Rates are percentages calculated from every scenario; failed runs are not removed.',
                'Token totals are null because the current provider interface does not expose usage metadata.',
                'external_calls counts logical provider calls; internal HTTP retries are not observable here.',
            ],
        ];

        $this->line(json_encode(
            $report,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int|float|null>  $baseline
     * @param  array<string, int|float|null>  $ai
     * @return array<string, float>
     */
    private function comparison(array $baseline, array $ai): array
    {
        return collect([
            'valid_response_rate',
            'known_product_rate',
            'constraint_match_rate',
            'expected_match_rate',
        ])->mapWithKeys(fn (string $metric): array => [
            "{$metric}_difference" => round((float) $ai[$metric] - (float) $baseline[$metric], 2),
        ])->all();
    }
}
