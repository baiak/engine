<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('is_responsible')
                    ->label('É responsável pelo departamento')
                    ->default(false)
                    ->afterStateUpdated(function ($state, $set, $record) {
                        if ($state && $record) {
                            $this->getOwnerRecord()->users()->update(['pivot_is_responsible' => false]);
                        }
                    }),
                    
                Toggle::make('is_active')
                    ->label('Está ativo no departamento')
                    ->default(true)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('dismissal_date', Carbon::now());
                        } else {
                            $set('dismissal_date', null);
                        }
                    }),
                    
                DateTimePicker::make('admission_date')
                    ->label('Data de admissão')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
                    
                DateTimePicker::make('dismissal_date')
                    ->label('Data de demissão')
                    ->visible(fn (callable $get) => !$get('is_active'))
                        
                    ->default(null)
                    ->minDate(function (callable $get) {
                        $admissionDate = $get('admission_date');
                        return $admissionDate ? Carbon::parse($admissionDate) : null;
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profileImg')
                    ->label('Avatar')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->description(fn ($record): string => $record->email)
                    ->searchable(),
                    
                Tables\Columns\ToggleColumn::make('is_responsible')
                    ->label('Responsável')
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            // Remover a responsabilidade de outros usuários
                            $this->getOwnerRecord()->users()->update(['pivot_is_responsible' => false]);
                        }
                        
                        // Atualizar o estado deste usuário
                        $this->getOwnerRecord()->users()->updateExistingPivot($record->id, [
                            'is_responsible' => $state,
                        ]);
                    }),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo')
                    ->updateStateUsing(function ($record, $state) {
                        $data = ['is_active' => $state];
                        
                        if (!$state) {
                            $data['dismissal_date'] = now();
                        } else {
                            $data['dismissal_date'] = null;
                        }
                        
                        $this->getOwnerRecord()->users()->updateExistingPivot($record->id, $data);
                    }),
                    
                Tables\Columns\TextColumn::make('admission_date')
                    ->label('Admissão')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('dismissal_date')
                    ->label('Demissão')
                    ->date('d/m/Y')
                    ->placeholder('--')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativos',
                        '0' => 'Inativos',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Toggle::make('is_responsible')
                            ->label('É responsável pelo departamento')
                            ->default(false),
                        DateTimePicker::make('admission_date')
                            ->label('Data de admissão')
                            ->default(now())
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make()
                    ->before(function ($record) {
                        if ($record->pivot->is_responsible) {
                            $this->getOwnerRecord()->users()->update(['pivot_is_responsible' => false]);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsActive')
                        ->label('Marcar como ativos')
                        ->action(function (array $recordsIds) {
                            foreach ($recordsIds as $recordId) {
                                $this->getOwnerRecord()->users()->updateExistingPivot($recordId, [
                                    'is_active' => true,
                                    'dismissal_date' => null,
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markAsInactive')
                        ->label('Marcar como inativos')
                        ->action(function (array $recordsIds) {
                            foreach ($recordsIds as $recordId) {
                                $this->getOwnerRecord()->users()->updateExistingPivot($recordId, [
                                    'is_active' => false,
                                    'dismissal_date' => now(),
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}