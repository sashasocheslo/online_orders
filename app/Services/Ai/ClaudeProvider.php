<?php

namespace App\Services\Ai;

use App\Enums\AiProvider;
use App\Exceptions\AiProviderException;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Services\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class ClaudeProvider implements AiProviderInterface
{
    public function provider(): AiProvider
    {
        return AiProvider::Claude;
    }

    public function configured(): bool
    {
        return filled(config('services.ai.anthropic.key'))
            && filled(config('services.ai.anthropic.model'));
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        if (! $this->configured()) {
            throw new AiProviderNotConfiguredException('Claude поки не налаштовано.');
        }

        try {
            $response = Http::baseUrl((string) config('services.ai.anthropic.base_url'))
                ->withHeaders([
                    'x-api-key' => (string) config('services.ai.anthropic.key'),
                    'anthropic-version' => (string) config('services.ai.anthropic.version'),
                ])
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(2, 250)
                ->post('/messages', [
                    'model' => (string) config('services.ai.anthropic.model'),
                    'max_tokens' => 500,
                    'system' => $systemPrompt,
                    'output_config' => [
                        'format' => [
                            'type' => 'json_schema',
                            'schema' => $this->responseSchema(),
                        ],
                    ],
                    'messages' => [[
                        'role' => 'user',
                        'content' => $userPrompt,
                    ]],
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new AiProviderException(
                'Claude тимчасово не відповідає. Спробуйте пізніше.',
                previous: $exception,
            );
        }

        $text = collect($response->json('content', []))
            ->filter(fn (array $item): bool => ($item['type'] ?? null) === 'text')
            ->pluck('text')
            ->filter(fn (mixed $part): bool => is_string($part) && trim($part) !== '')
            ->implode("\n");

        if ($text === '') {
            throw new AiProviderException('Claude повернув порожню відповідь.');
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
