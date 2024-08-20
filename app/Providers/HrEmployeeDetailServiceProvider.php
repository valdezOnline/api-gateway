<?php

namespace App\Providers;

use App\Services\HrEmployeeDetail\HrEmployeeDetailService;
use Illuminate\Support\ServiceProvider;

class HrEmployeeDetailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            HrEmployeeDetailService::class,
            fn() => new HrEmployeeDetailService(
                config('services.hrEmployeeDetail.key'),
                config('services.hrEmployeeDetail.baseUrl'),
                config('services.hrEmployeeDetail.singleDataMinutes'),
                config('services.hrEmployeeDetail.multiDataMinutes'),
            )
        );
    }
}