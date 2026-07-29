<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that checks session-based authentication for admin API routes.
 *
 * This bypasses Sanctum's `EnsureFrontendRequestsAreStateful` modifications
 * by reading the authenticated user directly from the session and logging
 * them in via the web guard. This is necessary because Vite's dev proxy
 * strips the Origin header (via changeOrigin: true) on state-mutating
 * requests (POST/PUT/DELETE), which causes Sanctum to mis-identify the
 * request as non-stateful and switch to token-based auth.
 */
class AdminSessionAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. First, try the standard web guard check
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // 2. If that fails (Sanctum may have broken the guard), authenticate
        //    directly from the session, bypassing Sanctum's modifications.
        $sessionKey = Auth::guard('web')->getName();
        $userId = $request->session()->get($sessionKey);

        if ($userId !== null) {
            $user = User::find($userId);
            if ($user) {
                Auth::guard('web')->login($user);

                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
