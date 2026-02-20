<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = file_exists(base_path('.env')) && file_exists(storage_path('installed'));

        if (!$isInstalled && !$request->is('setup', 'setup/*')) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
