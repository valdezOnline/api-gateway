<?php

namespace App\Providers;

use App\Services\SisData\ActiveStudentService;
use Illuminate\Support\ServiceProvider;

class ActiveStudentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            ActiveStudentService::class,
            fn() => new ActiveStudentService(
                config('services.sisData.key'),
                config('services.sisData.baseUrl'),
                config('services.sisData.singleDataMinutes'),
                config('services.sisData.multiDataMinutes'),
            )
        );
    }
}