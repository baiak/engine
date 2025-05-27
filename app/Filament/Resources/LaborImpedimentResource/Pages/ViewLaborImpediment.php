<?php

namespace App\Filament\Resources\LaborImpedimentResource\Pages;

use App\Filament\Resources\LaborImpedimentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLaborImpediment extends ViewRecord
{
    protected static string $resource = LaborImpedimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(), // You can add an edit action here if needed
        ];
    }
}