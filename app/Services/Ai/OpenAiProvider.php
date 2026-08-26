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

    /**
     * @param  list<AiConversationMessage>  $history
     */
    public function generate(string $systemPrompt, string $userPrompt, array $history = []): string
    {
        if (! $this->configured()) {
            throw new AiProviderNotConfiguredException('ChatGPT поки не налаштовано.');
        }

        $input = collect($history)
            ->map(fn (AiConversationMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->push(['role' => 'user', 'content' => $userPrompt])
            ->all();

        try {
            $response = Http::baseUrl((string) config('services.ai.openai.base_url'))
                ->withToken((string) config('services.ai.openai.key'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(AiHttpRetryPolicy::delays(), 0, [AiHttpRetryPolicy::class, 'shouldRetry'])
                ->post('/responses', [
                    'model' => (string) config('services.ai.openai.model'),
                    'instructions' => $systemPrompt,
                    'input' => $input,
                    'max_output_tokens' => 500,
                    'reasoning' => ['effort' => 'none'],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'menu_recommendation',
                            'strict' => true,
                            'schema' => AiRecommendationSchema::definition(),
                        ],
                    ],
                    'store' => false,
                ])
                ->throw();
        } catch (RequestException $exception) {
            $status = $exception->response->status();

            Log::warning('OpenAI API request failed.', [
                'provider' => $this->provider()->value,
                'status' => $status,
                'request_id' => $exception->response->header('x-request-id'),
            ]);

            throw new AiProviderException(
                match ($status) {
                    400 => 'ChatGPT відхилив запит. Перевірте назву моделі.',
                    401, 403 => 'ChatGPT не прийняв API-ключ. Перевірте ключ і його дозволи.',
                    404 => 'Обрана модель ChatGPT недоступна. Перевірте OPENAI_MODEL.',
                    429 => 'Ліміт запитів ChatGPT вичерпано. Спробуйте пізніше.',
                    default => 'ChatGPT тимчасово не відповідає. Спробуйте пізніше.',
                },
                previous: $exception,
                fallbackAllowed: AiHttpRetryPolicy::fallbackAllowedForStatus($status),
            );
        } catch (ConnectionException $exception) {
            Log::warning('Could not connect to OpenAI API.', [
                'provider' => $this->provider()->value,
                'exception' => $exception::class,
            ]);

            throw new AiProviderException(
                'Не вдалося з’єднатися з ChatGPT. Перевірте інтернет-з’єднання контейнера.',
                previous: $exception,
            );
        } catch (Throwable $exception) {
            Log::warning('Unexpected OpenAI provider error.', [
                'provider' => $this->provider()->value,
                'exception' => $exception::class,
            ]);

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
}
