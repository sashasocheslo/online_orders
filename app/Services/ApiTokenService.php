<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Contracts\ApiTokenServiceInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenService implements ApiTokenServiceInterface
{
    private const TOKEN_ABILITIES = ['api:access'];

    private const TOKEN_LIFETIME_DAYS = 7;

    public function register(array $data): User
    {
        return User::query()->forceCreate([
            ...Arr::only($data, ['name', 'email', 'password']),
            'role' => UserRole::User,
            'email_verified_at' => now(),
        ]);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        if (Hash::needsRehash($user->password)) {
            $user->update(['password' => $password]);
        }

        return $user;
    }

    public function issue(User $user, string $deviceName): NewAccessToken
    {
        return $user->createToken(
            $deviceName,
            self::TOKEN_ABILITIES,
            now()->addDays(self::TOKEN_LIFETIME_DAYS),
        );
    }

    public function revokeCurrent(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
