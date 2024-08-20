<?php

namespace App\Providers;

use App\Services\UcrPerson\UcrPersonService;
use Illuminate\Support\ServiceProvider;

class UcrPersonServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->singleton(
      UcrPersonService::class,
      fn() => new UcrPersonService(
        config('services.ucrPerson.key'),
        config('services.ucrPerson.baseUrl'),
        config('services.ucrPerson.singleDataMinutes'),
        config('services.ucrPerson.multiDataMinutes'),
      )
    );
  }
}