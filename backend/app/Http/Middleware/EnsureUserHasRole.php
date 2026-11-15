<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:owner'). Must run after auth.token.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user()?->role !== $role) {
            return response()->json([
                'message' => $role === 'owner'
                    ? 'Only rental point owner accounts can do this.'
                    : 'Only renter accounts can do this.',
            ], 403);
        }

        return $next($request);
    }
}
