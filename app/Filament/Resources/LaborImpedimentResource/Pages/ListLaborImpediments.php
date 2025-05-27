<?php

namespace App\Filament\Resources\LaborImpedimentResource\Pages;

use App\Filament\Resources\LaborImpedimentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListLaborImpediments extends ListRecords
{
    protected static string $resource = LaborImpedimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Display impediments where the authenticated user is the 'complained_id' **
        return parent::getTableQuery()->where('complained_id', Auth::id());
    }

    // If you want a true card layout, you might need to override the view:
    // protected static string $view = 'filament.resources.labor-impediment-resource.pages.list-labor-impediments';
    // And then create a Blade file at resources/views/filament/resources/labor-impediment-resource/pages/list-labor-impediments.blade.php
    // to loop through $this->getRecords() and display custom cards.
    // Filament's ->contentGrid() on the table definition in the Resource file can also provide a simpler card-like grid.
}