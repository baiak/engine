<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaborResource\Pages;
use App\Filament\Resources\LaborResource\RelationManagers;
use App\Models\Labor;
use App\Models\Part;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaborResource extends Resource
{
    protected static ?string $model = Labor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('vehicle_id')
                    ->label('Veículo')
                    ->options(Vehicle::orderBy('model')->pluck('model', 'id'))
                    ->searchable()
                    ->reactive()
                    ->placeholder('Selecione um veículo'),

                /*Forms\Components\TextInput::make('part_id')
                    ->required()
                    ->numeric(),*/
                Select::make('part_id')
                    ->options(function (callable $get) {
                        $vehicle_id = $get('vehicle_id');
                        return Part::where('vehicle_id', $vehicle_id)->pluck('title', 'id');
                    })
                   ->reactive(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('part.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.model'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabors::route('/'),
            'create' => Pages\CreateLabor::route('/create'),
            'edit' => Pages\EditLabor::route('/{record}/edit'),
        ];
    }
}
