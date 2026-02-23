<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\CheckInstalled::class);
        $middleware->alias([
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'claude-worker-admin' => \App\Http\Middleware\ClaudeWorkerAdmin::class,
            'claude-worker-cors' => \App\Http\Middleware\ClaudeWorkerCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
