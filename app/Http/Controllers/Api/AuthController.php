<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Contracts\ApiTokenServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{
    public function __construct(
        private readonly ApiTokenServiceInterface $apiTokenService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->apiTokenService->register($request->validated());
        $token = $this->apiTokenService->issue(
            $user,
            (string) $request->validated('device_name'),
        );

        return $this->tokenResponse($user, $token, Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->apiTokenService->authenticate(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        );

        if ($user === null) {
            return response()->json([
                'message' => 'Невірні облікові дані.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->apiTokenService->issue(
            $user,
            (string) $request->validated('device_name'),
        );

        return $this->tokenResponse($user, $token);
    }

    public function me(Request $request): UserResource
    {
        $user = $request->user();
        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        return new UserResource($user);
    }

    public function logout(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $this->apiTokenService->revokeCurrent($user);

        return response()->noContent();
    }

    private function tokenResponse(
        User $user,
        NewAccessToken $token,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user' => new UserResource($user),
        ], $status);
    }
}
