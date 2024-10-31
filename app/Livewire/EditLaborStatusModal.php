<?php
namespace App\Livewire;

use App\Models\ServiceLabor;
use App\Models\User;
use Livewire\Component;


class EditLaborStatusModal extends Component
{
    protected $listeners = ['EditLaborStatusModal' => 'loadData'];

    public function loadData($id)
    {
        $this->labor = ServiceLabor::find($id); // Carrega os dados
        $this->dispatchBrowserEvent('toggle-modal'); // Dispara um evento JavaScript
    }

    public function render()
    {
        return view('livewire.edit-labor-status-modal');
    }
}
