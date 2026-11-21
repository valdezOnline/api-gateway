<?php

namespace App\Providers;

use App\Services\IdmsCardData\IdmsCardDataService;
use Illuminate\Support\ServiceProvider;

class IdmsCardDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(IdmsCardDataService::class, 
        fn() => new IdmsCardDataService(
            config('services.idmsCardData.key'),
            config('services.idmsCardData.baseUrl'),
            config('services.idmsCardData.singleDataMinutes'),
            config('services.idmsCardData.multiDataMinutes')
        ));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
