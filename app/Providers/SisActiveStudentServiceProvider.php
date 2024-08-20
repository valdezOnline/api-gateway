<?php

namespace App\Providers;

use App\Services\SisActiveStudent\SisActiveStudentService;
use Illuminate\Support\ServiceProvider;

class SisActiveStudentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            SisActiveStudentService::class,
            fn() => new SisActiveStudentService(
                config('services.ucrPerson.key'),
                config('services.ucrPerson.baseUrl'),
                config('services.ucrPerson.singleDataMinutes'),
                config('services.ucrPerson.multiDataMinutes'),
            )
        );
    }
}