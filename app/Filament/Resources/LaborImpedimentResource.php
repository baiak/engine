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
                    ->native(false)
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
                    ->label('Peça/Serviço')
                    ->options(function (Get $get) {
                        $orderId = $get('order_id');
                        if (!$orderId) {
                            return [];
                        }
                        return Service::query()
                            ->where('order_id', $orderId)
                            ->whereIn('status', [\App\Enums\TypeOfServiceStatus::pendente->value, \App\Enums\TypeOfServiceStatus::aprovado->value])
                            ->whereHas('serviceLabors', function (Builder $subQuery) {
                                $subQuery->whereIn('status', ['pendente', 'aguardando aprovação', 'aprovado', 'em andamento']);
                            })
                            ->with(['order:id,order_number', 'part:id,title']) // Eager load 'part' relationship
                            ->get()
                            ->mapWithKeys(function (Service $service) {
                                // Obter o nome da peça usando o atributo 'title' do modelo Part
                                $partName = $service->part ? ($service->part->title ?? 'Peça não especificada') : 'Peça não vinculada'; //

                                // Limpar HTML da descrição do serviço
                                $rawServiceDescription = $service->description ?? '';
                                $serviceDescription = strip_tags((string) $rawServiceDescription);
                                if (empty(trim($serviceDescription))) {
                                    $serviceDescription = 'serviço não descrito';
                                }

                                $orderNumber = $service->order ? ($service->order->order_number ?? 'N/A') : 'N/A';

                                // Construir o novo rótulo
                                $label = "Peça: {$partName} - {$serviceDescription} (OS: {$orderNumber})";

                                return [
                                    $service->id => $label
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
                    ->native(false)
                    ->options(function (Get $get) {
                        $serviceId = $get('service_id');
                        if (!$serviceId) {
                            return [];
                        }
                        return \App\Models\ServiceLabor::query()
                            ->where('service_id', $serviceId)
                            ->whereIn('status', ['pendente', 'aguardando aprovação', 'aprovado', 'em andamento'])
                            ->with('labor','service.part') // Eager load labor for its title
                            ->get()
                            ->mapWithKeys(function (\App\Models\ServiceLabor $sl) {
                                $laborTitle = $sl->labor ? ($sl->labor->title ?? 'Título Indisponível') : 'Mão de Obra N/A'; // (Adjusted)
                                $status = $sl->status ?? 'Status N/A'; // Fallback for status
                                return [
                                    $sl->id => "Peça: {$sl->service->part->title} - {$laborTitle} (Status: {$status})"
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                // --- New Targeting Logic ---
                Forms\Components\Select::make('target_selection_type')
                    ->label('Como Direcionar o Impedimento?')
                    ->native(false)

                    ->options([
                        'department_specific_user' => 'Para um Usuário Específico de um Departamento',
                        'department_all_users' => 'Para Todos os Usuários de um Departamento',
                        'system_all_users' => 'Para Todos os Usuários do Sistema',
                    ])
                    ->live() // Essential for conditional visibility
                    ->required()
                    ->afterStateUpdated(function (Set $set) { // Reset dependent fields when this changes
                        $set('target_department_id', null);
                        $set('final_complained_user_id', null);
                    })
                    ->columnSpanFull(),

                Forms\Components\Select::make('target_department_id')
                    ->label('Selecione o Departamento')
                    ->native(false)
                    ->options(Department::all()->pluck('title', 'id')->toArray()) //
                    ->live()
                    ->visible(fn(Get $get) => in_array($get('target_selection_type'), ['department_specific_user', 'department_all_users']))
                    ->required(fn(Get $get) => in_array($get('target_selection_type'), ['department_specific_user', 'department_all_users']))
                    ->searchable()
                    ->afterStateUpdated(fn(Set $set) => $set('final_complained_user_id', null)) // Reset user if department changes
                    ->columnSpanFull(),

                Forms\Components\Select::make('final_complained_user_id') // This field will store the target user's ID if a specific user is chosen
                    ->label('Selecione o Usuário Específico')
                    ->native(false)
                    ->options(function (Get $get) {
                        $departmentId = $get('target_department_id');
                        if ($get('target_selection_type') === 'department_specific_user' && $departmentId) {
                            $department = Department::find($departmentId);
                            // Uses the 'users' relationship from Department model and 'name' from User model
                            return $department ? $department->users()->pluck('users.name', 'users.id')->toArray() : [];
                        }
                        return []; // No options if not applicable
                    })
                    ->searchable()
                    ->visible(fn(Get $get) => $get('target_selection_type') === 'department_specific_user')
                    ->required(fn(Get $get) => $get('target_selection_type') === 'department_specific_user')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('reason') // **
                    ->label('Motivo do Impedimento')
                    ->hint('Descreva uma razão para o impedimento de forma clara, resumida e objetiva.')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status') // **
                    ->label('Status Inicial do Impedimento')
                    ->native(false)
                    ->options([\App\Enums\TypeOfLaborImpedimentStatus::getValues()])
                    ->default(TypeOfLaborImpedimentStatus::em_aberto->value) // Define o valor padrão
                    ->required(),

                Forms\Components\RichEditor::make('description_for_log') // Not mapped directly
                    ->label('Descrição/Observação Inicial do Impedimento')
                    ->helperText('Esta descrição será o primeiro registro no histórico do impedimento.')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
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
