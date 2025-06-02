<?php

namespace App\Livewire\KanbanImpediments;

use App\Models\Labor;
use App\Models\LaborImpediment;
use Livewire\Component;

class ImpedimentHeader extends Component
{
    public LaborImpediment $registro;
    
    public function mount(LaborImpediment $record)
    {
        $this->registro = $record;
    }
    public function render()
    {
        return view('livewire.kanban-impediments.impediment-header');
    }
}
