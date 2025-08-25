<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;


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
                'password' => Hash::make('password'),
                'google_id' => $googleUser->id,
            ]);

            Auth::login($user);
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }
}
