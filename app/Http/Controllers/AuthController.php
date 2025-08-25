<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceInterface;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function loginForm(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'HTML login form is not available for API. Use POST /auth/login.'
            ], 406);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if ($this->authService->login($credentials, $remember)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'user' => \Illuminate\Support\Facades\Auth::user(),
                ], 200);
            }
            return redirect()->intended('/');
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return redirect()->back()->with('error', 'Невірні облікові дані');
    }

    public function registerForm(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'HTML register form is not available for API. Use POST /auth/register.'
            ], 406);
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:users',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $remember = $request->filled('remember');
        $this->authService->register($request->only(['name', 'email', 'password']), $remember);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User registered successfully',
                'user' => \Illuminate\Support\Facades\Auth::user(),
            ], 201);
        }

        return redirect()->intended('/');
    }

    public function destroy(Request $request)
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('menu.index');
    }
}
