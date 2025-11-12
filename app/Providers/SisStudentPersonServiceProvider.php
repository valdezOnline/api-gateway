<?php

namespace App\Providers;

use App\Services\SisData\SisStudentPersonService;
use Illuminate\Support\ServiceProvider;

class SisStudentPersonServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            SisStudentPersonService::class,
            fn() => new SisStudentPersonService(
                config('services.sisData.key'),
                config('services.sisData.baseUrl'),
                config('services.sisData.singleDataMinutes'),
                config('services.sisData.multiDataMinutes'),
            )
        );
    }
}