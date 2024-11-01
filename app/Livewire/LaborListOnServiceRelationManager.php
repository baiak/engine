<?php
namespace App\Livewire;
Livewire('Livewire.Edit-Labor-Status-Modal');


use App\Enums\TypeOfLaborStatus;
use App\Models\Labor;
use App\Models\ServiceLabor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;

class LaborListOnServiceRelationManager extends Component
{
    public $itemId;
    public $title;
    public $status;
    public $teste;
    public function getStatusOptionsProperty()
    {
        return TypeOfLaborStatus::cases();
    }

    protected $listeners = ['openEditModal' => 'loadItem'];

    public function loadItem($id)
    {
        $item = Labor::find($id); // Substitua "Item" pelo seu modelo.
        $this->itemId = $item->id;
        $this->title = $item->title;
        $this->status = $item->pivot->status;
    }

    public function save()
    {
        $item = Labor::find($this->itemId);
        $item->pivot->update(['status' => $this->status]);
        $this->emit('itemUpdated');
        $this->dispatchBrowserEvent('closeModal');
    }
    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        return view('livewire.labor-list-on-service-relation-manager');
    }


}
