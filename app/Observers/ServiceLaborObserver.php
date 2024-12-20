<?php

namespace App\Observers;

use App\Models\ServiceLabor;
use App\Models\ServiceLaborLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceLaborObserver
{
    /**
     * Handle the ServiceLabor "created" event.
     */
    public function created(ServiceLabor $serviceLabor):void
    {


        DB::table('service_labor_logs')->insert([
            'service_labor_id' => $serviceLabor->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => json_encode($serviceLabor->getAttributes()),
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Handle the ServiceLabor "updated" event.
     */
    public function updated(ServiceLabor $serviceLabor): void
    {

       // Log::info('ServiceLabor atualizado event triggered.', ['id' => $serviceLabor->id]);


    }


    /**
     * Handle the ServiceLabor "deleted" event.
     */
    public function deleted(ServiceLabor $serviceLabor): void
    {
        DB::table('service_labor_logs')->insert([
            'service_labor_id' => $serviceLabor->id,

            'event' => 'deleted',
            'old_values' => json_encode($serviceLabor->getOriginal()),
            'new_values' => json_encode($serviceLabor->getChanges()),
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
