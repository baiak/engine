<?php
namespace App\Http\Livewire;

use Livewire\Component;

class EditLaborStatusModal extends Component
{
    public $recordId;

    protected $listeners = ['openModal'];

    public function openModal($recordId)
    {
        $this->recordId = $recordId;
        $this->dispatchBrowserEvent('open-modal');
    }

    public function render()
    {
        return view('livewire.edit-labor-status-modal');
    }
}
