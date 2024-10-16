<?php

namespace App\Livewire;

use App\Enums\TypeOfLaborStatus;
use App\Enums\TypeOfServiceStatus;
use App\Models\Labor;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceLabor;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
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
            //->query(ServiceLabor::query()->where('service_id', $this->ServiceLabor->id)->orderByDesc( 'created_at' ))
            ->relationship(fn(): BelongsToMany => $this->ServiceLabor->labor())
            ->inverseRelationship('Service')
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('title')

                    ]),
                    Stack::make([
                        //->description(function(Model $record){return($record->description);}),
                        TextColumn::make('status'),
                    ])->alignment(Alignment::End)
                ]),
                Panel::make([
                        Stack::make([
                            TextColumn::make('created_at')
                                ->formatStateUsing(function($record) {
                                    return '<small>Adicionado em: ' . $record->pivot->created_at->format('d/m/Y - H:i') . '</small>';
                                })->html(),
                            TextColumn::make('description')
                                ->default(
                                    //fn ($record) => $record->pivot->description
                                    function($record){
                                        return($record->pivot->description);
                                    }
                                )->html(),
                        ]),
                ])->collapsed(true)
            ])->contentGrid([
                'sm' => 1,
                'md' => 1,
                'xl' => 1,
            ])
            ->filters([
                // ...
            ])->paginated(false)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar mão de obra')
                    ->model(ServiceLabor::class)
                    ->form([
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                        Forms\Components\Hidden::make('order_id')
                            ->default($this->ServiceLabor->order->id),
                        Forms\Components\Hidden::make('service_id')
                            ->default($this->ServiceLabor->id),
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
                                Forms\Components\Hidden::make('part_id')
                                    ->default($this->ServiceLabor->part_id),
                                Forms\Components\TextInput::make('title')
                                    ->label('Titulo da mão de obra')
                                    ->required(),
                                /*Forms\Components\RichEditor::make('description')
                                    ->label('Descrições/Parametros e/ou observaçoes diversas')
                                    ->required(),*/
                            ]),
                        Forms\Components\DatePicker::make('includedAt')
                            ->default(now())
                            ->required(),
                        Forms\Components\Hidden::make('part_id')
                            ->required()
                            ->default(function () {
                                $service = Service::find($this->ServiceLabor->id);
                                return $service ? $service->part_id : null;
                            }),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->label('Descrição/observaçoes diversas sobre o serviço'),
                        Forms\Components\Radio::make('status')
                            ->options(TypeOfLaborStatus::class)
                            ->required(),
                    ])
                    ->action(function ($data) {
                        ServiceLabor::create($data);
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modificar')
                    ->record($this->ServiceLabor)
                    ->model(ServiceLabor::class)
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->readOnly(),
                        Forms\Components\RichEditor::make('description')
                             ->formatStateUsing(fn(Model $record)=>$record->pivot->description)
                             ->required(),
                        Radio::make('status')
                            ->options(TypeOfLaborStatus::class),
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
