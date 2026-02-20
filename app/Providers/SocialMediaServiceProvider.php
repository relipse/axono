<?php

namespace App\Providers;

use App\Services\SocialMedia\SocialMediaManager;
use Illuminate\Support\ServiceProvider;

class SocialMediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SocialMediaManager::class, function () {
            return new SocialMediaManager();
        });
    }
}
