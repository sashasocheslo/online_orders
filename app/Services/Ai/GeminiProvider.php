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

class GeminiProvider implements AiProviderInterface
{
    public function provider(): AiProvider
    {
        return AiProvider::Gemini;
    }

    public function configured(): bool
    {
        return filled(config('services.ai.gemini.key'))
            && filled(config('services.ai.gemini.model'));
    }

    /**
     * @param  list<AiConversationMessage>  $history
     */
    public function generate(
        string $systemPrompt,
        string $userPrompt,
        array $history = [],
    ): string {
        if (! $this->configured()) {
            throw new AiProviderNotConfiguredException('Gemini поки не налаштовано.');
        }

        $contents = collect($history)
            ->map(fn (AiConversationMessage $message): array => [
                'role' => $message->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message->content]],
            ])
            ->push([
                'role' => 'user',
                'parts' => [['text' => $userPrompt]],
            ])
            ->all();

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => (string) config('services.ai.gemini.key'),
                ])
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(AiHttpRetryPolicy::delays(), 0, [AiHttpRetryPolicy::class, 'shouldRetry'])
                ->post(sprintf(
                    '%s/models/%s:generateContent',
                    rtrim((string) config('services.ai.gemini.base_url'), '/'),
                    rawurlencode((string) config('services.ai.gemini.model')),
                ), [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => 1000,
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                        'responseJsonSchema' => AiRecommendationSchema::definition(),
                    ],
                ])
                ->throw();
        } catch (RequestException $exception) {
            $status = $exception->response->status();

            Log::warning('Gemini API request failed.', [
                'provider' => $this->provider()->value,
                'status' => $status,
                'request_id' => $exception->response->header('x-request-id'),
            ]);

            throw new AiProviderException(
                match ($status) {
                    400 => 'Gemini відхилив запит. Перевірте назву моделі.',
                    401, 403 => 'Gemini не прийняв API-ключ. Перевірте ключ і його обмеження.',
                    404 => 'Обрана модель Gemini недоступна. Перевірте GEMINI_MODEL.',
                    429 => 'Ліміт запитів Gemini вичерпано. Спробуйте пізніше.',
                    default => 'Gemini тимчасово не відповідає. Спробуйте пізніше.',
                },
                previous: $exception,
                fallbackAllowed: AiHttpRetryPolicy::fallbackAllowedForStatus($status),
            );
        } catch (ConnectionException $exception) {
            Log::warning('Could not connect to Gemini API.', [
                'provider' => $this->provider()->value,
                'exception' => $exception::class,
            ]);

            throw new AiProviderException(
                'Не вдалося з’єднатися з Gemini. Перевірте інтернет-з’єднання контейнера.',
                previous: $exception,
            );
        } catch (Throwable $exception) {
            Log::warning('Unexpected Gemini provider error.', [
                'provider' => $this->provider()->value,
                'exception' => $exception::class,
            ]);

            throw new AiProviderException(
                'Gemini тимчасово не відповідає. Спробуйте пізніше.',
                previous: $exception,
            );
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter(fn (mixed $part): bool => is_string($part) && trim($part) !== '')
            ->implode("\n");

        if ($text === '') {
            throw new AiProviderException('Gemini повернув порожню відповідь.');
        }

        return trim($text);
    }
}
