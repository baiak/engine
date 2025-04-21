<?php

namespace App\Filament\Pages;

use AllowDynamicProperties;
use App\Enums\TypeOfServiceStatus;
use App\Models\Department;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceLabor;
use Filament\Actions\Action;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;


#[AllowDynamicProperties] class ServicesKanbanBoard extends KanbanBoard
{
    protected static string $model = Service::class;
    protected static string $statusEnum = TypeOfServiceStatus::class;

    protected static string $recordTitleAttribute = 'formatted_title';

    public $selectedOrderNumber;
    public $selectedDepartment;

    public $selectedOrderAndDepartment;
    public $selectedOrderAndDepartment_order_number;
    public $selectedOrderAndDepartment_department;


    public $orderNumbers = [];


    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit'),

            Action::make('filterByOrderNumber')
                ->label('Filtrar por ordem') // Mejor etiqueta para la acción
                ->form([
                    \Filament\Forms\Components\Select::make('selectedOrderNumber')
                        ->label('Número de Ordem')
                        ->options(
                            Service::pluck('order_number', 'order_number') // Más eficiente
                        )
                        ->placeholder('Seleccione un número de orden')
                        ->searchable() // Permite buscar en la lista
                        ->reactive() // Hace que el campo sea reactivo
                        ->required() // Asegura que el usuario seleccione un valor
                        ->rules(['exists:services,order_number'])// Valida que el valor exista en la base de datos
                        ->live(),
                ])
                ->action(function (array $data) {
                    //resetar form
                    $this->selectedOrderAndDepartment_order_number = null;
                    $this->selectedOrderAndDepartment_department = null;
                    $this->selectedDepartment = null;
                    // Almacena el valor seleccionado en una propiedad del componente
                    $this->selectedOrderNumber = $data['selectedOrderNumber'];
                }),

            Action::make('filterByClientName')
                ->label('Filtrar por cliente')
                ->form([
                    \Filament\Forms\Components\Select::make('selectedClientName')
                        ->label('Cliente')
                        ->options(
                            Order::with('client','vehicle') // Carrega o relacionamento com Cliente e veiculo
                            ->get()
                                ->mapWithKeys(function ($order) {
                                    // Formata a chave e o valor para o select
                                    return [
                                        $order->order_number => "Ordem :{$order->order_number} - {$order->client->name} - {$order->vehicle->factory}/{$order->vehicle->model}"
                                    ];
                                })
                        )
                        ->placeholder('Selecione o cliente')
                        ->searchable() // Permite buscar en la lista
                        ->reactive() // Hace que el campo sea reactivo
                        ->required() // Asegura que el usuario seleccione un valor
                        ->live()
                ])
                ->action(function (array $data) {
                    //resetar form
                    $this->selectedOrderAndDepartment_order_number = null;
                    $this->selectedOrderAndDepartment_department = null;
                    $this->selectedDepartment = null;
                    // Almacena el valor seleccionado en una propiedad del componente
                    $this->selectedOrderNumber = $data['selectedClientName'];
                }),

            Action::make('filterByDepartment')
                ->label('Filtrar por departamento')
                ->form([
                    \Filament\Forms\Components\Select::make('selectedDepartment')
                        ->label('Departamento')
                        ->options(
                            Department::with('user') // Carrega o relacionamento
                            ->get()
                                ->mapWithKeys(function ($department) {
                                    // Formata a chave e o valor para o select
                                    return [
                                        $department->id => "{$department->title} - {$department->user->name}"
                                    ];
                                })
                        )
                        ->placeholder('Selecione o departamento')
                        ->searchable() // Permite buscar en la lista
                        ->reactive() // Hace que el campo sea reactivo
                        ->required() // Asegura que el usuario seleccione un valor
                ])
                ->action(function (array $data) {
                    //resetar form
                    $this->selectedOrderAndDepartment_order_number = null;
                    $this->selectedOrderAndDepartment_department = null;
                    $this->selectedOrderNumber = null;
                    // Almacena el valor seleccionado en una propiedad del componente
                    $this->selectedDepartment = $data['selectedDepartment'];
                }),

            Action::make('filterByOrderNumberAndDepartment')
                ->label('Filtrar por Ordem e Departamento')
                ->form([
                    \Filament\Forms\Components\Select::make('selectedOrderNumber')
                    ->options(
                        Service::pluck('order_number', 'order_number')
                    ),
                    \Filament\Forms\Components\Select::make('selectedDepartment')
                    ->options(
                        Department::with('user') // Carrega o relacionamento
                        ->get()
                            ->mapWithKeys(function ($department) {
                                // Formata a chave e o valor para o select
                                return [
                                    $department->id => "{$department->title} - {$department->user->name}"
                                ];
                            })
                    )
                ])
                ->action(function (array $data) {
                    //resetar form
                    $this->selectedOrderNumber = null;
                    $this->selectedDepartment = null;

                    $this->selectedOrderAndDepartment_order_number = $data['selectedOrderNumber'];
                    $this->selectedOrderAndDepartment_department = $data['selectedDepartment'];
                })
        ];
    }
    public string $getFilterTitle = '';
    public $getFilterAttribute = '';
    protected function records(): \Illuminate\Support\Collection
    {     // Se nenhum filtro estiver definido, retorna todos os registros
        if (
            empty($this->selectedOrderNumber) &&
            empty($this->selectedDepartment) &&
            (empty($this->selectedOrderAndDepartment_order_number) || empty($this->selectedOrderAndDepartment_department))
        ) {
            return Service::all();
        }
        /*return Service::query()
            ->when($this->selectedOrderNumber, fn($query) =>
            $query->where('order_number', $this->selectedOrderNumber)
            )
            ->when($this->selectedDepartment, fn($query) =>
            $query->where('department_id', $this->selectedDepartment)
            )
            ->when(
                $this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department,
                fn($query) => $query->where('department_id', $this->selectedOrderAndDepartment_department)
                    ->where('order_number', $this->selectedOrderAndDepartment_order_number)
            )
            ->get();-*/
        return Service::query()
            ->when($this->selectedOrderNumber, function ($query) {
                return $query->where('order_number', $this->selectedOrderNumber);
            })
           ->when($this->selectedDepartment, function ($query) {
               return $query->where('department_id', $this->selectedDepartment);
           })
            ->when($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department,
                function ($query) {
                 return $query->where('department_id', $this->selectedOrderAndDepartment_department)
                     ->where('order_number', $this->selectedOrderAndDepartment_order_number);
                })->get();
    }

    protected function getAdditionalData(): string
    {
        if ($this->selectedOrderNumber) {
            return "Exibindo serviços da ordem: {$this->selectedOrderNumber}";
        }

        if ($this->selectedDepartment) {
            $department = Department::find($this->selectedDepartment);

            if (!$department) {
                return 'Departamento não encontrado';
            }

            $user = optional($department->user);
            $userAvatar = app('userAvatar')($user->id ?? null);
            $userName = app('userName')($user->id ?? 'Usuário desconhecido');

            return <<<HTML
        <div style="background-color: #f0f0f0; padding: 8px; border-radius: 6px; font-size: 14px; color: #181918;">
            Exibindo serviços do departamento: <b>{$department->title}</b>
            <div style="display: flex; align-items: center; margin: 8px 0; background-color: #f0f0f0; padding: 8px; border-radius: 6px;">
                {$userAvatar}
                <span style="margin-left: 8px; font-size: 14px; color: #181918; font-weight: bold;">{$userName}</span>
            </div>
        </div>
        HTML;
        }

        if ($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department) {
            return "Ordem: {$this->selectedOrderAndDepartment_order_number} - Departamento: {$this->selectedOrderAndDepartment_department}";
        }

        return 'Exibindo todos os serviços de todas as ordens';
    }


    protected static string $view = 'service-kanban.kanban-board';

    protected static string $headerView = 'service-kanban.kanban-header';

    protected static string $recordView = 'service-kanban.kanban-record';

    protected static string $statusView = 'service-kanban.kanban-status';

    protected static string $scriptsView = 'service-kanban.kanban-scripts';

    public bool $disableEditModal = true;

    protected $listeners = [
        'laborStatusUpdated' => 'refreshRecord'
    ];

    #[On('laborStatusUpdated')]
    public function refreshRecord($recordId)
    {
        // Em vez de recarregar todo o card, apenas atualizar o modelo
        $record = ServiceLabor::find($recordId);

        // Não recarregue o componente inteiro
        // NÃO faça $this->dispatch('refreshKanban');

        // Você pode atualizar uma propriedade específica se necessário
        // $this->records = YourRecordModel::all(); // Só se necessário

        // Ou emitir um evento específico para o cliente
        $this->dispatch('recordUpdated', recordId: $recordId);
    }


}
