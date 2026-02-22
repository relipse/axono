<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClaudeWorkerAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isClaudeWorkerAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Claude Worker admin access required.'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access the Claude Worker admin panel.');
        }

        return $next($request);
    }
}
