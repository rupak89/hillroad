<?php

namespace App\Providers;

use App\Services\RecipeCostService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One cost calculation cache per request/job. `scoped` keeps the cache
        // from leaking between requests under Octane or queue workers.
        $this->app->scoped(RecipeCostService::class, fn () => new RecipeCostService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
