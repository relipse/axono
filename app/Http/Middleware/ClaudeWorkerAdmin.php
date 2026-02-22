<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClaudeWorkerAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('cw_admin')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Admin login required.'], 403);
            }

            return redirect()->route('claude-worker.login');
        }

        return $next($request);
    }
}
