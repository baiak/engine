<?php

namespace App\Observers;

use App\Models\LaborImpediment;

class LaborImpedimentObserver
{
    /**
     * Handle the LaborImpediment "created" event.
     */
    public function created(LaborImpediment $laborImpediment): void
    {
        //
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
