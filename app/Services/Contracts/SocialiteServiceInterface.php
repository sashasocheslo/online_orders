<?php

namespace App\Services\Contracts;

use App\Models\User;

interface SocialiteServiceInterface
{
    public function loginWithGoogle(): ?User;
}
