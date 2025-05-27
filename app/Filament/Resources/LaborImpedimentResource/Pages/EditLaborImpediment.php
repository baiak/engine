<?php

namespace App\Filament\Resources\LaborImpedimentResource\Pages;

use App\Filament\Resources\LaborImpedimentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaborImpediment extends EditRecord
{
    protected static string $resource = LaborImpedimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
