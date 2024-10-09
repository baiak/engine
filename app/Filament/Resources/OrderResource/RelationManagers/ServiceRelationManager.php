<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\TypeOfLaborStatus;
use App\Enums\TypeOfServiceStatus;
use App\Livewire\ListLabor;
use App\Models\Department;
use App\Models\Labor;
use App\Models\Order;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Support\View\Components\Modal;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Filament\Pages\Actions;

class ServiceRelationManager extends RelationManager
{
    protected static string $relationship = 'service';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->default(function (RelationManager $livewire) {
                        return ($livewire->getOwnerRecord()->id);
                    })
                    //->disabled()
                    ->reactive()
                    ->required()
                    ->maxLength(255),

                Select::make('part_id')
                    ->label('Peça')
                    ->reactive()
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
                    ->placeholder('Selecione um departamento'),

                Radio::make('status')
                    ->options(TypeOfServiceStatus::class),

                Forms\Components\DatePicker::make('deadline')
                    ->required(),

                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('part_id')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('part_id')
                        ->weight('bold')
                        ->size('lg')
                        ->getStateUsing(function ($record) {
                            return Part::where('id', $record->part_id)->first()->title;
                        }),
                    Tables\Columns\TextColumn::make('status')
                        ->label('Status'),

                    Tables\Columns\TextColumn::make('Responsável: ')
                        ->formatStateUsing(fn(Column $column, $state): string => $column->getLabel() . ': ' . $state)
                        ->weight('bold')
                        ->label('Responsável')
                        ->getStateUsing(function ($record) {
                            return ($record->department->user->name);
                        }),

                    Tables\Columns\TextColumn::make('deadline')
                        ->formatStateUsing(fn(string $state) => 'Prazo: ' . Carbon::parse($state)->format('d/m/y')),

                    Tables\Columns\TextColumn::make('labor.title')
                        ->label('Listagem de mao de obra')
                        ->formatStateUsing(function ($record) {
                            $labor = $record->labor->pluck('title')->toArray();
                            $html = '<ul>';
                            foreach ($labor as $item) {
                                $html .= "<li>{$item}</li>";
                            }
                            $html .= '</ul>';

                            return $html;
                            }
                        )->html(),
                    /*Tables\Columns\ViewColumn::make('labor')
                        ->view('livewire.list-labor'),*/
                ]),
            ])
            /*->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('part_id')
                        ->weight('bold')
                        ->getStateUsing(function ($record) {
                            return Part::where('id', $record->part_id)->first()->title;
                        }),
                    Tables\Columns\TextColumn::make('status')
                        ->label('Status'),

                    Tables\Columns\TextColumn::make('Responsável: ')
                        ->formatStateUsing(fn(Column $column, $state): string => $column->getLabel() . ': ' . $state)
                        ->weight('bold')
                        ->label('Responsável')
                        ->getStateUsing(function ($record) {
                            return ($record->department->user->name);
                        }),

                    Tables\Columns\TextColumn::make('deadline')
                        ->formatStateUsing(fn (string $state) => 'Prazo: '.Carbon::parse($state)->format('d/m/y')),
                        //->formatStateUsing(fn(Column $column, $state): string => $column->getLabel() . ': ' . $state)

                    Tables\Columns\TextColumn::make('Mao de obra')

                        ->getStateUsing(
                            function ($record) {

                                return (ServiceLabor::select('labor_id')
                                    ->where('service_id', $record->service_id)
                                    ->pluck('id')

                                );
                            }),

                ]),

            ])*/
            //->contentGrid(['sm' => 1, 'md' => 2, 'lg' => 3])

            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar serviço'),
            ])
            ->actions([

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Fieldset::make('Dados da ordem')
                    ->schema([
                        TextEntry::make('order.order_number')
                            ->label('Número da ordem'),
                        TextEntry::make('order.client.name')
                            ->label('Cliente'),
                        TextEntry::make('order.vehicle')
                            ->label('Veículo')
                            ->formatStateUsing(function ($record) {
                                return ($record->order->vehicle->factory . '/' .
                                    $record->order->vehicle->model . '/' .
                                    $record->order->vehicle->motor);
                            })

                    ])->columns(3),
                Fieldset::make('Dados do serviço')
                  ->schema([
                      TextEntry::make('part.title')
                          ->label('Peça:')
                          ->columnSpan(1),
                      Livewire::make(ListLabor::class)
                          ->columnSpan(4),
                      ])->columns(5),
            ])->columns(2);
    }
}
