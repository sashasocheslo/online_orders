<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


interface SocialiteServiceInterface
{
    public function loginWithGoogle(): ?User;
}
