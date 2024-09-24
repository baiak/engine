<?php

namespace App\Filament\Resources;

use App\Enums\TypeOforderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Client;
use App\Models\Order;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->default(auth()->id()),

                Select::make('client_id')
                    ->label('Cliente')
                    ->options(Client::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Selecione um cliente')
                    ->relationship('client', 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                        Forms\Components\TextInput::make('city')
                            ->label('Cidade')
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\Group::make()
                    /*->schema([
                        Select::make('vehicle_id')
                            ->label('Veículo')
                            ->options(
                                Vehicle::orderBy('model')->pluck('model', 'id')->toArray()
                            )
                            ->relationship('vehicle', 'model')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('factory')
                                    ->label('Fabricante')
                                    ->required(),
                                Forms\Components\TextInput::make('model')
                                    ->label('Modelo')
                                    ->required(),
                                Forms\Components\TextInput::make('motor')
                                    ->label('Motor')
                                    ->required(),
                                Forms\Components\TextInput::make('year')
                                    ->label('Ano'),
                                Forms\Components\Select::make('fuel')
                                    ->options(['Gasolina' => 'Gasolina', 'Diesel' => 'Diesel', 'Alcool' => 'Alcool', 'Flex' => 'Flex'])
                                    ->label('Combustivel'),
                            ])
                            //->searchable()
                            ->placeholder('Selecione um veículo')
                            ->live()
                            ->required(),

                        Forms\Components\Placeholder::make('vehicle_details')
                            ->label(false)
                            ->live()
                            ->content(function (callable $get) {
                                $vehicle = Vehicle::find($get('vehicle_id'));
                                return $vehicle
                                    ? "{$vehicle->factory} / {$vehicle->model} / {$vehicle->motor}"
                                    : 'Nenhum veículo selecionado';
                            }),
                    ]),*/
                    ->schema([
                        Forms\Components\Select::make('fabricante')
                         ->label('Fabricante')
                            ->live()
                            ->options([
                                    array_unique(
                                        Vehicle::query()
                                            ->select([DB::raw("CONCAT(factory, '/', model, '/', motor) as vehicle"), 'id',])
                                            ->pluck('vehicle', 'id')
                                            ->toArray()
                                    )
                                ])
                            ->relationship('vehicle', 'factory')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('factory'),
                                Forms\Components\TextInput::make('model'),
                                Forms\Components\TextInput::make('motor'),
                            ])
                        ]),

                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('deadline')
                    ->required(),

                Radio::make('status')
                    ->options(TypeOforderStatus::class),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('client.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.factory')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vehicle.motor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),


                Tables\Columns\TextColumn::make('vehicle.model')
                    ->getStateUsing(fn($record) => $record->vehicle->factory . ' / ' .
                        $record->vehicle->model . ' / ' .
                        $record->vehicle->motor)
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->since()
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\ServiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
