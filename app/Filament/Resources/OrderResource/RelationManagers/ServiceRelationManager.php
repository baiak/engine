<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\TypeOfServiceStatus;
use App\Models\Department;
use App\Models\Order;
use App\Models\Part;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceRelationManager extends RelationManager
{
    protected static string $relationship = 'service';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->default(function (RelationManager $livewire) {
                        return ($livewire->getOwnerRecord()->id);
                    })
                    ->disabled()
                    ->reactive()
                    ->required()
                    ->maxLength(255),

                Select::make('part_id')
                    ->label('Peça')
                    ->reactive()
                    ->options(function (callable $get, $state) {
                        // Obtém o ID do pedido selecionado
                        $orderId = $get('order_id');

                        // Busca o veículo associado ao pedido
                        $vehicleIdFromOrder = Order::where('id', $orderId)->value('vehicle_id');

                        // Se o pedido e o veículo relacionados forem válidos, retorna as peças associadas ao veículo
                        if ($orderId && $vehicleIdFromOrder) {
                            return Part::where('vehicle_id', $vehicleIdFromOrder)->pluck('title', 'id');
                        }

                        // Se houver um estado (part_id) e a peça for encontrada, retorna a peça
                        if ($state) {
                            $part = Part::find($state);
                            if ($part) {
                                return Part::where('id', $state)->pluck('title', 'id');
                            }
                        }

                        // Caso contrário, retorna todas as peças ordenadas por título
                        return Part::orderBy('title')->pluck('title', 'id');
                    })
                    ->searchable()
                    ->required(),

                Select::make('department_id')
                    ->label('Departamento')
                    ->options(function (callable $get) {
                        $partId = $get('part_id');
                        $part = Part::select('department_id')->find($partId);
                        if ($part && $part->department_id) {
                            return Department::where('id', $part->department_id)->pluck('title', 'id');
                        }
                        return Department::orderBy('title')->pluck('title', 'id');
                    })
                    ->searchable()
                    ->placeholder('Selecione um departamento'),

                Radio::make('status')
                    ->options(TypeOfServiceStatus::class),

                Forms\Components\DatePicker::make('deadline')
                    ->required(),

                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('part_id')
            ->columns([
                Tables\Columns\TextColumn::make('part_id')
                    ->getStateUsing(function ($record) {
                        return Part::where('id', $record->part_id)->first()->title;
                    }),
                Tables\Columns\TextColumn::make('status'),

                Tables\Columns\TextColumn::make('deadline')
                    ->since()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
