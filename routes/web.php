<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataFileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostScheduleController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\ClaudeWorkerController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Setup wizard (first-run only)
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup', [SetupController::class, 'run'])->name('setup.run');

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Subscription plans (viewable without login)
Route::get('/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');

// Claude Worker — public marketing page
Route::get('/claude-worker/features', [ClaudeWorkerController::class, 'marketing'])->name('claude-worker.marketing');
Route::get('/claude-worker/screenshots/{name}', [ClaudeWorkerController::class, 'screenshot'])->name('claude-worker.screenshot');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Subscription management
    Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // OAuth routes (outside subscription middleware — connecting is always allowed)
    Route::get('/auth/{platform}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{platform}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

    // Claude Worker Admin (restricted to authorized admins)
    Route::prefix('claude-worker')->middleware('claude-worker-admin')->group(function () {
        Route::get('/', [ClaudeWorkerController::class, 'index'])->name('claude-worker.index');
        Route::post('/launch', [ClaudeWorkerController::class, 'launch'])->name('claude-worker.launch');
        Route::get('/tasks', [ClaudeWorkerController::class, 'tasks'])->name('claude-worker.tasks');
        Route::get('/tasks/{taskId}/logs', [ClaudeWorkerController::class, 'taskLogs'])->name('claude-worker.task-logs');
        Route::post('/tasks/{taskId}/stop', [ClaudeWorkerController::class, 'stopTask'])->name('claude-worker.task-stop');
        Route::get('/runs', [ClaudeWorkerController::class, 'runs'])->name('claude-worker.runs');
        Route::get('/runs/{runId}/diff', [ClaudeWorkerController::class, 'runDiff'])->name('claude-worker.run-diff');
        Route::get('/runs/{runId}/summary', [ClaudeWorkerController::class, 'runSummary'])->name('claude-worker.run-summary');
        Route::get('/runs/{runId}/logs', [ClaudeWorkerController::class, 'runLogs'])->name('claude-worker.run-logs');
        Route::delete('/runs/{runId}', [ClaudeWorkerController::class, 'deleteRun'])->name('claude-worker.run-delete');
        Route::get('/docker/workers', [ClaudeWorkerController::class, 'dockerWorkers'])->name('claude-worker.docker.workers');
        Route::get('/docker/logs/{name}', [ClaudeWorkerController::class, 'dockerLogs'])->name('claude-worker.docker.logs');
        Route::post('/docker/stop/{name}', [ClaudeWorkerController::class, 'dockerStop'])->name('claude-worker.docker.stop');
        Route::post('/docker/stop-all', [ClaudeWorkerController::class, 'dockerStopAll'])->name('claude-worker.docker.stop-all');
    });

    // Routes requiring active subscription
    Route::middleware('subscription')->group(function () {
        // Social accounts
        Route::resource('social-accounts', SocialAccountController::class)->except(['show', 'store']);

        // Posts
        Route::resource('posts', PostController::class);

        // Schedules
        Route::resource('schedules', PostScheduleController::class);
        Route::post('/schedules/{schedule}/toggle', [PostScheduleController::class, 'toggle'])->name('schedules.toggle');

        // Data files (requires file upload capability)
        Route::middleware('subscription:file_upload')->group(function () {
            Route::resource('data-files', DataFileController::class)->except(['edit', 'update']);
        });
    });
});
