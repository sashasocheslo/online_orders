<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class AiProviderException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly bool $fallbackAllowed = true,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
