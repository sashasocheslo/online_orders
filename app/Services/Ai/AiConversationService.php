<?php

namespace App\Services\Ai;

use App\Data\AiConversationMessage;
use App\Models\Menu;
use App\Services\Contracts\AiConversationServiceInterface;
use Illuminate\Http\Request;

class AiConversationService implements AiConversationServiceInterface
{
    private const MAX_MESSAGES = 6;

    private const MAX_MESSAGE_LENGTH = 500;

    public function __construct(
        private readonly Request $request,
    ) {}

    public function history(Menu $menu): array
    {
        return collect($this->request->session()->get($this->key($menu), []))
            ->filter(fn (mixed $message): bool => is_array($message)
                && in_array($message['role'] ?? null, ['user', 'assistant'], true)
                && is_string($message['content'] ?? null))
            ->take(-self::MAX_MESSAGES)
            ->map(fn (array $message): AiConversationMessage => new AiConversationMessage(
                role: $message['role'],
                content: $message['content'],
            ))
            ->values()
            ->all();
    }

    public function rememberExchange(Menu $menu, string $question, string $answer): void
    {
        $messages = [
            ...array_map(
                fn (AiConversationMessage $message): array => $message->toArray(),
                $this->history($menu),
            ),
            (new AiConversationMessage('user', $this->normalize($question)))->toArray(),
            (new AiConversationMessage('assistant', $this->normalize($answer)))->toArray(),
        ];

        $this->request->session()->put(
            $this->key($menu),
            array_slice($messages, -self::MAX_MESSAGES),
        );
    }

    public function clear(Menu $menu): void
    {
        $this->request->session()->forget($this->key($menu));
    }

    private function key(Menu $menu): string
    {
        $userId = $this->request->user()?->getAuthIdentifier();

        abort_if($userId === null, 401);

        return sprintf(
            'ai.conversations.user.%s.menu.%d',
            $userId,
            $menu->id,
        );
    }

    private function normalize(string $message): string
    {
        return mb_substr(trim($message), 0, self::MAX_MESSAGE_LENGTH);
    }
}
