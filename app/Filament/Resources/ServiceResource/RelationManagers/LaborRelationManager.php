<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use App\Enums\TypeOfLaborStatus;
use App\Models\ServiceLabor;
use App\Models\Labor;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaborRelationManager extends RelationManager
{
    protected static string $relationship = 'labor';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('service_id')
                    ->required()
                    ->default(function (RelationManager $livewire) {
                        return ($livewire->getOwnerRecord()->id);
                    })
                    ->maxLength(255),

                Forms\Components\Select::make('labor_id')
                    ->required()
                    ->options(function (Get $get) {
                        $partIdFromService = Service::find($get('service_id'));
                        return Labor::all()
                            ->where('part_id', $partIdFromService->part_id)
                            ->pluck('title', 'id');
                    }),

                Forms\Components\TextInput::make('part_id')
                    ->required()
                    ->default(function (Get $get) {
                        $partIdFromService = Service::find($get('service_id'));
                        return ($partIdFromService->part_id);
                    }),

                Forms\Components\Placeholder::make('status atual')
                    ->content(function ($record) {
                        if ($record) {
                            return $record->status;
                        }
                        return ('status nao incluso');
                    }),

                Forms\Components\Radio::make('status')
                    ->options(TypeOfLaborStatus::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Lançamento de Mao de obras')
            ->columns([
                Tables\Columns\TextColumn::make('Mao de obra')
                    ->getStateUsing(function (Labor $record): string {
                        return strtoupper($record->title);
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->description(function ($record) {
                        return ('Em:' . $record->updated_at);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('Adicionar Mão de obra')
                    ->model(ServiceLabor::class)
                    ->action(function (array $data): void {
                        // Criando um novo registro na tabela 'ServiceLabor'
                        ServiceLabor::create([
                            'user_id' => auth()->id(),
                            'order_id' => $data['order_id'],
                            'service_id' => $data['service_id'],
                            'labor_id' => $data['laborModal_id'],
                            'includedAt' => $data['includedAt'],
                            'status' => $data['status'],
                            'description' => $data['description'],
                        ]);
                    })
                    ->form([
                        Forms\Components\TextInput::make('order_id')
                            ->required()
                            ->default(function (RelationManager $livewire) {
                                return $livewire->getOwnerRecord()->order->id;
                            }),

                        Forms\Components\TextInput::make('service_id')
                            ->required()
                            ->default(function (RelationManager $livewire) {
                                return $livewire->getOwnerRecord()->id;
                            })
                            ->maxLength(255),

                        Forms\Components\Select::make('laborModal_id')
                            ->required()
                            ->relationship('labor', 'title')
                            ->options(function (Get $get) {
                                $service = Service::find($get('service_id'));  // Obtém o serviço pelo ID
                                if ($service) {
                                    return Labor::where('part_id', $service->part_id)->pluck('title', 'id');
                                }
                                return [];
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('part_id')
                                    ->readOnly()
                                    ->default(function (RelationManager $livewire) {
                                        return $livewire->getOwnerRecord()->part_id;
                                    }),
                                Forms\Components\TextInput::make('title')
                                    ->required(),
                                Forms\Components\TextInput::make('description'),
                            ]),

                        Forms\Components\DatePicker::make('includedAt')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('part_id')
                            ->required()
                            ->default(function (Get $get) {
                                $service = Service::find($get('service_id'));
                                return $service ? $service->part_id : null;
                            }),

                        Forms\Components\Radio::make('status')
                            ->options(TypeOfLaborStatus::class)
                            ->required(),

                        Forms\Components\TextInput::make('description')
                            ->required(),
                    ]),

                //Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                //Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DetachBulkAction::make(),
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
