<?php

namespace App\Services\Contracts;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

interface ApiTokenServiceInterface
{
    public function register(array $data): User;

    public function authenticate(string $email, string $password): ?User;

    public function issue(User $user, string $deviceName): NewAccessToken;

    public function revokeCurrent(User $user): void;
}
