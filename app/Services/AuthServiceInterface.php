<?php

namespace App\Services;

interface AuthServiceInterface
{
    public function login(array $credentials, bool $remember): bool;

    public function register(array $data, bool $remember);

    public function logout(): void;
}
