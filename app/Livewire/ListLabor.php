<?php

namespace App\Livewire;

use App\Enums\TypeOfLaborStatus;
use App\Models\Labor;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceLabor;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Livewire\Component;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Forms;

/*class ListLabor extends Component implements HasForms, HasTable
{
    public $service;
    public $record;

    use InteractsWithForms;
    use InteractsWithTable;
    public function mount($record)
    {
        $this->service = $record;
    }
    public function table(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->paginated(false)
            ->query(Service::query())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('false')
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }


    public function render()
    {
        return view('livewire.list-labor');
    }
}*/

class ListLabor extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $ServiceLabor;
    public $record;
    public $service;


    public function mount($record)
    {
        $this->ServiceLabor = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            //->query(ServiceLabor::query()->where('service_id', $this->ServiceLabor->id))
            ->relationship(fn(): BelongsToMany => $this->ServiceLabor->labor())
            ->inverseRelationship('Service')
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('title'),
                    ]),
                    Stack::make([
                        //->description(function(Model $record){return($record->description);}),
                        TextColumn::make('status'),
                    ])->alignment(Alignment::End)
                ]),
                Panel::make([
                    Stack::make([
                        TextColumn::make('created_at')
                          ->formatStateUsing(function(Model $record){
                              return('Criado em: <b>'.$record->pivot->created_at.'</b><br />Descrição da mao de obra: <b>'.$record->pivot->description.'</b>');
                          })->html()
                    ])
                ])->collapsed(true)
            ])->contentGrid([
                'sm' => 1,
                'md' => 1,
                'xl' => 1,
            ])
            ->filters([
                // ...
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar mão de obra')
                    ->model(ServiceLabor::class)
                    ->form([
                        Forms\Components\TextInput::make('user_id')
                            ->default(auth()->id()),
                        Forms\Components\TextInput::make('order_id')
                            ->default($this->ServiceLabor->order->id),
                        Forms\Components\TextInput::make('service_id')
                            ->default($this->ServiceLabor->id)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('labor_id')
                            ->required()
                            ->relationship('labor', 'title')
                            ->options(function (Get $get) {
                                $service = Service::find($this->ServiceLabor->id);  // Obtém o serviço pelo ID
                                if ($service) {
                                    return Labor::where('part_id', $service->part_id)->pluck('title', 'id');
                                }
                                return [];
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('part_id')
                                    ->readOnly()
                                    ->default($this->ServiceLabor->part_id),
                                Forms\Components\TextInput::make('title')
                                    ->required(),
                                Forms\Components\TextInput::make('description'),
                            ]),

                        Forms\Components\DatePicker::make('includedAt')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('part_id')
                            ->required()
                            ->default(function () {
                                $service = Service::find($this->ServiceLabor->id);
                                return $service ? $service->part_id : null;
                            }),

                        Forms\Components\Radio::make('status')
                            ->options(TypeOfLaborStatus::class)
                            ->required(),

                        Forms\Components\TextInput::make('description')
                            ->required(),
                    ])
                    ->action(function ($data) {
                        ServiceLabor::create($data);
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Visualizar')
                    ->record($this->ServiceLabor)
                    ->form([
                        Forms\Components\TextInput::make('title')
                    ]),
                Tables\Actions\DetachAction::make()
                    ->label('Remover')
                    ->model(ServiceLabor::class)
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.list-labor');
    }
}
