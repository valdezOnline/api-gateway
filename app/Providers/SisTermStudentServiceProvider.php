<?php

namespace App\Providers;

use App\Services\SisData\SisTermStudentService;
use Illuminate\Support\ServiceProvider;

class SisTermStudentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            SisTermStudentService::class,
            fn() => new SisTermStudentService(
                config('services.sisTermStudent.key'),
                config('services.sisTermStudent.baseUrl'),
                config('services.sisTermStudent.singleDataMinutes'),
                config('services.sisTermStudent.multiDataMinutes'),
            )
        );
    }
}