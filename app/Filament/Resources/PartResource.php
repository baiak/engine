<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartResource\Pages;
use App\Filament\Resources\PartResource\RelationManagers;
use App\Models\Department;
use App\Models\Part;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mews\Purifier\Facades\Purify;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static ?string $modelLabel = 'Peça';
    protected static ?string $pluralModelLabel = 'Peças';
    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    public static function getCleanOptionString(Model $model): string
    {
        return (
            view('Components.select-user-result')
            ->with('name', $model?->name)
            ->with('email', $model?->email)
            ->with('image', $model?->profileImg)
            ->render()
        );
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /* Select::make('user')
                    ->label('Selecione um corno')
                    ->searchable()
                    ->allowHtml()
                    ->getSearchResultsUsing(function (string $search) {
                        $users = User::where('name', 'like', "%{$search}%")->limit(50)->get();

                        return $users->mapWithKeys(function ($user) {
                            return [$user->getKey() => static::getCleanOptionString($user)];
                        })->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): string {
                        $user = User::find($value);

                        return static::getCleanOptionString($user);
                    }),*/
                Select::make('department_id')
                    ->label('Departamento responsável')
                    ->options(Department::orderBy('title')->pluck('title', 'id'))
                    ->searchable()
                    ->placeholder('Selecione um departamento'),

                Select::make('vehicle_id')
                    ->label('Veículo')
                    ->searchable()
                    ->preload()
                    ->placeholder('Selecione um veículo')
                    ->options(Vehicle::all()->pluck('title', 'id'))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('factory')
                            ->label('Fabricante')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('year')
                            ->label('Ano')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('motor')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('fuel')
                            ->label('Combustível')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('infos')
                            ->label('Informações adicionais (opcional)')
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(function ($data): void {
                        Vehicle::create($data);
                    }),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('parameters')
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Peça')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.model')
                    ->label('Veículo')
                    ->getStateUsing(fn($record) => $record->vehicle->factory . ' / ' .
                        $record->vehicle->model . ' / ' .
                        $record->vehicle->motor)
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.motor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            'index' => Pages\ListParts::route('/'),
            'create' => Pages\CreatePart::route('/create'),
            'edit' => Pages\EditPart::route('/{record}/edit'),
        ];
    }
}
