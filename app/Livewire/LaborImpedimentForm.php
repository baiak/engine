<?php

namespace App\Livewire;

use App\Models\LaborImpediment;
use Filament\Forms\Components\Livewire;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\ServiceLabor;
use Illuminate\Support\Facades\Auth;

class LaborImpedimentForm extends Component
{
    public $items;
    public $pivot;
    public $users;
    public $service_labor_id;






    public function mount($service_labor_id, $items, $pivot, $users)
    {   $this->items = $items;
        $this->pivot = $pivot;
        $this->users = $users;
        $this->service_labor_id = $service_labor_id;
    }




    public function render()
    {
        return view('livewire.labor-impediment-form', [

        ]);
    }
}
