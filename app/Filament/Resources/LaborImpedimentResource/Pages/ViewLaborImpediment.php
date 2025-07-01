<?php

namespace App\Filament\Resources\LaborImpedimentResource\Pages;

use App\Filament\Resources\LaborImpedimentResource;
use Dom\Text;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components; // Import the main Components namespace
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;

class ViewLaborImpediment extends ViewRecord
{
    protected static string $resource = LaborImpedimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(), // If you have an Edit page
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make()
                    ->schema([
                        Fieldset::make('')
                            ->label(fn($record) => 'OS nº ' . $record->serviceLabor->service->order->order_number)
                            ->schema([
                                TextEntry::make('serviceLabor.service.order.client.name')->label('Cliente'),
                                TextEntry::make('serviceLabor.service.order.vehicle.formatted_vehicle')->label('Veículo'),
                         
                                TextEntry::make('serviceLabor.service.part.title')->label('Peça'),
                                TextEntry::make('createdAtt')->label('Impedimento adicionado em:')
                                    ->state(
                                        function ($record) {
                                            return \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i');
                                        }
                                    )
                                    ->columnSpanFull(),
                                TextEntry::make('serviceLabor.labor.code')->label('Última atualização:')
                                    ->state(
                                        function ($record) {
                                            return \Carbon\Carbon::parse($record->serviceLabor->updated_at)->format('d/m/Y H:i');
                                        }
                                    )
                                    ->columnSpanFull(),
                                TextEntry::make('serviceLabor.labor.title')->label('Mão de Obra')
                                    ->columnSpanFull(),
                                TextEntry::make('status')
                                    ->label('Status do impedimento:')
                                    ->columnSpanFull(),

                            ])
                            ->columnStart(1)
                            ->columnSpan(1)
                            ->columns(2),


                        Fieldset::make('Dados do Impedimento')
                            ->schema([
                                ViewEntry::make('impediments')
                                    ->view('components.impediments.header-wrapper')
                                    ->columnSpanFull(),
                            ])
                            ->columnStart(2)
                            ->columnSpan(2)
                            ->columns(2),
                    ])
                    ->columns(3),
            ])
            ->columns(4);
    }
}
