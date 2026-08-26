<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Throwable;

final class AiHttpRetryPolicy
{
    /**
     * @return list<int>
     */
    public static function delays(): array
    {
        return [250, 750];
    }

    public static function shouldRetry(Throwable $exception, PendingRequest $request): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if (! $exception instanceof RequestException) {
            return false;
        }

        $status = $exception->response->status();

        return in_array($status, [408, 429], true) || $status >= 500;
    }

    public static function fallbackAllowedForStatus(int $status): bool
    {
        return in_array($status, [408, 429], true) || $status >= 500;
    }
}
