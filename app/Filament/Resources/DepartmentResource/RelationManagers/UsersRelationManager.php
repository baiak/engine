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
                    ->visible(fn (callable $get) => $get('is_active') ?? true)
                    ->afterStateUpdated(function ($state, $set, $record) {
                        if ($state && $record) {
                            // Get all user IDs explicitly from the relationship
                            $userIds = $this->getOwnerRecord()
                                ->users()
                                ->select('users.id')
                                ->pluck('users.id')
                                ->toArray();
                                
                            // Update all existing users to not be responsible
                            foreach ($userIds as $userId) {
                                $this->getOwnerRecord()->users()->updateExistingPivot(
                                    $userId,
                                    ['is_responsible' => false]
                                );
                            }
                            
                            // Then update the current record
                            $this->getOwnerRecord()->users()->updateExistingPivot(
                                $record->id,
                                ['is_responsible' => true]
                            );
                        }
                    }),
                    
                Toggle::make('is_active')
                    ->label('Está ativo no departamento')
                    ->default(true)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('dismissal_date', Carbon::now());
                            $set('is_responsible', false); // Automatically set is_responsible to false when inactive
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
            ->heading('Usuários do Departamento')
            ->columns([
                Tables\Columns\ImageColumn::make('profileImg')
                    ->label('Avatar')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->description(fn ($record): string => $record->email)
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('pivot.is_responsible')
                    ->label('Responsável')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                    
                Tables\Columns\IconColumn::make('pivot.is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                    
                Tables\Columns\TextColumn::make('pivot.admission_date')
                    ->label('Admissão')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pivot.dismissal_date')
                    ->label('Demissão')
                    ->date('d/m/Y')
                    ->placeholder('--')
                    ->sortable(),
                
                // Adicionar uma coluna de ações para gerenciar o status de responsável
                Tables\Columns\TextColumn::make('actions')
                    ->label('Gerenciar')
                    ->formatStateUsing(function ($record) {
                        // Não exibir se não estiver ativo
                        if (!isset($record->pivot) || !$record->pivot->is_active) {
                            return '';
                        }
                        
                        return $record->pivot->is_responsible ? 'Remover responsabilidade' : 'Tornar responsável';
                    })
                    ->action(function ($record) {
                        // Não fazer nada se não estiver ativo
                        if (!isset($record->pivot) || !$record->pivot->is_active) {
                            return;
                        }
                        
                        // Se for responsável, remove; se não for, torna responsável
                        $newState = !$record->pivot->is_responsible;
                        
                        if ($newState) {
                            // Get all user IDs explicitly from the relationship
                            $userIds = $this->getOwnerRecord()
                                ->users()
                                ->select('users.id')
                                ->pluck('users.id')
                                ->toArray();
                                
                            // Update all existing users to not be responsible
                            foreach ($userIds as $userId) {
                                $this->getOwnerRecord()->users()->updateExistingPivot(
                                    $userId,
                                    ['is_responsible' => false]
                                );
                            }
                        }
                        
                        // Update the current record
                        $this->getOwnerRecord()->users()->updateExistingPivot(
                            $record->id,
                            ['is_responsible' => $newState]
                        );
                    })
                    ->hidden(fn ($record) => !isset($record->pivot) || !$record->pivot->is_active),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativos',
                        '0' => 'Inativos',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->wherePivot('is_active', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Toggle::make('is_responsible')
                            ->label('É responsável pelo departamento')
                            ->default(false)
                            ->visible(fn (callable $get) => $get('is_active') ?? true),
                        Toggle::make('is_active')
                            ->label('Ativo no departamento')
                            ->default(true)
                            ->reactive(),
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
                        if ($record && isset($record->pivot) && $record->pivot->is_responsible) {
                            // Get user IDs explicitly to avoid column ambiguity
                            $userIds = $this->getOwnerRecord()
                                ->users()
                                ->select('users.id')
                                ->pluck('users.id')
                                ->toArray();
                                
                            // Update each user individually
                            foreach ($userIds as $userId) {
                                if ($userId != $record->id) { // Skip the current record as it will be detached
                                    $this->getOwnerRecord()->users()->updateExistingPivot(
                                        $userId,
                                        ['is_responsible' => false]
                                    );
                                }
                            }
                        }
                    }),
                // Nova ação para alternar o status de responsável
                Tables\Actions\Action::make('toggleResponsible')
                    ->label(fn ($record) => $record && isset($record->pivot) && $record->pivot->is_responsible ? 
                        'Remover responsabilidade' : 'Tornar responsável')
                    ->icon(fn ($record) => $record && isset($record->pivot) && $record->pivot->is_responsible ? 
                        'heroicon-o-x-mark' : 'heroicon-o-check')
                    ->color(fn ($record) => $record && isset($record->pivot) && $record->pivot->is_responsible ? 
                        'danger' : 'success')
                    ->hidden(fn ($record) => !($record && isset($record->pivot) && $record->pivot->is_active))
                    ->action(function ($record) {
                        $newState = !$record->pivot->is_responsible;
                        
                        if ($newState) {
                            // Get all user IDs explicitly from the relationship
                            $userIds = $this->getOwnerRecord()
                                ->users()
                                ->select('users.id')
                                ->pluck('users.id')
                                ->toArray();
                                
                            // Update all existing users to not be responsible
                            foreach ($userIds as $userId) {
                                $this->getOwnerRecord()->users()->updateExistingPivot(
                                    $userId,
                                    ['is_responsible' => false]
                                );
                            }
                        }
                        
                        // Update the current record
                        $this->getOwnerRecord()->users()->updateExistingPivot(
                            $record->id,
                            ['is_responsible' => $newState]
                        );
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
                                    'is_responsible' => false, // Automatically set is_responsible to false when inactive
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}