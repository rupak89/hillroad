<?php

namespace App\Providers;

use App\Services\GstService;
use App\Services\RecipeCostService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One GST settings cache per request/job, so cost calculations do not
        // repeatedly query the settings table.
        $this->app->scoped(GstService::class, fn () => new GstService);

        // One cost calculation cache per request/job. `scoped` keeps the cache
        // from leaking between requests under Octane or queue workers.
        $this->app->scoped(RecipeCostService::class, fn ($app) => new RecipeCostService(
            $app->make(GstService::class)
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
