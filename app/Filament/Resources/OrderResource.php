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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

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
                    ->required(),

                Select::make('vehicle_id')
                    ->label('Veículo')
                    ->options(Vehicle::orderBy('model')->pluck('model', 'id'))
                    ->searchable()
                    ->placeholder('Selecione um veículo')
                    ->required(),

                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('deadline')
                    ->required(),

                /*Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),*/

                Radio::make('status')
                    ->options(TypeOforderStatus::class),
                   /* ->options([
                        'aguardando_orcamento_servicos' => 'Aguardando orçamento de serviços',
                        'aguardando_aprovacao_cliente' => 'Aguardando aprovacao do cliente',
                        'aprovado' => 'Aprovado',
                        'em_andamento' => 'Em Andamento',
                        'finalizado' => 'Finalizado',
                    ])*/

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
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.model')
                    ->numeric()
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
            //
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
