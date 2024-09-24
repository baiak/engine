<?php

namespace App\Filament\Resources;

use App\Enums\TypeOfServiceStatus;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Client;
use App\Models\Department;
use App\Models\Order;
use App\Models\Part;
use App\Models\Service;
use App\Models\Vehicle;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(components: [
                Forms\Components\Group::make()
                    ->schema([
                        Select::make('order_id')
                            ->label('Ordem de serviço')
                            ->options(Order::orderBy('order_number')->pluck('order_number', 'id'))
                            ->searchable()
                            ->reactive()
                            ->placeholder('Selecione uma ordem')
                            ->afterStateUpdated(function ($set, $state) {
                                if ($state) {
                                    // Obtém o veículo associado à ordem
                                    $vehicleIdFromOrderTable = Order::where('id', $state)->value('vehicle_id');
                                    $vehicle = Vehicle::find($vehicleIdFromOrderTable);

                                    // Obtém o cliente associado à ordem
                                    $clientIdFromOrderTable = Order::where('id', $state)->value('client_id');
                                    $client = Client::find($clientIdFromOrderTable);

                                    if ($vehicle) {
                                        // Define o campo veículo com os dados do veículo
                                        $set('vehicle', $vehicle->factory . '/' . $vehicle->model . '/' . $vehicle->motor);
                                    }

                                    if ($client) {
                                        // Define o campo cliente com o nome do cliente
                                        $set('client', $client->name);
                                    }
                                } else {
                                    // Caso o estado seja resetado ou inválido
                                    $set('vehicle', null);
                                    $set('client', null);
                                }
                            })
                            ->hidden(fn(string $operation): bool => $operation === 'edit')
                            ->required(),

                        Select::make('part_id')
                            ->label('Peça')
                            //->options(Part::orderBy('title')->pluck('title', 'id'))
                            ->options(function (callable $get, $state) {
                                // Obtém o ID do pedido selecionado
                                $orderId = $get('order_id');

                                // Busca o veículo associado ao pedido
                                $vehicleIdFromOrder = Order::where('id', $orderId)->value('vehicle_id');

                                // Se o pedido e o veículo relacionados forem válidos, retorna as peças associadas ao veículo
                                if ($orderId && $vehicleIdFromOrder) {
                                    return Part::where('vehicle_id', $vehicleIdFromOrder)->pluck('title', 'id');
                                }

                                // Se houver um estado (part_id) e a peça for encontrada, retorna a peça
                                if ($state) {
                                    $part = Part::find($state);
                                    if ($part) {
                                        return Part::where('id', $state)->pluck('title', 'id');
                                    }
                                }

                                // Caso contrário, retorna todas as peças ordenadas por título
                                return Part::orderBy('title')->pluck('title', 'id');
                            })
                            ->searchable()
                            ->placeholder('Selecione uma peça')
                            ->reactive()
                            ->required(),
                        /* Forms\Components\TextInput::make('department_id')
                             ->required()
                             ->numeric(),*/
                        Select::make('department_id')
                            ->label('Departamento')
                            ->options(function (callable $get) {
                                $partId = $get('part_id');
                                $part = Part::select('department_id')->find($partId);
                                if ($part && $part->department_id) {
                                    return Department::where('id', $part->department_id)->pluck('title', 'id');
                                }
                                return Department::orderBy('title')->pluck('title', 'id');
                            })
                            ->searchable()
                            ->reactive()
                            ->placeholder('Selecione o departamento')
                            ->required(),

                        Forms\Components\DatePicker::make('deadline')
                            ->required(),
                        Radio::make('status')
                            ->options(TypeOfServiceStatus::class),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Placeholder::make('vehicle')
                            ->label('Veículo')
                            ->content(function (callable $get) {
                                $orderId = $get('order_id');
                                if ($orderId) {
                                    // Obtém o veículo da ordem se a ordem estiver definida
                                    $vehicleIdFromOrderTable = Order::where('id', $orderId)->value('vehicle_id');
                                    $vehicle = Vehicle::find($vehicleIdFromOrderTable);
                                    if ($vehicle) {
                                        return $vehicle->factory . '/' . $vehicle->model . '/' . $vehicle->motor; // Retorna o modelo do veículo
                                    }
                                }
                                return null; // Retorna nulo se não houver ordem ou veículo
                            }),

                        Forms\Components\Placeholder::make('order.client_id')
                            ->label('Cliente')
                            ->content(function (callable $get) {
                                $orderId = $get('order_id');
                                if ($orderId) {
                                    // Obtém o cliente da ordem se a ordem estiver definida
                                    $clientIdFromOrderTable = Order::where('id', $orderId)->value('client_id');
                                    $client = Client::find($clientIdFromOrderTable);
                                    if ($client) {
                                        return $client->name; // Retorna o modelo do veículo
                                    }
                                }
                                return null; // Retorna nulo se não houver ordem ou veículo
                            }),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('order.order_number')
                    //->tooltip(fn($record)=> )
                    ->tooltip(function ($record) {
                        return ('Cliente:' . $record->order->client->name .
                            '  -  Veículo:' . $record->order->vehicle->factory . '/' .
                            $record->order->vehicle->model . '/' .
                            $record->order->vehicle->motor);
                    })
                    ->html()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part.title')
                    ->description(fn($record) => 'Responsável: ' . $record->department->user->name)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LaborRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
