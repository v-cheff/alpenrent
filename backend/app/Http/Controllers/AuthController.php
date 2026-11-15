<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user and generate access token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
        ]);

        $plainToken = Str::random(60);
        $user->tokens()->create([
            'token' => hash('sha256', $plainToken),
        ]);

        return response()->json([
            'user' => $user->toApiArray(),
            'token' => $plainToken,
            'message' => 'Registration successful.',
        ], 201);
    }

    /**
     * Authenticate user credentials and return an access token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($validated['email'])))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
                'errors' => [
                    'email' => ['Invalid email or password.'],
                ],
            ], 422);
        }

        $plainToken = Str::random(60);
        $user->tokens()->create([
            'token' => hash('sha256', $plainToken),
        ]);

        return response()->json([
            'user' => $user->toApiArray(),
            'token' => $plainToken,
            'message' => 'Logged in successfully.',
        ]);
    }

    /**
     * Get current authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->toApiArray(),
        ]);
    }

    /**
     * Revoke current token / log out user.
     */
    public function logout(Request $request): JsonResponse
    {
        $tokenString = $request->bearerToken();

        if ($tokenString) {
            PersonalAccessToken::query()
                ->where('token', hash('sha256', $tokenString))
                ->orWhere('token', $tokenString)
                ->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
