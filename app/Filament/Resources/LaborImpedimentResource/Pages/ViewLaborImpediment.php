<?php

namespace App\Filament\Resources\LaborImpedimentResource\Pages;

use App\Filament\Resources\LaborImpedimentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components; // Import the main Components namespace

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
            ->record($this->record) // Pass the current LaborImpediment record
            ->schema([
                // Section for Core Impediment Details
                Components\Section::make('Detalhes do Impedimento')
                    ->schema([
                        Components\TextEntry::make('reason')
                            ->label('Motivo do Impedimento')
                            ->columnSpanFull(),
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('status')
                                    ->label('Status Atual do Impedimento')
                                    ->badge(),
                                Components\TextEntry::make('created_at')
                                    ->label('Data de Criação')
                                    ->dateTime('d/m/Y H:i:s'),
                                Components\TextEntry::make('updated_at')
                                    ->label('Última Atualização')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ])->collapsible(),

                // Section for Involved Parties
                Components\Section::make('Partes Envolvidas')
                    ->schema([
                         Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('complainantUser.name')->label('Reportado Por (Reclamante)'),
                                Components\TextEntry::make('complainedUser.name')->label('Designado Para (Reclamado)'),
                            ])
                    ])->collapsible(),

                // Section for Associated Service and Labor
                Components\Section::make('Contexto do Serviço e Mão de Obra')
                    ->schema([
                        Components\TextEntry::make('serviceLabor.service.order.formatted_title')
                            ->label('Ordem de Serviço')
                            //->url(fn ($record) => $record->serviceLabor?->service?->order ? \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record->serviceLabor->service->order_id]) : null)
                            ->columnSpanFull(),
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('serviceLabor.service.part.title')->label('Peça do Serviço'),
                                Components\TextEntry::make('serviceLabor.labor.title')->label('Mão de Obra Específica'),
                                Components\TextEntry::make('serviceLabor.status')->label('Status da Mão de Obra no Serviço')->badge(),
                                Components\TextEntry::make('serviceLabor.service.description')->label('Descrição do Serviço')->html()->columnSpanFull(),
                            ])
                    ])->collapsible(),
                
                // Section for Logs - This is where we embed the Livewire component
                Components\Section::make('Histórico de Interações e Atualizações')
                    ->id('logs-section') // Optional ID for deep linking
                    ->schema([
                        Components\View::make('display-labor-impediment-logs')
                    ])
                    ->collapsible()
                    ->collapsed(false), // Start expanded by default
            ]);
    }
}