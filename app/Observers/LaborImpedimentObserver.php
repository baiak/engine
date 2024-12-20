<?php

namespace App\Observers;

use App\Models\LaborImpediment;
use Illuminate\Support\Facades\DB;

class LaborImpedimentObserver
{
    /**
     * Handle the LaborImpediment "created" event.
     */
    public function created(LaborImpediment $laborImpediment): int
    {
        $getTotalFromDB = DB::table('labor_impediments')
            ->where('service_labor_id',  $laborImpediment)
            ->count();
        $total = $getTotalFromDB;

        return $total;
    }

    /**
     * Handle the LaborImpediment "updated" event.
     */
    public function updated(LaborImpediment $laborImpediment): void
    {
        $laborImpediment->logs[] = [
            'updated_at' => now(),
            'changes' => $laborImpediment->getChanges(),
        ];
    }

    /**
     * Handle the LaborImpediment "deleted" event.
     */
    public function deleted(LaborImpediment $laborImpediment): void
    {
        //
    }

    /**
     * Handle the LaborImpediment "restored" event.
     */
    public function restored(LaborImpediment $laborImpediment): void
    {
        //
    }

    /**
     * Handle the LaborImpediment "force deleted" event.
     */
    public function forceDeleted(LaborImpediment $laborImpediment): void
    {
        //
    }
}
