<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\ServiceLabor;
use App\Observers\ServiceLaborObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\ServiceObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Service::observe(ServiceObserver::class);
        ServiceLabor::observe(ServiceLaborObserver::class);
    }
}
