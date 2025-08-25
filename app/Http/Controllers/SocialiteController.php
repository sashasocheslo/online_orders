<?php

namespace App\Http\Controllers;

use App\Services\SocialiteServiceInterface;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google OAuth: повертає або редірект, або JSON з URL для редіректу.
 */
class SocialiteController extends Controller
{
    private SocialiteServiceInterface $socialiteService;

    public function __construct(SocialiteServiceInterface $socialiteService)
    {
        $this->socialiteService = $socialiteService;
    }

    public function googleLogin(Request $request)
    {
        $redirect = Socialite::driver('google')->redirect();

        if ($request->wantsJson()) {
            return response()->json(['url' => $redirect->getTargetUrl()], 200);
        }

        return $redirect;
    }

    public function googleAuthentication(Request $request)
    {
        $user = $this->socialiteService->loginWithGoogle();

        if ($request->wantsJson()) {
            if ($user) {
                return response()->json([
                    'message' => 'Google authentication successful',
                    'user' => $user,
                ], 200);
            }
            return response()->json(['message' => 'Google authentication failed'], 401);
        }

        if ($user) {
            return redirect()->route('menu.index');
        }

        return redirect()->route('login')->with('error', 'Помилка авторизації через Google.');
    }
}
