<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaborResource\Pages;
use App\Filament\Resources\LaborResource\RelationManagers;
use App\Models\Labor;
use App\Models\Part;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class LaborResource extends Resource
{
    protected static ?string $model = Labor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //section com placeholders exibidos apenas no modal de edição
                section::make('Dados da peça e do veículo')
                    ->visible(fn($record) => $record !== null)
                    ->schema([
                        Placeholder::make('Peça')
                            ->content(fn($record) => $record ? $record->part->title : null)
                            ->visible(fn($record) => $record !== null),

                        Placeholder::make('Veículo')
                            ->content(fn($record) => $record ? $record->vehicle->factory.'/'.
                                $record->vehicle->model.'/'.$record->vehicle->motor : null)
                            ->visible(fn($record) => $record !== null),
                    ]),

                Select::make('vehicle_id')
                    ->label('Veículo')
                    ->options(Vehicle::orderBy('model')->pluck('model', 'id'))
                        ->options([
                        array_unique(
                            Vehicle::query()
                                ->select([DB::raw("CONCAT(factory, '/', model, '/', motor) as vehicle"), 'id',])
                                ->pluck('vehicle', 'id')
                                ->toArray()
                        )
                    ])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('factory')
                         ->label('Fabricante')
                         ->required(),
                        Forms\Components\TextInput::make('model')
                        ->label('Modelo')
                        ->required(),
                        Forms\Components\TextInput::make('motor')
                        ->label('Motor')
                        ->required(),
                        Forms\Components\TextInput::make('year')
                        ->label('Ano'),
                        Forms\Components\TextInput::make('fuel')
                        ->label('Combustível'),


                    ])
                    ->createOptionUsing(function(array $data){ return(Vehicle::create($data));})
                    ->searchable()
                    ->live()
                    ->placeholder('Selecione um veículo')
                    ->hidden(fn(string $operation): bool => $operation === 'edit'),


                Select::make('part_id')
                    ->options(function (callable $get) {
                        $vehicle_id = $get('vehicle_id');
                        return Part::where('vehicle_id', $vehicle_id)->pluck('title', 'id');
                    })
                    ->hidden(fn(string $operation): bool => $operation === 'edit')
                    ->reactive(),

                Forms\Components\TextInput::make('title')
                    ->required()
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
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle.model')
                   ->formatStateUsing(fn(Model $record)=>
                   $record->vehicle->factory.'/'.$record->vehicle->model.'/'.$record->vehicle->motor)
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
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
