<?php

namespace App\Data;

use InvalidArgumentException;

final readonly class AiConversationMessage
{
    public function __construct(
        public string $role,
        public string $content,
    ) {
        if (! in_array($role, ['user', 'assistant'], true)) {
            throw new InvalidArgumentException('Unsupported AI conversation role.');
        }
    }

    /**
     * @return array{role: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
