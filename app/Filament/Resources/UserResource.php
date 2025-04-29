<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($record) => !$record)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->rule(Password::default()),
                    
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->requiredWith('password')
                    ->dehydrated(false),
                    
                Forms\Components\FileUpload::make('profileImg')
                    ->avatar()
                    ->imageEditor()
                    ->directory('user-profiles'),
                    
                Toggle::make('is_admin')
                    ->label('Administrador')
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profileImg')
                    ->label('Avatar')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('active_departments_count')
                    ->label('Departamentos Ativos')
                    ->getStateUsing(fn (User $record): int => $record->activeDepartments()->count()),
                    
                Tables\Columns\TextColumn::make('departments_count')
                    ->label('Total Departamentos')
                    ->counts('departments'),
                    
                Tables\Columns\TextColumn::make('responsible_departments')
                    ->label('Responsável por')
                    ->getStateUsing(function (User $record): string {
                        $depts = $record->responsibleDepartments()->pluck('title')->toArray();
                        return count($depts) ? implode(', ', $depts) : 'Nenhum';
                    }),
                    
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_admin')
                    ->label('Tipo de usuário')
                    ->options([
                        '1' => 'Administrador',
                        '0' => 'Usuário normal',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('makeAdmin')
                        ->label('Tornar administrador')
                        ->action(fn (array $records) => User::whereIn('id', $records)->update(['is_admin' => true]))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['departments', 'activeDepartments']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DepartmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}