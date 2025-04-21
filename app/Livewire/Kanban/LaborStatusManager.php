<?php

namespace App\Livewire\Kanban;

use Livewire\Component;
use App\Enums\TypeOfLaborStatus;
use App\Models\ServiceLabor; // Substitua pelo nome correto do seu modelo

class LaborStatusManager extends Component
{
    public $laborPivotId;
    public $status;
    public $recordId;
    public $showDropdown = false;
    public $loading = false;

    public function mount($laborPivotId, $status, $recordId)
    {
        $this->laborPivotId = $laborPivotId;
        $this->status = $status;
        $this->recordId = $recordId;
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function updateStatus($newStatus)
    {
        $this->closeDropdown();
        $this->loading = true;

        try {
            // Atualiza no banco de dados
            $model = ServiceLabor::find($this->laborPivotId);
            $model->status = $newStatus;
            $model->save();

            // Atualiza localmente
            $this->status = $newStatus;
        } catch (\Exception $e) {
            // Log do erro
        }

        $this->loading = false;
    }

    public function render()
    {
        $statusOptions = TypeOfLaborStatus::cases();

        $currentStatus = null;
        foreach ($statusOptions as $option) {
            if ($option->value === $this->status) {
                $currentStatus = $option;
                break;
            }
        }

        return view('livewire.kanban.labor-status-manager', [
            'statusOptions' => $statusOptions,
            'currentStatus' => $currentStatus,
        ]);
    }
}
