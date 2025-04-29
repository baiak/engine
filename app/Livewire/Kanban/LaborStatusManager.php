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

    // Necessário para ajudar no rehydrate
    public $statusOptions;
    public $currentStatusValue;

    public function mount($laborPivotId, $status, $recordId)
    {
        $this->laborPivotId = $laborPivotId;
        $this->status = $status;
        $this->recordId = $recordId;
        $this->currentStatusValue = $status;

        // Inicializar as opções de status para o Alpine.js
        $this->prepareStatusOptions();
    }

    public function prepareStatusOptions()
    {
        $options = TypeOfLaborStatus::cases();
        $this->statusOptions = array_map(function($option) {
            return [
                'value' => $option->value,
                'label' => $option->getLabel(),
                'style' => $option->getStyle(),
                'icon' => method_exists($option, 'getIcon') ? $option->getIcon() : '',
                'color' => method_exists($option, 'getColor') ? $option->getColor() : ''
            ];
        }, $options);
    }

    public function updateStatus($newStatus)
    {
        try {
            $model = ServiceLabor::find($this->laborPivotId);
            if ($model) {
                $model->status = $newStatus;
                $model->save();

                $this->status = $newStatus;
                $this->currentStatusValue = $newStatus;

                $this->dispatch('status-updated', [
                    'laborPivotId' => $this->laborPivotId,
                    'newStatus' => $newStatus,
                    'recordId' => $this->recordId
                ]);

                return ['success' => true];
            }

            return ['success' => false, 'message' => 'Registro não encontrado'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function render()
    {
        $statusOptions = TypeOfLaborStatus::cases();

        // Preparar currentStatus como array associativo
        $currentStatus = [];
        foreach ($statusOptions as $option) {
            if ($option->value === $this->status) {
                $currentStatus = [
                    'value' => $option->value,
                    'label' => $option->getLabel(),
                    'style' => $option->getStyle(),
                    'icon' => method_exists($option, 'getIcon') ? $option->getIcon() : '',
                    'color' => method_exists($option, 'getColor') ? $option->getColor() : ''
                ];
                break;
            }
        }

        // Preparar options como array de arrays associativos
        $optionsArray = array_map(function($option) {
            return [
                'value' => $option->value,
                'label' => $option->getLabel(),
                'style' => $option->getStyle(),
                'icon' => method_exists($option, 'getIcon') ? $option->getIcon() : '',
                'color' => method_exists($option, 'getColor') ? $option->getColor() : ''
            ];
        }, $statusOptions);

        return view('livewire.kanban.labor-status-manager', [
            'statusOptions' => $this->statusOptions,
            'currentStatus' => collect($this->statusOptions)
                ->firstWhere('value', $this->status),
            'status' => $this->status,
            'laborPivotId' => $this->laborPivotId
        ]);
    }

    public function dehydrate()
    {
        $this->dispatch('reinitializeAlpine', [
            'laborPivotId' => $this->laborPivotId,
            'status' => $this->status
        ])->to('livewire.kanban.labor-status-manager');
    }
}
