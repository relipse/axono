<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataFileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostScheduleController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Subscription plans (viewable without login)
Route::get('/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');

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

    // Routes requiring active subscription
    Route::middleware('subscription')->group(function () {
        // Social accounts
        Route::resource('social-accounts', SocialAccountController::class)->except(['show']);

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
