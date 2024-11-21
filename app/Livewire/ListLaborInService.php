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
use Livewire\Livewire;


class ListLaborInService extends Component implements HasForms, HasTable
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
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->record($this->ServiceLabor)
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->readOnly(),
                        Forms\Components\RichEditor::make('description')
                            ->formatStateUsing(fn(Model $record)=>$record->pivot->description)
                            ->required(),
                        Radio::make('status')
                            ->options(TypeOfLaborStatus::class),

                    ]),
                /*Tables\Actions\Action::make('editStatus')
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
                    ])
                    ->action(function ($data):void {ServiceLabor::update($data);}),*/

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

                Tables\Actions\DeleteAction::make()
                    ->label('Remover')
                    ->model(ServiceLabor::class)
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.list-labor-in-service');
    }
    /*public function callAction($action, $id)
    {
        $record = ServiceLabor::findOrFail($id);
        $this->emit('callFilamentAction', $action, $record);
    }*/

}
