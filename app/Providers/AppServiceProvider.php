<?php

namespace App\Providers;

use App\Models\LaborImpediment;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use App\Observers\LaborImpedimentObserver;
use App\Observers\ServiceLaborObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\ServiceObserver;
use App\Constants\AppConstants;
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
        app()->singleton('userAvatar', function () {
            return function ($userId) {
                $user = User::query()->where('id', $userId)->first();

                if ($user) {
                         return '
                         <div class="w-10 h-10 rounded-full overflow-hidden">
                              <img src="'.AppConstants::SITE_URL.'storage/'.$user->profileImg.'" class="w-10 h-10 object-cover">

                         </div>';
                } else {
                    return 'User Not Found';
                }
            };
        });

        Service::observe(ServiceObserver::class);
        ServiceLabor::observe(ServiceLaborObserver::class);
        LaborImpediment::observe(LaborImpedimentObserver::class);
    }
}
