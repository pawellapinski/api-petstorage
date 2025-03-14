<?php

namespace App\Providers;

use App\Services\PetstoreService;
use Illuminate\Support\ServiceProvider;

class PetstoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(PetstoreService::class, function ($app) {
            return new PetstoreService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
