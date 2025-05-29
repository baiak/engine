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
                        ->label(fn ($record)=> 'OS nº ' . $record->serviceLabor->service->order->order_number)
                            ->schema([
                                TextEntry::make('serviceLabor.service.order.client.name')->label('Cliente'),
                                TextEntry::make('serviceLabor.service.order.vehicle.formatted_vehicle')->label('Veículo'),
                                TextEntry::make('serviceLabor.service.order.deadline')
                                   ->label('Prazo')
                                   ->state(
                                    function ($record) {
                                        $deadline = $record->serviceLabor->service->order->deadline;
                                        if(!$deadline) {
                                            return 'Sem prazo definido';
                                        }
                                        $date = \Carbon\Carbon::parse($deadline)->format('d/m/Y');
                                        $diff = \Carbon\Carbon::parse($deadline)->diffForHumans(now(), [
                                            'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
                                            'parts' => 2,
                                            'join' => true,
                                        ]);
                                        return "{$date} - ({$diff})";
                                    }
                                   )->columnSpanFull(),
                                //TextEntry::make('serviceLabor.service.part.title')->label('Peça'),

                            ])
                            ->columnStart(1)
                            ->columnSpan(1)
                            ->columns(2),


                            Fieldset::make('Dados do Impedimento')
                            ->schema([
                                Section::make('')
                                   ->label(fn ($record) => 'Impedimento nº ' . $record->id)
                                    ->schema([
                                        TextEntry::make('')
                                            ->label(fn ($record) => 'Peça: ' . $record->serviceLabor->service->part->title),
                                        
                                        TextEntry::make('')
                                            ->label(fn ($record) => 'Reclamante: ' . app()->make('userName')($record->complainant_id)),
                                        
                                        TextEntry::make('status'),
                                        
                                        TextEntry::make('created_at')
                                            ->label('Criado em')    
                                            ->state(fn ($record) => $record->created_at->format('d/m/Y H:i:s')),
                                        TextEntry::make('updated_at')
                                            ->label('Atualizado em')
                                            ->state(fn ($record) => $record->updated_at->format('d/m/Y H:i:s')),
                                    ])
                                    ->columns(3)
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
