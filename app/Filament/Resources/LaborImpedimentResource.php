<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaborImpedimentResource\Pages;
use App\Models\LaborImpediment;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\Department; // Assuming you have a Department model
use App\Models\User;
use App\Enums\TypeOfLaborImpedimentStatus;
use App\Enums\TypeOfServiceStatus; // Assuming you have this enum for Service statuses
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get; // Import Get
use Filament\Forms\Set; // Import Set
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;


class LaborImpedimentResource extends Resource
{
    protected static ?string $model = LaborImpediment::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation'; // Choose an icon

    protected static ?string $navigationLabel = 'Impedimentos de Mão de Obra';

    protected static ?string $modelLabel = 'Impedimento de Mão de Obra';

    protected static ?string $pluralModelLabel = 'Impedimentos de Mão de Obra';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Ordem de Serviço')
                    ->options(function () {
                        return \App\Models\Order::query()
                            ->whereHas('service', function (Builder $query) {
                                $query->whereIn('status', [\App\Enums\TypeOfServiceStatus::pendente, \App\Enums\TypeOfServiceStatus::aprovado])
                                      ->whereHas('serviceLabors', function (Builder $subQuery) {
                                          $subQuery->whereIn('status', ['pendente', 'aguardando aprovação', 'aprovado', 'em andamento']);
                                      });
                            })
                            ->get()
                            ->mapWithKeys(fn(\App\Models\Order $order) => [
                                $order->id => $order->getFormattedTitleAttribute() // This should already return a string
                            ])
                            ->toArray();
                    })
                    ->live() // Use live() for reactivity
                    ->afterStateUpdated(fn(Set $set) => $set('service_id', null))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('service_id')
                    ->label('Serviço')
                    ->options(function (Get $get) {
                        $orderId = $get('order_id');
                        if (!$orderId) {
                            return [];
                        }
                        return \App\Models\Service::query()
                            ->where('order_id', $orderId)
                            ->whereIn('status', [\App\Enums\TypeOfServiceStatus::pendente, \App\Enums\TypeOfServiceStatus::aprovado])
                            ->whereHas('serviceLabors', function (Builder $subQuery) {
                                $subQuery->whereIn('status', ['pendente', 'aguardando aprovação', 'aprovado', 'em andamento']);
                            })
                            ->with('order') // Eager load order to prevent N+1 if accessing order details
                            ->get()
                            ->mapWithKeys(function (\App\Models\Service $service) {
                                $description = $service->description ?? ('Serviço ID: ' . $service->id);
                                $orderNumber = $service->order ? ($service->order->order_number ?? 'N/A') : 'N/A';
                                return [
                                    $service->id => "{$description} (OS: {$orderNumber})"
                                ];
                            })
                            ->toArray();
                    })
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('service_labor_id', null))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('service_labor_id') // This will be stored **
                    ->label('Mão de Obra')
                    ->options(function (Get $get) {
                        $serviceId = $get('service_id');
                        if (!$serviceId) {
                            return [];
                        }
                        return \App\Models\ServiceLabor::query()
                            ->where('service_id', $serviceId)
                            ->whereIn('status', ['pendente', 'aguardando aprovação', 'aprovado', 'em andamento'])
                            ->with('labor') // Eager load labor for its title
                            ->get()
                            ->mapWithKeys(function (\App\Models\ServiceLabor $sl) {
                                $laborTitle = $sl->labor ? ($sl->labor->title ?? 'Título Indisponível') : 'Mão de Obra N/A'; // (Adjusted)
                                $status = $sl->status ?? 'Status N/A'; // Fallback for status
                                return [
                                    $sl->id => "ID: {$sl->id} - {$laborTitle} (Status: {$status})"
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('target_audience') // Not mapped directly, used for logic
                    ->label('Direcionar Impedimento Para')
                    ->options(function () {
                        // Ensure department names are not null, or provide a fallback
                        $departments = \App\Models\Department::all()->mapWithKeys(function ($department) {
                            return [$department->id => $department->name ?? "Departamento ID: {$department->id} (Sem Nome)"];
                        })->toArray();
                        $departments['all_system_users'] = 'Todos os Usuários (do Sistema)';
                        return $departments;
                    })
                    ->helperText('Se um departamento for selecionado, o impedimento será criado para todos os usuários daquele departamento. Se "Todos os Usuários" for selecionado, será criado para cada usuário no sistema.')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('reason') // **
                    ->label('Motivo do Impedimento')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status') // **
                    ->label('Status Inicial do Impedimento')
                    ->options(TypeOfLaborImpedimentStatus::class) // **
                    ->required(),

                Forms\Components\Textarea::make('description_for_log') // Not mapped directly
                    ->label('Descrição/Observação Inicial (para o log)')
                    ->helperText('Esta descrição será o primeiro registro no histórico do impedimento.')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([ // This enables a card-like layout
                'md' => 2, // 2 columns on medium screens
                'lg' => 3, // 3 columns on large screens
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('serviceLabor.labor.title') // **
                    ->label('Mão de Obra')
                    ->tooltip(fn(LaborImpediment $record): string => "Serviço: {$record->serviceLabor?->service?->description} (OS: {$record->serviceLabor?->service?->order?->order_number})")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reason') // **
                    ->label('Motivo')
                    ->limit(50)
                    ->tooltip(fn(LaborImpediment $record): string => $record->reason)
                    ->searchable(),
                Tables\Columns\TextColumn::make('complainantUser.name')
                    ->label('Reportado Por')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status') // **
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(), // Add if you create an Edit page
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Define relation managers if needed later (e.g., for logs)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaborImpediments::route('/'),
            'create' => Pages\CreateLaborImpediment::route('/create'),
            'view' => Pages\ViewLaborImpediment::route('/{record}'), // View page is good for showing details including logs
             //'edit' => Pages\EditLaborImpediment::route('/{record}/edit'),
        ];
    }
}
