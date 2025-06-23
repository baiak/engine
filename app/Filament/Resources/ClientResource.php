<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $title = 'Cliente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Cliente') // Título da seção
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Cliente')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label('Cidade - Estado')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(1), // Define que os campos dentro da seção terão 1 coluna
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Cliente')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('sm')
                    ->modalHeading('Adicionar Cliente'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Cidade - Estado')
                    ->searchable(),
            ])
            ->filters([
                //
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


    /*  public static function getRelations(): array
    {
        return [
            RelationManagers\OrderRelationManager::class
        ];
    }*/

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
           // 'create' => Pages\CreateClient::route('/create'),
           // 'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
