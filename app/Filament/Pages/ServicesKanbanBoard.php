<?php

namespace App\Filament\Pages;

use App\Enums\TypeOfServiceStatus;
use App\Enums\TypeOfLaborStatus; 
use App\Models\Department;
use App\Models\Order;
use App\Models\Observation;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Livewire\Attributes\On;

class ServicesKanbanBoard extends KanbanBoard implements HasActions 
{
    use InteractsWithActions; 
    protected static string $model = Service::class;
    protected static string $statusEnum = TypeOfServiceStatus::class;
    protected static string $recordTitleAttribute = 'formatted_title';

    public $selectedOrderNumber;
    public $selectedDepartment;
    public $selectedDepartmentUser;
    public $selectedOrderAndDepartment_order_number;
    public $selectedOrderAndDepartment_department;

    public string $getFilterTitle = '';
    public $getFilterAttribute = '';

    protected static string $view = 'service-kanban.kanban-board';
    protected static string $headerView = 'service-kanban.kanban-header';
    protected static string $recordView = 'service-kanban.kanban-record';
    protected static string $statusView = 'service-kanban.kanban-status';
    protected static string $scriptsView = 'service-kanban.kanban-scripts';

    public bool $disableEditModal = true;

    protected $listeners = [
        'laborStatusUpdated' => 'refreshRecord',
    ];

    public ?ServiceLabor $selectedLaborForCancellation = null; // Added
    public $recordIdForCancellation = null; // Added: To store the Service (parent record) ID for refreshing the card


    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearFilters')
                ->label('Limpar Filtros')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(function() {
                    return !empty($this->selectedOrderNumber) || 
                           !empty($this->selectedDepartment) || 
                           !empty($this->selectedOrderAndDepartment_order_number) || 
                           !empty($this->selectedOrderAndDepartment_department);
                })
                ->action(function () {
                    $this->reset([
                        'selectedOrderNumber', 
                        'selectedDepartment', 
                        'selectedDepartmentUser', 
                        'selectedOrderAndDepartment_order_number', 
                        'selectedOrderAndDepartment_department'
                    ]);
                }),
            
            Action::make('filterOptions')
                ->label('Opções de Filtro')
                ->icon('heroicon-o-funnel')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Aplicar Filtros')
                ->form([
                    Fieldset::make('Filtros')
                        ->schema([
                            Select::make('filterType')
                                ->label('Tipo de Filtro')
                                ->options([
                                    'orderNumber' => 'Número de Ordem',
                                    'clientName' => 'Cliente',
                                    'department' => 'Departamento',
                                ])
                                ->placeholder('Selecione o tipo de filtro')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (callable $set) {
                                    $set('selectedOrderNumber', null);
                                    $set('selectedClientName', null);
                                    $set('selectedDepartment', null);
                                    $set('selectedDepartmentUser', null);
                                }),

                            // Filtro por Número de Ordem
                            Select::make('selectedOrderNumber')
                                ->label('Número de Ordem')                        
                                ->options(Service::pluck('order_number', 'order_number')->toArray())
                                ->placeholder('Selecione um número de ordem')
                                ->searchable()
                                ->visible(fn (callable $get) => $get('filterType') === 'orderNumber')
                                ->required(fn (callable $get) => $get('filterType') === 'orderNumber'),

                            // Filtro por Cliente
                            Select::make('selectedClientName')
                                ->label('Cliente')
                                ->options(
                                    Order::with(['client', 'vehicle'])->get()->mapWithKeys(function ($order) {
                                        return [
                                            $order->order_number => "Ordem: {$order->order_number} - {$order->client->name} - {$order->vehicle->factory}/{$order->vehicle->model}"
                                        ];
                                    })->toArray()
                                )
                                ->placeholder('Selecione o cliente')
                                ->searchable()
                                ->visible(fn (callable $get) => $get('filterType') === 'clientName')
                                ->required(fn (callable $get) => $get('filterType') === 'clientName'),

                            // Filtro por Departamento
                            Select::make('selectedDepartment')
                                ->label('Departamento')
                                ->options(
                                    Department::with(['service', 'users'])->get()->mapWithKeys(function ($department) {
                                        return [
                                            $department->id => "{$department->title}"
                                        ];
                                    })->toArray()
                                )
                                ->placeholder('Selecione o departamento')
                                ->searchable()
                                ->reactive()
                                ->visible(fn (callable $get) => $get('filterType') === 'department')
                                ->required(fn (callable $get) => $get('filterType') === 'department')
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('selectedDepartmentUser', null);
                                }),
                            
                            Select::make('selectedDepartmentUser')
                                ->label('Usuário do Departamento')
                                ->options(function (callable $get) {
                                    $departmentId = $get('selectedDepartment');
                                    if (!$departmentId) {
                                        return [];
                                    }
                                    
                                    $department = Department::find($departmentId);
                                    if (!$department) {
                                        return [];
                                    }
                                    
                                    return $department->users()
                                        ->wherePivot('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($user) {
                                            $responsibleText = $user->pivot->is_responsible ? ' (Responsável)' : '';
                                            return [$user->id => $user->name . $responsibleText];
                                        })
                                        ->toArray();
                                })
                                ->placeholder('Selecione o usuário (opcional)')
                                ->searchable()
                                ->reactive()
                                ->visible(fn (callable $get) => $get('filterType') === 'department' && $get('selectedDepartment'))
                                ->disabled(function (callable $get) {
                                    return empty($get('selectedDepartment'));
                                }),
                        ]),
                ])
                ->action(function (array $data) {
                    // Limpar todos os filtros primeiro
                    $this->reset([
                        'selectedOrderNumber', 
                        'selectedDepartment', 
                        'selectedDepartmentUser',
                        'selectedOrderAndDepartment_order_number', 
                        'selectedOrderAndDepartment_department'
                    ]);

                    // Aplicar o filtro selecionado
                    switch ($data['filterType']) {
                        case 'orderNumber':
                            $this->selectedOrderNumber = $data['selectedOrderNumber'];
                            break;
                        case 'clientName':
                            $this->selectedOrderNumber = $data['selectedClientName'];
                            break;
                        case 'department':
                            $this->selectedDepartment = $data['selectedDepartment'];
                            $this->selectedDepartmentUser = $data['selectedDepartmentUser'] ?? null;
                            break;
                    }
                }),               
        ];
    }

    protected function records(): \Illuminate\Support\Collection
    {
        // Ensuring eager loading for display and potential filtering needs
        $query = Service::with([
            'order.client',
            'order.vehicle',
            'department',
            'user', // Assuming Service has a direct user_id for the person responsible for the service itself
            'part',
            'serviceLabors.labor', // Eager load labors within service
            'serviceLabors.user',  // Eager load user assigned to the labor
            'serviceLabors.observations' // Eager load observations for each labor
        ]);


        if (
            empty($this->selectedOrderNumber) &&
            empty($this->selectedDepartment) &&
            (empty($this->selectedOrderAndDepartment_order_number) || empty($this->selectedOrderAndDepartment_department))
        ) {
            // Removed Service::all() to apply eager loading by default.
            return $query->get();
        }


        if ($this->selectedOrderNumber) {
            $query->where('order_number', $this->selectedOrderNumber);
        }

        if ($this->selectedDepartment) {
            $query->where('department_id', $this->selectedDepartment);
            if ($this->selectedDepartmentUser) {
                // If filtering by user responsible for the Service itself
                // $query->where('user_id', $this->selectedDepartmentUser);

                // If filtering Services that have at least one ServiceLabor assigned to this user
                 $query->whereHas('serviceLabors', function ($q) {
                     $q->where('user_id', $this->selectedDepartmentUser);
                 });
            }
        }

        if ($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department) {
            $query->where('order_number', $this->selectedOrderAndDepartment_order_number)
                  ->where('department_id', $this->selectedOrderAndDepartment_department);
        }

        return $query->get();
    }

    protected function getAdditionalData(): string
    {
        if ($this->selectedOrderNumber) {
            return "Exibindo serviços da ordem: {$this->selectedOrderNumber}";
        }

        if ($this->selectedDepartment) {
            $department = Department::find($this->selectedDepartment);

            if (!$department) {
                return '<div class="text-red-600 dark:text-red-400">Departamento não encontrado</div>';
            }

            $html = '<div class="inline-block rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-3 text-sm text-gray-800 dark:text-gray-200 space-y-2 shadow-md">';
            $html .= "<div>Exibindo serviços do departamento: <strong>{$department->title}</strong></div>";

            if ($this->selectedDepartmentUser) {
                $user = User::find($this->selectedDepartmentUser);

                if ($user) {
                    $userAvatar = app('userAvatar')($user->id);
                    $userName = $user->name ?? 'Usuário desconhecido';

                    $html .= <<<HTML
                        <div class="flex items-center gap-2">
                            {$userAvatar}
                            <span class="font-semibold text-sm">{$userName}</span>
                        </div>
                    HTML;
                }
            } else {
                $activeUsers = $department->activeUsers()->get();

                if ($activeUsers->isNotEmpty()) {                

                    foreach ($activeUsers as $user) {
                        $userAvatar = app('userAvatar')($user->id);
                        $userName = $user->name;
                        $responsible = optional($user->pivot)->is_responsible
                            ? ' <span class="text-xs text-blue-600 dark:text-blue-400">(Responsável)</span>'
                            : '';

                        $html .= <<<HTML
                            <div class="flex items-center gap-2">
                                {$userAvatar}
                                <span class="text-sm">{$userName}{$responsible}</span>
                            </div>
                        HTML;
                    }
                }
            }

            $html .= '</div>';
            return $html;
        }

        if ($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department) {
            return "<div class='inline-block text-sm text-gray-700 dark:text-gray-300'>
                        Ordem: <strong>{$this->selectedOrderAndDepartment_order_number}</strong> -
                        Departamento: <strong>{$this->selectedOrderAndDepartment_department}</strong>
                    </div>";
        }

        return '<div class="inline-block text-sm text-gray-600 dark:text-gray-300">Exibindo todos os serviços de todas as ordens</div>';
    }

    public function updateLaborStatus($serviceLaborId, $newStatus, $recordId) // recordId is the Service ID
    {
        $serviceLabor = ServiceLabor::find($serviceLaborId);

        if (!$serviceLabor) {
            $this->dispatch('notify', message: 'Erro: Mão de obra não encontrada.', type: 'danger');
            return;
        }

        $originalStatusValue = $serviceLabor->status;

        // Rule 1: Cannot change a 'cancelado' labor to any other status.
        if ($originalStatusValue === TypeOfLaborStatus::cancelado->value && $newStatus !== TypeOfLaborStatus::cancelado->value) {
            Notification::make()
                ->title('Ação Não Permitida')
                ->body('Uma mão de obra cancelada não pode ter seu status alterado para outro.')
                ->warning()
                ->send();
            $this->dispatch('laborStatusUpdated', recordId: $recordId)->self(); // Revert dropdown by refreshing the card
            return;
        }

        // Rule 2: Cannot change a 'finalizado' labor to any other status.
        // This also implicitly prevents a 'finalizado' labor from being 'cancelado'.
        if ($originalStatusValue === TypeOfLaborStatus::finalizado->value && $newStatus !== TypeOfLaborStatus::finalizado->value) {
             Notification::make()
                ->title('Ação Não Permitida')
                ->body('Uma mão de obra finalizada não pode ter seu status alterado.')
                ->warning()
                ->send();
            $this->dispatch('laborStatusUpdated', recordId: $recordId)->self(); // Revert dropdown
            return;
        }

        // If the new status is 'cancelado'
        if ($newStatus === TypeOfLaborStatus::cancelado->value) {
            // If it's already 'cancelado' and user selects 'cancelado' again, do nothing.
            if ($originalStatusValue === TypeOfLaborStatus::cancelado->value) {
                return;
            }

            $this->selectedLaborForCancellation = $serviceLabor;
            $this->recordIdForCancellation = $recordId;
            $this->mountAction('cancelLaborOnServiceCard');
        } else {
            try {
                $statusEnum = TypeOfLaborStatus::from($newStatus);
                $serviceLabor->status = $statusEnum;
                 if ($statusEnum === TypeOfLaborStatus::em_andamento && is_null($serviceLabor->startedAt)) {
                 $serviceLabor->startedAt = now();
                }
                if ($statusEnum === TypeOfLaborStatus::finalizado && is_null($serviceLabor->finishedAt)) {
                $serviceLabor->finishedAt = now();
                }
                $serviceLabor->save();

                $this->dispatch('laborStatusUpdated', recordId: $recordId)->self();
                $this->dispatch('notify', message: 'Status da mão de obra atualizado com sucesso!', type: 'success');
            } catch (\ValueError $e) {
                $this->dispatch('notify', message: 'Erro: Status inválido selecionado.', type: 'danger');
            }
        }
    }

        // Action to handle the cancellation with observation
        public function cancelLaborOnServiceCard(): Action
        {
            return Action::make('cancelLaborOnServiceCard')
                ->label('Cancelar Mão de Obra')
                ->record(fn() => $this->selectedLaborForCancellation)
                ->modalHeading('Confirmar Cancelamento de Mão de Obra')
                ->form([
                    Placeholder::make('cancellation_info')
                        ->label('')
                        ->content(function (?ServiceLabor $record) {
                            if (!$record) return 'Mão de obra não selecionada.';
                            return "Você está cancelando a mão de obra: \"{$record->labor->title}\" associada ao serviço da ordem nº \"{$record->order->order_number}\".";
                        }),
                    RichEditor::make('cancellation_description')
                        ->label('Motivo do Cancelamento')
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
    
                    // Refresh the specific service card where this labor belongs
                    if ($this->recordIdForCancellation) {
                        $this->dispatch('laborStatusUpdated', recordId: $this->recordIdForCancellation)->self();
                    } else {
                        $this->dispatch('$refresh'); // Fallback to full refresh
                    }
                    $this->selectedLaborForCancellation = null; // Clear selection
                    $this->recordIdForCancellation = null;
                })
                ->modalCancelActionLabel('Voltar')
                ->modalSubmitActionLabel('Confirmar Cancelamento')
                ->modalWidth('xl');
        }

        
    // Register the action so mountAction can find it
    protected function getFormActions(): array
    {
        return [
            $this->cancelLaborOnServiceCard(),
            // ... any other actions that might be mounted ...
        ];
    }


    #[On('laborStatusUpdated')]
    public function refreshRecord($recordId): void
    {
        $record = ServiceLabor::find($recordId);
        if ($record) {
            $this->dispatch('recordUpdated', id: $record->id);
        }
    }
}