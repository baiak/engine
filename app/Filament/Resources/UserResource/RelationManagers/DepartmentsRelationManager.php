<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DepartmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departments';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('is_responsible')
                    ->label('É responsável pelo departamento')
                    ->default(false)
                    ->afterStateUpdated(function ($state, $set, $record) {
                        if ($state && $record) {
                            // Reset all other responsible flags for this department
                            $record->users()->updateExistingPivot(
                                $record->users()->pluck('id'),
                                ['is_responsible' => false]
                            );
                        }
                    }),
                    
                    Toggle::make('is_active')
                        ->label('Está ativo no departamento')
                        ->default(true),
                        
                        
                    DateTimePicker::make('admission_date')
                        ->label('Data de admissão')                        
                        ->required()
                        ->maxDate(now()),
                        
                    DateTimePicker::make('dismissal_date')
                        ->label('Data de demissão')
                        ->placeholder('Selecione a data de demissão')
                        ->maxDate(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                    
                Tables\Columns\ToggleColumn::make('pivot.is_responsible')
                    ->label('Responsável')
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            // Reset all other responsible flags for this department
                            $record->users()->update(['pivot.is_responsible' => false]);
                        }
                        
                        $record->users()->updateExistingPivot($this->getOwnerRecord()->id, [
                            'is_responsible' => $state,
                        ]);
                    }),
                    
                Tables\Columns\ToggleColumn::make('pivot.is_active')
                    ->label('Ativo')
                    ->updateStateUsing(function ($record, $state) {
                        $data = ['is_active' => $state];
                        
                        if (!$state) {
                            $data['dismissal_date'] = now();
                        } else {
                            $data['dismissal_date'] = null;
                        }
                        
                        $record->users()->updateExistingPivot($this->getOwnerRecord()->id, $data);
                    }),
                    
                Tables\Columns\TextColumn::make('pivot.admission_date')
                    ->label('Admissão')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pivot.dismissal_date')
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
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] !== null) {
                            $query->wherePivot('is_active', $data['value']);
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Toggle::make('is_responsible')
                            ->label('É responsável pelo departamento')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Ativo no departamento')
                            ->default(true),
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
                            // Find another user to be responsible if needed
                            $record->users()
                                ->where('id', '!=', $this->getOwnerRecord()->id)
                                ->first()
                                ?->updateExistingPivot($record->id, ['is_responsible' => true]);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsActive')
                        ->label('Marcar como ativos')
                        ->action(function (array $records) {
                            foreach ($records as $record) {
                                $this->getRelationship()->updateExistingPivot($record->id, [
                                    'is_active' => true,
                                    'dismissal_date' => null,
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markAsInactive')
                        ->label('Marcar como inativos')
                        ->action(function (array $records) {
                            foreach ($records as $record) {
                                $this->getRelationship()->updateExistingPivot($record->id, [
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