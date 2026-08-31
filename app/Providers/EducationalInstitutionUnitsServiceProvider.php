<?php

namespace App\Providers;

use App\Services\SisData\EducationalInstitutionUnitsService;
use Illuminate\Support\ServiceProvider;

class EducationalInstitutionUnitsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            EducationalInstitutionUnitsService::class,
            fn() => new EducationalInstitutionUnitsService(
                config('services.sisData.key'),
                config('services.sisData.baseUrl'),
                config('services.sisData.singleDataMinutes'),
                config('services.sisData.multiDataMinutes'),
            )
        );
    }
}
