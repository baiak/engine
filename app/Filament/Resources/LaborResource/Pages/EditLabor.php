<?php

namespace App\Filament\Resources\LaborResource\Pages;

use App\Filament\Resources\LaborResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLabor extends EditRecord
{
    protected static string $resource = LaborResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
