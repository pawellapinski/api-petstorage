<?php

namespace App\Providers;

use App\Services\PetstoreService;
use App\Services\PetDataProcessor;
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

        $this->app->singleton(PetDataProcessor::class, function ($app) {
            return new PetDataProcessor();
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
