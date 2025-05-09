<?php

namespace App\Filament\Pages;

use App\Enums\TypeOfServiceStatus;
use App\Models\Department;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Livewire\Attributes\On;

class ServicesKanbanBoard extends KanbanBoard
{
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
            
            Action::make('filterByOrderNumber')
                ->label('Filtrar por ordem')
                ->form([
                    Select::make('selectedOrderNumber')
                        ->label('Número de Ordem')
                        ->options(Service::pluck('order_number', 'order_number')->toArray())
                        ->placeholder('Selecione um número de ordem')
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->live(),
                ])
                ->action(function (array $data) {
                    $this->reset(['selectedDepartment', 'selectedDepartmentUser', 'selectedOrderAndDepartment_order_number', 'selectedOrderAndDepartment_department']);
                    $this->selectedOrderNumber = $data['selectedOrderNumber'];
                }),

            Action::make('filterByClientName')
                ->label('Filtrar por cliente')
                ->form([
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
                        ->reactive()
                        ->required()
                        ->live(),
                ])
                ->action(function (array $data) {
                    $this->reset(['selectedDepartment', 'selectedDepartmentUser', 'selectedOrderAndDepartment_order_number', 'selectedOrderAndDepartment_department']);
                    $this->selectedOrderNumber = $data['selectedClientName'];
                }),
            Action::make('filterByDepartment')
                ->label('Filtrar por departamento')
                ->form([
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
                        ->required()
                        ->live()
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
                        ->disabled(function (callable $get) {
                            return empty($get('selectedDepartment'));
                        })
                        ->live(),
                ])
                ->action(function (array $data) {
                    $this->reset(['selectedOrderNumber', 'selectedOrderAndDepartment_order_number', 'selectedOrderAndDepartment_department']);
                    $this->selectedDepartment = $data['selectedDepartment'];
                    $this->selectedDepartmentUser = $data['selectedDepartmentUser'] ?? null;
                }),               
        ];
    }

    protected function records(): \Illuminate\Support\Collection
    {
        if (
            empty($this->selectedOrderNumber) &&
            empty($this->selectedDepartment) &&
            (empty($this->selectedOrderAndDepartment_order_number) || empty($this->selectedOrderAndDepartment_department))
        ) {
            return Service::all();
        }

        $query = Service::query();

        // Filtro por número de ordem
        if ($this->selectedOrderNumber) {
            $query->where('order_number', $this->selectedOrderNumber);
        }

        // Filtro por departamento
        if ($this->selectedDepartment) {
            $query->where('department_id', $this->selectedDepartment);
            
            // Se tiver usuário selecionado, filtra pelos serviços desse usuário
            if ($this->selectedDepartmentUser) {
                $query->where('user_id', $this->selectedDepartmentUser);
            }
        }

        // Filtro por ordem e departamento combinados
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



    #[On('laborStatusUpdated')]
    public function refreshRecord($recordId): void
    {
        $record = ServiceLabor::find($recordId);
        if ($record) {
            $this->dispatch('recordUpdated', id: $record->id);
        }
    }
}