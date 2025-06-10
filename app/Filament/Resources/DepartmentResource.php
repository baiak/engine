<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $pluralModelLabel = 'Departamentos';
    protected static ?string $title = 'Departamento';
    
    protected static ?string $recordTitleAttribute = 'Departamento ';

    public static function getBreadcrumb(): string {
    return 'Departamentos';
    }
    


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Departamento')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Section::make('Usuários do Departamento')
                    ->schema([
                        CheckboxList::make('users')
                            ->label('Selecione os usuários')
                            ->relationship('users', 'name')
                            ->searchable()
                            ->columns(2)
                            ->bulkToggleable(),

                        Select::make('responsible_user_id')
                            ->label('Responsável')
                            ->options(function (callable $get) {
                                $selectedUsers = $get('users');
                                return User::whereIn('id', $selectedUsers ?: [])
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->reactive(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
         ->heading('Departamentos')

            ->columns([
                TextColumn::make('title')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('responsible_user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),

                Split::make([
                    ImageColumn::make('users.profileImg')
                        ->label('Avatar do Responsável')
                        ->circular(),


                    TextColumn::make('responsible_user.email')
                        ->label('Email do Responsável'),
                ]),

                TextColumn::make('users.name')
                    ->label('Usuários')
                    ->searchable()
                    ->description(fn($record) => 'Total: ' . $record->users->count() . ' usuário(s)')
                    ->wrap(),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('has_responsible')
                    ->label('Tem responsável')
                    ->options([
                        '1' => 'Sim',
                        '0' => 'Não',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === '1') {
                            return $query->whereHas('users', fn($q) => $q->where('is_responsible', true));
                        }

                        if ($data['value'] === '0') {
                            return $query->whereDoesntHave('users', fn($q) => $q->where('is_responsible', true));
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
