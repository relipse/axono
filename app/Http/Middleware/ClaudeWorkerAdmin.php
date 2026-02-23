<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClaudeWorkerAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Session-based auth (browser)
        if ($request->session()->get('cw_admin')) {
            return $next($request);
        }

        // Token-based auth (API clients / iOS app / SPA frontend)
        $token = $request->bearerToken() ?? $request->query('api_token');
        if ($token) {
            $password = config('claude-worker.admin_password');
            if ($password && hash_equals($password, $token)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized. Admin login required.'], 403);
        }

        return redirect()->route('claude-worker.login');
    }
}
