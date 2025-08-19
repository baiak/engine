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
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'Ordem de Serviço';
    protected static ?string $pluralModelLabel = 'Ordens de Serviço';
    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Informações da ordem')
                    ->columnSpan(['sm' => 1])
                    ->hidden(fn(string $operation): bool => $operation === 'create')
                    ->schema([
                        Forms\Components\Placeholder::make('order_number')
                            ->label('Número:')
                            ->content(function ($record) {
                                return $record->order_number;
                            }),

                        Forms\Components\Placeholder::make('Cliente:')
                            ->content(function ($record) {
                                return $record->client->name;
                            }),

                        Forms\Components\Placeholder::make('Veículo:')
                            ->content(function ($record) {
                                return $record->vehicle->factory . '/' .
                                    $record->vehicle->model . '/' .
                                    $record->vehicle->motor;
                            }),

                        Forms\Components\Placeholder::make('Status atual:')
                            ->content(function ($record) {
                                return $record->status;
                            }),

                        Forms\Components\Placeholder::make('Cadastrado por:')
                            ->content(function ($record) {
                                return $record->user->name . ' em: ' . $record->created_at->format('d/m/Y - H:i');
                            }),

                        Forms\Components\Placeholder::make('deadline')
                            ->content(function ($record) {
                                if (! $record->deadline) {
                                    return '-';
                                }
                                return $record->deadline->format('d/m/Y')
                                    . ' (' . $record->deadline->diffForHumans() . ')';
                            }),
                        //->hidden(fn(string $operation): bool => $operation === 'create'),
                    ]),

                Forms\Components\Fieldset::make('Dados')
                    ->columnSpan(['sm' => 1])
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->required()
                            ->default(Auth::user()?->id),
                        // ->hidden(fn(string $operation): bool => $operation === 'edit'),


                        Select::make('client_id')
                            ->columnSpan(['sm' => 2])
                            ->label('Cliente')
                            ->options(Client::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Selecione um cliente')
                            ->relationship('client', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome')
                                    ->required(),
                                // Repeater para Endereços
                                Forms\Components\Repeater::make('addresses') // 'addresses' será o nome da relação no modelo Client
                                    ->label('Endereços')
                                    ->relationship('addresses') // Relacionamento com o modelo Address
                                    ->columns(1) // Dois campos por linha no repeater
                                    ->collapsed() // Começa recolhido
                                    ->cloneable(false) // Nao Permite duplicar entradas
                                    ->schema([
                                        Forms\Components\TextInput::make('cep')
                                            ->label('CEP')
                                            ->mask('99999-999')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('estado')
                                            ->label('Estado')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('cidade')
                                            ->label('Cidade')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('bairro')
                                            ->label('Bairro')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('rua')
                                            ->label('Rua')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('numero')
                                            ->label('Número')
                                            ->numeric()
                                            ->maxLength(10),
                                        Forms\Components\TextInput::make('complemento')
                                            ->label('Complemento')
                                            ->maxLength(255)
                                            ->nullable(),
                                    ]),

                                Forms\Components\Repeater::make('phone')
                                    ->label('Telefones')
                                    ->relationship('phones') // Relacionamento com o modelo Phone
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título (Ex: Residencial, Celular)')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('number')
                                            ->label('Número de Telefone')
                                            ->tel()
                                            ->mask('(99)99999-9999')
                                            ->required()
                                            ->maxLength(20),
                                    ])

                            ])
                            ->required()
                            ->hidden(fn(string $operation): bool => $operation === 'edit')
                            ->createOptionAction(function ($action) {
                                return $action->modalWidth('md'); // aqui você define a largura
                            }),

                        Forms\Components\Select::make('vehicle_id')
                            ->columnSpan(['sm' => 2])
                            ->native(false)

                            ->label('Veículo')
                            ->live()
                            ->options([
                                array_unique(
                                    Vehicle::query()
                                        ->select([DB::raw("CONCAT(factory, '/', model, '/', motor) as vehicle"), 'id',])
                                        ->pluck('vehicle', 'id')
                                        ->toArray()
                                )
                            ])
                            //->relationship('vehicle', 'factory')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('factory'),
                                Forms\Components\TextInput::make('model'),
                                Forms\Components\TextInput::make('motor'),
                            ])
                            ->createOptionUsing(function ($data): void {
                                Vehicle::create($data);
                            })
                            ->hidden(fn(string $operation): bool => $operation === 'edit'),

                        Forms\Components\TextInput::make('order_number')
                            ->extraInputAttributes(['style' => 'color: #778899'])
                            ->columnSpan(['sm' => 1])
                            ->label('Número da ordem')
                            ->required()
                            ->maxLength(255),
                        //->hidden(fn(string $operation): bool => $operation === 'edit'),


                        Forms\Components\DatePicker::make('deadline')
                            ->seconds(false)
                            ->columnSpan(['sm' => 1])
                            ->required(),

                        Radio::make('status')
                            ->columnSpan(['sm' => 2])
                            ->options(TypeOforderStatus::class),
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

                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('client.name')
                    ->label("Cliente:")
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vehicle.factory')
                    ->label('Fabricante')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vehicle.motor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vehicle.model')
                    ->label('Veículo')
                    ->getStateUsing(fn($record) => $record->vehicle->factory . ' / ' .
                        $record->vehicle->model . ' / ' .
                        $record->vehicle->motor)
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nº Ordem')
                    ->searchable(),

                Tables\Columns\TextColumn::make('deadline')
                    ->label('Prazo')
                    ->date('d/m/Y')
                    //  ->since()
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

    public $items;
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
