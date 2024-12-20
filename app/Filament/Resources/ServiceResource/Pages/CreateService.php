<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

protected function beforeCreate(): void
{
    Log::info("beforeCreate carregado TESTE AQUI-> user:".auth()->user()->$this->id); // Log para confirmar carregamento
    $recipient= auth()->user()->$this->id;
     Notification::make()
     ->title('Novo serviço no sistema')
     ->sendToDatabase($recipient);
}

}
