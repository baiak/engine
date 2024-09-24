<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

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
                        return Labor::all()->where('part_id', $partIdFromService->part_id)->pluck('title', 'id');
                    }),
                Forms\Components\TextInput::make('part_id')
                    ->required()
                    ->default(function (Get $get) {
                        $partIdFromService = Service::find($get('service_id'));
                        return ($partIdFromService->part_id);
                    })

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
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('Adicionar Mão de obra')
                    ->model(ServiceLabor::class)
                    ->action(function (ServiceLabor $records, array $data): void {
                        ServiceLabor::create([
                            'user_id' => auth()->id(),
                            'order_id' => $data['order_id'],
                            'service_id' => $data['service_id'],
                            'labor_id' => $data['labor_id'],
                            'includedAt' => $data['includedAt'],
                            /*'approvedAt' => $data['approvedAt'],
                            'startedAt' => $data['startedAt'],
                            'finishedAt' => $data['finishedAt'],*/
                            'status' => $data['status'],
                            'description' => $data['description'],
                        ]);
                    })
                    ->form([

                        Forms\Components\TextInput::make('order_id')
                            ->required()
                            ->default(function (RelationManager $livewire) {
                                return ($livewire->getOwnerRecord()->order->id);
                            }),

                        Forms\Components\TextInput::make('service_id')
                            ->required()
                            ->default(function (RelationManager $livewire) {
                                return ($livewire->getOwnerRecord()->id);
                            })
                            ->maxLength(255),

                        Forms\Components\Select::make('labor_id')
                            ->required()
                            ->relationship('labor', 'title')
                            ->options(function (Get $get) {
                                $partIdFromService = Service::find($get('service_id'));
                                return Labor::all()->where('part_id', $partIdFromService->part_id)->pluck('title', 'id');
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('part_id')
                                    ->readOnly()
                                    ->default(function (RelationManager $livewire) {
                                        $partIdFromService = Service::find($livewire->getOwnerRecord()->id);
                                        return ($partIdFromService->part_id);
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
                                $partIdFromService = Service::find($get('service_id'));
                                return ($partIdFromService->part_id);
                            }),

                        Forms\Components\TextInput::make('status')
                            ->required(),

                        Forms\Components\TextInput::make('description')
                            ->required()
                    ]),

                //Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
