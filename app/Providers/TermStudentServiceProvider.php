<?php

namespace App\Providers;

use App\Services\SisData\TermStudentService;
use Illuminate\Support\ServiceProvider;

class TermStudentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            TermStudentService::class,
            fn() => new TermStudentService(
                config('services.sisData.key'),
                config('services.sisData.baseUrl'),
                config('services.sisData.singleDataMinutes'),
                config('services.sisData.multiDataMinutes'),
            )
        );
    }
}