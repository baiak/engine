<?php

namespace App\Providers;

use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /*FilamentAsset::register([
            asset('public/css/filament/app.css'),

        ]);
*/
        FilamentAsset::register([
            Css::make('filament-styles', __DIR__.'/../../resources/css/filament.css'),
        ]);



    }
}
