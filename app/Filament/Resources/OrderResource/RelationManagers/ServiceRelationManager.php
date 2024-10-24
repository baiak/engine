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
use App\Tables\Columns\listLaborWithStatus;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
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
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Filament\Pages\Actions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Database\Eloquent\Collection;


class ServiceRelationManager extends RelationManager
{


    protected static string $relationship = 'service';

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID'),
            TextColumn::make('title')->label('Título'),
            TextColumn::make('description')->label('Descrição'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('edit')
                ->label('Editar Status')
                ->action(function ($record) {
                    $this->notify(
                        'success',
                        "Abrindo modal para o registro: {$record->id}"
                    );
                    // Aqui você pode abrir o modal via JS ou outro método
                })
        ];
    }


    public static function getCleanOptionString(Model $model): string
    {
        return (
        view('Components.select-user-result')
            ->with('name', $model?->name)
            ->with('email', $model?->email)
            ->with('image', $model?->profileImg)
            ->render()
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('order_id')
                    ->default(function (RelationManager $livewire) {
                        return ($livewire->getOwnerRecord()->id);
                    })
                    //->disabled()
                    ->reactive()
                    ->required(),

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
                    ->createOptionForm([
                        Forms\Components\TextInput::make('vehicle.model')
                            ->label('Veículo')
                            ->readOnly()
                            ->default(function (RelationManager $livewire) {
                                return (
                                    $livewire->getOwnerRecord()->vehicle->factory . '/' .
                                    $livewire->getOwnerRecord()->vehicle->model . '/' .
                                    $livewire->getOwnerRecord()->vehicle->motor . '/'
                                );
                            }),

                        Select::make('department_id')
                            ->label('Departamento responsável')
                            ->options(Department::orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->placeholder('Selecione um departamento'),

                        Forms\Components\Hidden::make('vehicle_id')
                            ->label('Veículo')
                            ->default(function (RelationManager $livewire) {
                                return ($livewire->getOwnerRecord()->vehicle->id);
                            }),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('parameters')

                    ])
                    ->createOptionUsing(function ($data): void {
                        Part::create($data);
                    })
                    ->searchable()
                    ->hidden(fn(string $operation): bool => $operation === 'edit')
                    ->required(),

                //placeholders exibidos apenas no modal de edição
                Section::make('Dados do serviço')
                    ->visible(fn($record) => $record !== null)
                    ->schema([
                        Placeholder::make('Peça')
                            ->content(fn($record) => $record ? $record->part->title : null)
                            ->visible(fn($record) => $record !== null),

                        Placeholder::make('Responsável  / Departamento')
                            ->content(fn($record) => $record ? $record->department->user->name . ' / ' . $record->department->title : null)
                            ->visible(fn($record) => $record !== null),
                    ]),

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
                    ->required()
                    ->placeholder('Selecione um departamento')
                    ->hidden(fn(string $operation): bool => $operation === 'edit'),

                Radio::make('status')
                    ->required()
                    ->options(TypeOfServiceStatus::class),

                Forms\Components\DatePicker::make('deadline')
                    ->required(),

                Forms\Components\RichEditor::make('description')
                    ->required()
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

                ]),//stack1
                /*View::make('service.labor.list-labor-in-service')
                    ->components([
                        Tables\Columns\TextColumn::make('labor.title')
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ])
                    ->collapsible(),*/


                /*Tables\Columns\Layout\Stack::make([
                      listLaborWithStatus::make('labor')
                     /*Tables\Columns\TextColumn::make('labor.title')
                         ->listWithLineBreaks()
                         ->bulleted()
                         /*->description(function($record){
                             return($record->status);
                         })
                         /*->formatStateUsing(function ($record) {
                             $labor = $record->labor;
                            // dump($labor);
                             $html = '<ul>';
                             foreach ($labor as $item) {
                                 $html .= "<li>- {$item->title} - {$item->pivot->status}</li>";
                             }
                             $html .= '</ul>';

                             return $html;
                         }
                         )->html(),
                 ])->collapsible()*/
                Tables\Columns\Layout\Stack::make([
                        Tables\Columns\ViewColumn::make('labor')
                            ->view('livewire.labor-list-on-service-relation-manager')
                ])->collapsible()
            ])
            ->contentGrid(['sm' => 2])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar peça/serviço'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Mão de obras'),
                Tables\Actions\EditAction::make()
                    ->label('Status do serviço'),
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
                Fieldset::make('Dados do serviço  /  Mão de obra')
                    ->schema([
                        TextEntry::make('part.title')
                            ->label('Peça:'),
                        Livewire::make(ListLabor::class)
                            ->columnSpan(3),
                    ])->columns(4)
            ]);
    }


}
