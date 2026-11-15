<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenString = $request->bearerToken();

        if (! $tokenString) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $tokenRecord = PersonalAccessToken::query()
            ->where('token', hash('sha256', $tokenString))
            ->orWhere('token', $tokenString)
            ->with('user')
            ->first();

        if (! $tokenRecord || ! $tokenRecord->user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $tokenRecord->update(['last_used_at' => now()]);

        $user = $tokenRecord->user;
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
