<?php

namespace App\Services\Ai;

use App\Enums\AiProvider;
use App\Exceptions\AiProviderException;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Services\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenAiProvider implements AiProviderInterface
{
    public function provider(): AiProvider
    {
        return AiProvider::OpenAi;
    }

    public function configured(): bool
    {
        return filled(config('services.ai.openai.key'))
            && filled(config('services.ai.openai.model'));
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        if (! $this->configured()) {
            throw new AiProviderNotConfiguredException('ChatGPT поки не налаштовано.');
        }

        try {
            $response = Http::baseUrl((string) config('services.ai.openai.base_url'))
                ->withToken((string) config('services.ai.openai.key'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(2, 250)
                ->post('/responses', [
                    'model' => (string) config('services.ai.openai.model'),
                    'instructions' => $systemPrompt,
                    'input' => $userPrompt,
                    'max_output_tokens' => 500,
                    'reasoning' => [
                        'effort' => 'none',
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'menu_recommendation',
                            'strict' => true,
                            'schema' => $this->responseSchema(),
                        ],
                    ],
                    'store' => false,
                ])
                ->throw();
        } catch (Throwable $exception) {
            report($exception);

            throw new AiProviderException(
                'ChatGPT тимчасово не відповідає. Спробуйте пізніше.',
                previous: $exception,
            );
        }

        $text = collect($response->json('output', []))
            ->flatMap(fn (array $item): array => $item['content'] ?? [])
            ->filter(fn (array $item): bool => ($item['type'] ?? null) === 'output_text')
            ->pluck('text')
            ->filter(fn (mixed $part): bool => is_string($part) && trim($part) !== '')
            ->implode("\n");

        if ($text === '') {
            throw new AiProviderException('ChatGPT повернув порожню відповідь.');
        }

        return trim($text);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'message' => [
                    'type' => 'string',
                ],
                'product_ids' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                    ],
                    'maxItems' => 3,
                ],
            ],
            'required' => ['message', 'product_ids'],
            'additionalProperties' => false,
        ];
    }
}
