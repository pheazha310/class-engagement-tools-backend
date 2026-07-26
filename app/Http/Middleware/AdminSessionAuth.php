<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that checks session-based authentication for admin API routes.
 *
 * This is a simpler alternative to Sanctum's `auth:sanctum` guard, which
 * requires the `EnsureFrontendRequestsAreStateful` middleware to properly
 * mark requests as stateful. For admin API routes consumed by the Vue SPA
 * (loaded via Inertia with session auth from a Blade login), we can check
 * the web session guard directly.
 */
class AdminSessionAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('web')->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
