<?php

namespace App\Enums;

enum AiProvider: string
{
    case OpenAi = 'openai';
    case Gemini = 'gemini';
    case Claude = 'claude';

    public function label(): string
    {
        return match ($this) {
            self::OpenAi => 'ChatGPT',
            self::Gemini => 'Gemini',
            self::Claude => 'Claude',
        };
    }
}
