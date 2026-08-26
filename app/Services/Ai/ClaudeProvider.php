<?php

namespace App\Services\Ai;

use App\Data\AiConversationMessage;
use App\Enums\AiProvider;
use App\Exceptions\AiProviderException;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Services\Contracts\AiProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    /**
     * @param  list<AiConversationMessage>  $history
     */
    public function generate(string $systemPrompt, string $userPrompt, array $history = []): string
    {
        if (! $this->configured()) {
            throw new AiProviderNotConfiguredException('Claude поки не налаштовано.');
        }

        $messages = collect($history)
            ->map(fn (AiConversationMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->push(['role' => 'user', 'content' => $userPrompt])
            ->all();

        try {
            $response = Http::baseUrl((string) config('services.ai.anthropic.base_url'))
                ->withHeaders([
                    'x-api-key' => (string) config('services.ai.anthropic.key'),
                    'anthropic-version' => (string) config('services.ai.anthropic.version'),
                ])
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(AiHttpRetryPolicy::delays(), 0, [AiHttpRetryPolicy::class, 'shouldRetry'])
                ->post('/messages', [
                    'model' => (string) config('services.ai.anthropic.model'),
                    'max_tokens' => 500,
                    'system' => $systemPrompt,
                    'output_config' => [
                        'format' => [
                            'type' => 'json_schema',
                            'schema' => AiRecommendationSchema::definition(),
                        ],
                    ],
                    'messages' => $messages,
                ])
                ->throw();
        } catch (RequestException $exception) {
            $status = $exception->response->status();

            Log::warning('Claude API request failed.', [
                'provider' => $this->provider()->value,
                'status' => $status,
                'request_id' => $exception->response->header('request-id'),
            ]);

            throw new AiProviderException(
                match ($status) {
                    400 => 'Claude відхилив запит. Перевірте назву моделі.',
                    401, 403 => 'Claude не прийняв API-ключ. Перевірте ключ і його дозволи.',
                    404 => 'Обрана модель Claude недоступна. Перевірте ANTHROPIC_MODEL.',
                    429 => 'Ліміт запитів Claude вичерпано. Спробуйте пізніше.',
                    default => 'Claude тимчасово не відповідає. Спробуйте пізніше.',
                },
                previous: $exception,
                fallbackAllowed: AiHttpRetryPolicy::fallbackAllowedForStatus($status),
            );
        } catch (ConnectionException $exception) {
            Log::warning('Could not connect to Claude API.', [
                'provider' => $this->provider()->value,
                'exception' => $exception::class,
            ]);

            throw new AiProviderException(
                'Не вдалося з’єднатися з Claude. Перевірте інтернет-з’єднання контейнера.',
                previous: $exception,
            );
        } catch (Throwable $exception) {
            Log::warning('Unexpected Claude provider error.', [
                'provider' => $this->provider()->value,
                'exception' => $exception::class,
            ]);

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
}
