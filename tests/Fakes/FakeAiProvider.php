<?php

namespace Tests\Fakes;

use App\Data\AiConversationMessage;
use App\Enums\AiProvider;
use App\Services\Contracts\AiProviderInterface;
use LogicException;
use Throwable;

class FakeAiProvider implements AiProviderInterface
{
    /**
     * @var list<array{
     *     system_prompt: string,
     *     user_prompt: string,
     *     history: list<AiConversationMessage>
     * }>
     */
    public array $calls = [];

    /** @var list<string|Throwable> */
    private array $outcomes = [];

    public function __construct(
        private readonly AiProvider $aiProvider = AiProvider::Gemini,
        private readonly bool $isConfigured = true,
    ) {}

    public function provider(): AiProvider
    {
        return $this->aiProvider;
    }

    public function configured(): bool
    {
        return $this->isConfigured;
    }

    public function respondWith(string $response): self
    {
        $this->outcomes[] = $response;

        return $this;
    }

    public function failWith(Throwable $exception): self
    {
        $this->outcomes[] = $exception;

        return $this;
    }

    /**
     * @param  list<AiConversationMessage>  $history
     */
    public function generate(
        string $systemPrompt,
        string $userPrompt,
        array $history = [],
    ): string {
        $this->calls[] = [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'history' => $history,
        ];

        if ($this->outcomes === []) {
            throw new LogicException('Fake AI provider has no configured outcome.');
        }

        $outcome = array_shift($this->outcomes);

        if ($outcome instanceof Throwable) {
            throw $outcome;
        }

        return $outcome;
    }
}
