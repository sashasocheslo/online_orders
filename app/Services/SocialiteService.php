<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\SocialiteServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteService implements SocialiteServiceInterface
{
    public function loginWithGoogle(): ?User
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                Auth::login($user);

                return $user;
            }

            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(Str::random(64)),
                'google_id' => $googleUser->id,
            ]);

            Auth::login($user);

            return $user;
        } catch (Throwable $exception) {
            Log::warning('Google OAuth authentication failed.', [
                'exception' => $exception::class,
            ]);

            return null;
        }
    }
}
