<?php

namespace App\Livewire\Kanban;

use Livewire\Component;
use App\Enums\TypeOfLaborStatus;
use App\Models\ServiceLabor;

class LaborStatusManager extends Component
{

    public $laborPivotId;
    public $status;
    public $recordId;
    public $laborId = null; // ID da mão de obra

    public function mount($laborPivotId, $status, $recordId, $laborId = null)
    {
        $this->laborPivotId = $laborPivotId;
        $this->status = $status;
        $this->recordId = $recordId;
        $this->laborId = $laborId;
    }


    public function updateStatus($status)
    {
        $this->status = $status;
        $this->dispatch('laborStatusUpdated', laborPivotId: $this->laborPivotId, status: $this->status);
    } 


    public function render()
    { 
        if($this->laborId){
            $laborTitle = app('laborTitle')($this->laborId);
        }


        return view('livewire.kanban.labor-status-manager', [
            'laborTitle' =>$laborTitle
        ]);
    }
}