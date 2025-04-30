<?php

namespace App\Filament\Resources;

use App\Enums\TypeOfServiceStatus;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Livewire\ListLabor;
use App\Models\Client;
use App\Models\Department;
use App\Models\Order;
use App\Models\Part;
use App\Models\Service;
use App\Models\Vehicle;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use League\CommonMark\Util\HtmlElement;
use Nette\Utils\Html;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 1,
                        'xl' => 4,
                    ])
                    ->schema([
                        Forms\Components\Section::make([
                            Select::make('order_id')
                                ->label('Ordem de serviço:')
                                ->options(Order::orderBy('order_number')->pluck('order_number', 'id'))
                                ->searchable()
                                ->reactive()
                                ->live()
                                ->placeholder('Selecione uma ordem')
                                ->afterStateUpdated(function ($set, $state) {
                                    if ($state) {
                                        $vehicleIdFromOrderTable = Order::where('id', $state)->value('vehicle_id');
                                        $vehicle = Vehicle::find($vehicleIdFromOrderTable);

                                        $clientIdFromOrderTable = Order::where('id', $state)->value('client_id');
                                        $client = Client::find($clientIdFromOrderTable);

                                        if ($vehicle) {
                                            $set('vehicle', $vehicle->factory . '/' . $vehicle->model . '/' . $vehicle->motor);
                                        }

                                        if ($client) {
                                            $set('client', $client->name);
                                        }
                                    } else {
                                        $set('vehicle', null);
                                        $set('client', null);
                                    }
                                })
                                ->hidden(fn(string $operation): bool => $operation === 'edit')
                                ->required(),

                            Select::make('part_id')
                                ->label('Peça')
                                ->options(function (callable $get, $state) {
                                    $orderId = $get('order_id');
                                    $vehicleIdFromOrder = Order::where('id', $orderId)->value('vehicle_id');

                                    if ($orderId && $vehicleIdFromOrder) {
                                        return Part::where('vehicle_id', $vehicleIdFromOrder)->pluck('title', 'id');
                                    }

                                    if ($state) {
                                        $part = Part::find($state);
                                        if ($part) {
                                            return Part::where('id', $state)->pluck('title', 'id');
                                        }
                                    }

                                    return Part::orderBy('title')->pluck('title', 'id');
                                })
                                ->searchable()
                                ->placeholder('Selecione uma peça')
                                ->reactive()
                                ->hidden(fn(string $operation): bool => $operation === 'edit')
                                ->required(),

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
                                ->hidden(fn(string $operation): bool => $operation === 'edit')
                                ->required()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $set('user_id', null); // Limpa o usuário selecionado quando o departamento muda
                                }),
                                Select::make('user_id')
                                ->label('Responsável')
                                ->options(function (callable $get) {
                                    try {
                                        $departmentId = $get('department_id');
                                        
                                        if (!$departmentId) {
                                            return [];
                                        }
                            
                                        $users = \App\Models\Department::find($departmentId)
                                            ->users()
                                            ->wherePivot('is_active', true)
                                            ->pluck('name', 'id');
                                        
                                        return $users;
                                    } catch (\Exception $e) {
                                        return [];
                                    }
                                })
                                ->searchable()
                                ->reactive()
                                ->live()
                                ->placeholder('Selecione o responsável')
                                ->hidden(fn(string $operation): bool => $operation === 'edit')
                                ->required()
                                ->disabled(fn(callable $get): bool => empty($get('department_id'))),

                            Forms\Components\DatePicker::make('deadline')
                                ->required(),
                            Radio::make('status')
                                ->options(TypeOfServiceStatus::class),
                            Forms\Components\TextInput::make('description')
                                ->maxLength(255),
                        ])->grow(false)
                            ->columnSpan([
                                'sm' => 2,
                            ]),

                        Forms\Components\Section::make([
                            Grid::make([
                                'default' => 1,
                            ])
                                ->schema([
                                    Forms\Components\Placeholder::make('order_id')
                                        ->label('Número da ordem')
                                        ->live()
                                        ->content(function ($record) {
                                            if ($record) {
                                                return $record->order->order_number;
                                            }
                                            return null;
                                        }),

                                    Forms\Components\Placeholder::make('vehicle')
                                        ->label('Veículo:')
                                        ->live()
                                        ->content(function ($record) {
                                            if ($record) {
                                                return $record->order->vehicle->factory . '/'
                                                    . $record->order->vehicle->model . '/'
                                                    . $record->order->vehicle->motor;
                                            }
                                            return null;
                                        }),

                                    Forms\Components\Placeholder::make('order.client_id')
                                        ->label('Cliente:')
                                        ->live()
                                        ->content(function ($record) {
                                            if ($record) {
                                                return $record->order->client->name;
                                            }
                                            return null;
                                        }),

                                    Forms\Components\Placeholder::make('part_id')
                                        ->label('Peça:')
                                        ->live()
                                        ->content(function ($record) {
                                            if ($record) {
                                                return ($record->part->title);
                                            }
                                            return null;
                                        }),

                                    Forms\Components\Placeholder::make('department_id')
                                        ->label('Departamento:')
                                        ->live()
                                        ->content(function ($record) {
                                            if ($record) {
                                                return ($record->department->title);
                                            }
                                            return null;
                                        }),

                                    Forms\Components\Placeholder::make('user_id')
                                        ->label('Responsável:')
                                        ->live()
                                        ->content(function ($record) {
                                            if ($record && $record->user) {
                                                return $record->user->name;
                                            }
                                            return null;
                                        }),
                                ]),
                        ])->grow(false)
                            ->columnSpan([
                                'sm' => 2,
                            ]),
                    ])
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
                    ->searchable()
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->sortable(),
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