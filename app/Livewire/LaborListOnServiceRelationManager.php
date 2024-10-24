<?php

namespace App\Livewire;

use App\Models\ServiceLabor;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Livewire\Component;

class LaborListOnServiceRelationManager extends Component
{
    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        return view('livewire.labor-list-on-service-relation-manager');
    }
}
