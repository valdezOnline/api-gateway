<?php


namespace App\Providers;

use App\Services\ExLibrisAlma\ExLibrisAlmaFeeDataService;
use App\Services\ExLibrisAlma\ExLibrisAlmaPatronDataService;
use App\Services\ExLibrisAlma\ExLibrisAlmaLoanDataService;
use Illuminate\Support\ServiceProvider;

class ExLibrisAlmaDataServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            ExLibrisAlmaFeeDataService::class,
            fn() => new ExLibrisAlmaFeeDataService(
                config('services.exLibris.key'),
                config('services.exLibris.baseUrl'),
                config('services.exLibris.singleDataMinutes'),
                config('services.exLibris.multiDataMinutes'),
            )
        );
        $this->app->singleton(
            ExLibrisAlmaLoanDataService::class,
            fn() => new ExLibrisAlmaLoanDataService(
                config('services.exLibris.key'),
                config('services.exLibris.baseUrl'),
                config('services.exLibris.singleDataMinutes'),
                config('services.exLibris.multiDataMinutes'),
            )
        );
        $this->app->singleton(
            ExLibrisAlmaPatronDataService::class,
            fn() => new ExLibrisAlmaPatronDataService(
                config('services.exLibris.key'),
                config('services.exLibris.baseUrl'),
                config('services.exLibris.singleDataMinutes'),
                config('services.exLibris.multiDataMinutes'),
            )
        );
    }
}