<?php

namespace App\Filament\Pages;

use AllowDynamicProperties;
use App\Enums\TypeOfLaborStatus;
use App\Enums\TypeOfOrderStatus;
use App\Enums\TypeOfServiceStatus;
use App\Models\Department;
use App\Models\Labor;
use App\Models\Order;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use App\Models\Observation;
use App\Traits\HasHeaderActions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Livewire\Attributes\On;

#[AllowDynamicProperties] class ServiceLaborBoard extends KanbanBoard implements HasActions
{
    public bool $disableEditModal = true;

    use HasHeaderActions;
    use InteractsWithActions;
    public $selectedOrderNumber;
    public $selectedDepartment;

    public $selectedOrderAndDepartment;
    public $selectedOrderAndDepartment_order_number;
    public $selectedOrderAndDepartment_department;
    public $selectedClient;

    protected static string $headerView = 'service-labor-kanban.kanban-header';
    protected static string $recordView = 'service-labor-kanban.kanban-record';
    protected static string $scriptsView = 'service-labor-kanban.kanban-scripts';

    public ?ServiceLabor $selectedLaborForAction = null;
    public ?ServiceLabor $selectedLaborForObservation = null; // Property to hold the record for adding observation

        /**
     * Define o nome da coluna do banco de dados que armazena o status dos registros do Kanban.
     *
     * @return string
     */
    protected function getStatusColumn(): string
    {
        // Substitua 'status' pelo nome real da sua coluna de status no banco de dados,
        // se for diferente. Se for 'status', mantenha como está.
        return 'status';
    }

     /**
     * Sobrescreve o método statusChanged para adicionar lógica de bloqueio
     * para itens cancelados.
     * Este método é chamado pelo Livewire quando o evento 'status-changed' é recebido.
     */
    #[On('status-changed')]
    public function statusChanged($recordId, $status, $fromOrderedIds = [], $toOrderedIds = []): void
    {
        $newStatus = $status;
  
        $fromOrderedIds = is_array($fromOrderedIds) ? $fromOrderedIds : [];
        $toOrderedIds = is_array($toOrderedIds) ? $toOrderedIds : [];
    

        $safeRecordId = (string) $recordId;
        $safeNewStatus = (string) $newStatus;

        $modelClass = static::$model;
        if (!class_exists($modelClass)) {
            Notification::make()
                ->title('Erro de Configuração')
                ->body("A classe do modelo '{$modelClass}' não foi encontrada.")
                ->danger()
                ->send();
            $this->dispatch('$refresh');
            return;
        }
    
        $record = $modelClass::find($safeRecordId);
    
        if (!$record) {
            Notification::make()
                ->title('Erro')
                ->body('Registro não encontrado.')
                ->danger()
                ->send();
            $this->dispatch('$refresh');
            return;
        }
    
        $statusColumn = $this->getStatusColumn();
        $originalStatus = $record->{$statusColumn}; 
    
        // Comparações e atualizações devem usar os valores sanitizados/convertidos
        if ($originalStatus === TypeOfLaborStatus::cancelado->value && $safeNewStatus !== TypeOfLaborStatus::cancelado->value) {
            Notification::make()
                ->title('Ação Não Permitida')
                ->body('Uma mão de obra cancelada não pode ter seu status alterado para outro.')
                ->warning()
                ->send();
            $this->dispatch('$refresh');
            return;
        }
    
        $record->update([$statusColumn => $safeNewStatus]);
    

        if (method_exists($this, 'onStatusChanged')) {
            $this->onStatusChanged(
                $safeRecordId,      // Garante que é string
                $safeNewStatus,     // Garante que é string
                $fromOrderedIds,    // Já garantido como array
                $toOrderedIds       // Já garantido como array
            );
        }
    }

    #[On('openCancelLaborModal')]
    public function openCancelModal($recordId): void
    {
        if (empty($recordId)) {
            Notification::make()
                ->title('Erro')
                ->body('ID do registro não fornecido.')
                ->danger()
                ->send();
            return;
        }

        $this->selectedLaborForAction = ServiceLabor::find($recordId);
        if ($this->selectedLaborForAction) {
            if (in_array($this->selectedLaborForAction->status, [TypeOfLaborStatus::cancelado->value, TypeOfLaborStatus::finalizado->value])) {
                Notification::make()
                    ->title('Ação não permitida')
                    ->body('Esta mão de obra já está finalizada ou cancelada.')
                    ->warning()
                    ->send();
                return;
            }
            $this->mountAction('cancelLabor');
        } else {
            Notification::make()
                ->title('Erro')
                ->body('Mão de obra não encontrada para cancelamento.')
                ->danger()
                ->send();
            Log::error('Mão de obra não encontrada em openCancelModal.', ['recordId' => $recordId]);
        }
    }

    // Method to open the add observation modal
    #[On('openAddObservationModal')]
    public function openAddObservationModal($recordId): void
    {
        if (empty($recordId)) {
            Notification::make()
                ->title('Erro')
                ->body('ID do registro não fornecido.')
                ->danger()
                ->send();
            return;
        }

        $this->selectedLaborForObservation = ServiceLabor::find($recordId);
        if ($this->selectedLaborForObservation) {
            // You might want to add checks here if observations are not allowed for certain statuses
            // For example, if ($this->selectedLaborForObservation->status->value === TypeOfLaborStatus::cancelado->value) { ... }
            $this->mountAction('addObservation');
        } else {
            Notification::make()
                ->title('Erro')
                ->body('Mão de obra não encontrada para adicionar observação.')
                ->danger()
                ->send();
            Log::error('Mão de obra não encontrada em openAddObservationModal.', ['recordId' => $recordId]);
        }
    }

    public function cancelLabor(): Action
    {
        return Action::make('cancelLabor')
            ->label('Cancelar Mão de Obra')
            ->record(fn() => $this->selectedLaborForAction)
            ->modalHeading('Cancelar Mão de Obra')
            ->form([
                Placeholder::make('cancellation_reason_title')
                    ->label('')
                    ->content('Motivo do Cancelamento'),
                RichEditor::make('cancellation_description')
                    ->label('Descrição Detalhada')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, ServiceLabor $record) {
                Observation::create([
                    'service_labor_id' => $record->id,
                    'order_id' => $record->order_id,
                    'service_id' => $record->service_id,
                    'user_id' => Auth::id(),
                    'title' => 'Mão de obra cancelada',
                    'description' => $data['cancellation_description'],
                ]);

                $record->status = TypeOfLaborStatus::cancelado->value;
                $record->save();

                Notification::make()
                    ->title('Mão de Obra Cancelada')
                    ->body('A mão de obra foi marcada como cancelada.')
                    ->success()
                    ->send();
                $this->dispatch('$refresh');
            })
            ->modalSubmitActionLabel('Confirmar Cancelamento')
            ->modalWidth('xl');
    }

    // Define the add observation action
    public function addObservation(): Action
    {
        return Action::make('addObservation')
            ->label('Adicionar Observação')
            ->record(fn() => $this->selectedLaborForObservation) // Pass the selected record to the modal
            ->modalHeading('Adicionar Nova Observação')
            ->form([
                TextInput::make('observation_title')
                    ->label('Título da Observação')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('observation_description')
                    ->label('Descrição da Observação')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, ServiceLabor $record) { // $record is passed automatically
                Observation::create([
                    'service_labor_id' => $record->id,
                    'order_id' => $record->order_id,
                    'service_id' => $record->service_id,
                    'user_id' => Auth::id(), // Current logged-in user
                    'title' => $data['observation_title'],
                    'description' => $data['observation_description'],
                ]);

                Notification::make()
                    ->title('Observação Adicionada')
                    ->body('A observação foi adicionada com sucesso.')
                    ->success()
                    ->send();

                $this->dispatch('$refresh'); // Refresh the Kanban board to show new observation (if displayed directly) or just to update data
            })
            ->modalSubmitActionLabel('Salvar Observação')
            ->modalWidth('xl');
    }


    protected function getFormActions(): array
    {
        return [
            $this->cancelLabor(),
            $this->addObservation(), // Add the new action here
        ];
    }


    protected function getHeaderActions(): array
    {
        $actions = [];
        $userId = Auth::id();

        // Ação de filtro existente (será sempre adicionada)
        $actions[] = Action::make('filterForm')
            ->label('Filtrar')
            ->form([
                Select::make('selectedOrderNumber')
                    ->label('Número de Ordem')
                    ->options(
                        Order::select(['id', 'order_number', 'client_id', 'vehicle_id'])
                            ->with(['client:id,name', 'vehicle:id,factory,model'])
                            ->get()
                            ->mapWithKeys(function ($order) {
                                return [$order->id => $order->getFormattedTitleAttribute()];
                            })
                    )
                    ->placeholder('Selecione uma ordem')
                    ->searchable()
                    ->reactive()
                    ->live(),


                Select::make('selectedDepartment')
                    ->label('Departamento')
                    ->options(
                        Department::with('users')
                            ->get()
                            ->mapWithKeys(function ($department) {
                                $responsibleUser = $department->responsibleUser();
                                return [
                                    $department->id => "{$department->title}" . ($responsibleUser ? " - Resp: {$responsibleUser->name}" : "")
                                ];
                            })
                    )
                    ->placeholder('Selecione o departamento')
                    ->searchable()
                    ->reactive()
            ])
            ->action(function (array $data) {
                $this->selectedOrderNumber = $data['selectedOrderNumber'] ?? null;
                $this->selectedClient = $data['selectedClient'] ?? null;
                $this->selectedDepartment = $data['selectedDepartment'] ?? null;
            });


        $canShowAddLaborButton = Service::where('status', TypeOfServiceStatus::pendente->value)
            ->where('user_id', $userId)
            ->exists();

        if ($canShowAddLaborButton) {
            $actions[] = Action::make('addLaborToService')
                ->label('Adicionar Minha Mão de Obra')
                ->modalHeading('Adicionar Mão de Obra a Serviço Pendente Seu')
                ->form([
                    Select::make('order_id')
                        ->label('Ordem de Serviço (Com seus serviços pendentes)')
                        ->options(function () use ($userId) {
                            return Order::where('status', TypeOfOrderStatus::aguardando_servicos)
                                ->whereHas('service', function ($query) use ($userId) {
                                    $query->where('status', TypeOfServiceStatus::pendente->value)
                                        ->where('user_id', $userId);
                                })
                                ->get()
                                ->mapWithKeys(function ($order) {
                                    return [$order->id => $order->getFormattedTitleAttribute()];
                                });
                        })
                        ->live()
                        ->searchable()
                        ->required()
                        ->placeholder('Selecione uma Ordem de Serviço'),

                    Select::make('service_id')
                        ->label('Seu Serviço Pendente')
                        ->options(function (Get $get) use ($userId) {
                            $orderId = $get('order_id');
                            if (!$orderId) {
                                return [];
                            }
                            return Service::where('order_id', $orderId)
                                ->where('status', TypeOfServiceStatus::pendente->value)
                                ->where('user_id', $userId)
                                ->get()
                                ->mapWithKeys(function ($service) {
                                    $partDescription = $service->part ? $service->part->title : 'Peça não especificada';
                                    return [$service->id => "Serviço #{$service->id} ({$partDescription}) - {$service->description}"];
                                });
                        })
                        ->disabled(fn(Get $get) => !$get('order_id'))
                        ->live()
                        ->searchable()
                        ->required()
                        ->placeholder('Selecione um Serviço'),

                    Select::make('labor_id')
                        ->label('Mão de Obra a Executar')
                        ->options(Labor::all()->pluck('title', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Selecione a Mão de Obra')
                        ->createOptionForm([
                            TextInput::make('title')
                                ->label('Título da Mão de Obra')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label('Descrição (Opcional)')
                                ->rows(3),
                            Select::make('part_id')
                                ->label('Peça Associada (Opcional)')
                                ->options(Part::all()->pluck('title', 'id'))
                                ->searchable()
                                ->placeholder('Selecione uma peça, se aplicável'),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $newLabor = Labor::create([
                                'title' => $data['title'],
                                'description' => $data['description'],
                                'part_id' => $data['part_id'] ?? null,
                            ]);
                            return $newLabor->id;
                        }),

                    Textarea::make('description')
                        ->label('Observações para esta Mão de Obra no Serviço (Opcional)')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($userId) {
                    $service = Service::find($data['service_id']);

                    if (!$service || $service->user_id !== $userId || $service->status->value !== TypeOfServiceStatus::pendente->value) {
                        Notification::make()
                            ->title('Erro de Validação')
                            ->body('O serviço selecionado não está mais disponível ou não lhe pertence.')
                            ->danger()
                            ->send();
                        return;
                    }

                    ServiceLabor::create([
                        'order_id' => $service->order_id,
                        'service_id' => $data['service_id'],
                        'labor_id' => $data['labor_id'],
                        'user_id' => $userId,
                        'description' => $data['description'],
                        'status' => TypeOfLaborStatus::Aguardando_aprovacao->value,
                        'includedAt' => now(),
                    ]);

                    Notification::make()
                        ->title('Sucesso')
                        ->body('Mão de obra adicionada ao seu serviço pendente!')
                        ->success()
                        ->send();
                })
                ->modalWidth('xl');
        }

        return $actions;
    }


    protected function getOrderNumber($id)
    {
        return Order::find($id)?->order_number;
    }

    protected static string $model = ServiceLabor::class;
    protected static string $statusEnum = TypeOfLaborStatus::class;


    protected function records(): \Illuminate\Support\Collection
    {
        $userId = Auth::id();
        $query = ServiceLabor::with([
            'labor',
            'service.order.client',
            'service.part',
            'service.department', // Eager load department via service
            'order.client',
            'service.user',
            'observations', // Eager load observations to display them
        ]);

        $hasFilters = !empty($this->selectedOrderNumber) ||
            !empty($this->selectedClient) ||
            !empty($this->selectedDepartment) ||
            (!empty($this->selectedOrderAndDepartment_order_number) && !empty($this->selectedOrderAndDepartment_department));

        if (!$hasFilters) {
            // Consider if you want default filtering for the logged-in user here
            // Example: $query->where('user_id', $userId);
            // or $query->whereHas('service.department.users', fn($q) => $q->where('user_id', $userId));
            // For now, returning all records if no filter is applied.
            return $query->get();
        }

        if ($this->selectedOrderNumber) {
            $query->where('order_id', $this->selectedOrderNumber);
        }

        if ($this->selectedClient) {
            $query->whereHas('order', function ($subQuery) {
                $subQuery->where('client_id', $this->selectedClient);
            });
        }

        // Updated to correctly filter by department through the service relationship
        if ($this->selectedDepartment) {
            $query->whereHas('service', function ($subQuery) {
                $subQuery->where('department_id', $this->selectedDepartment);
            });
        }


        if ($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department) {
            $orderNumberForFilter = $this->selectedOrderAndDepartment_order_number;
            $departmentIdForFilter = $this->selectedOrderAndDepartment_department;

            $query->whereHas('order', function ($orderQuery) use ($orderNumberForFilter) {
                $orderQuery->where('order_number', $orderNumberForFilter);
            })->whereHas('service', function ($serviceQuery) use ($departmentIdForFilter) {
                $serviceQuery->where('department_id', $departmentIdForFilter);
            });
        }

        return $query->get();
    }
}