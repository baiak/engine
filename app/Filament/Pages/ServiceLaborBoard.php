<?php

namespace App\Filament\Pages;

use AllowDynamicProperties;
use App\Enums\TypeOfLaborStatus;
use App\Models\Department;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceLabor;
use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

#[AllowDynamicProperties] class ServiceLaborBoard extends KanbanBoard
{
    public $selectedOrderNumber;
    public $selectedDepartment;

    public $selectedOrderAndDepartment;
    public $selectedOrderAndDepartment_order_number;
    public $selectedOrderAndDepartment_department;

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


    protected function getOrderNumber($id){
        return([Order::query()->where('id', $id)->get()]);
    }
    protected static string $model = ServiceLabor::class;
    protected static string $statusEnum = TypeOfLaborStatus::class;

    protected static string $view = 'service-labor-kanban.kanban-board';
    protected static string $headerView = 'service-labor-kanban.kanban-header';
    protected static string $recordView = 'service-labor-kanban.kanban-record';
    protected static string $statusView = 'service-labor-kanban.kanban-status';
    protected static string $scriptsView = 'service-labor-kanban.kanban-scripts';

    public bool $disableEditModal = true;


   /* protected function records(): \Illuminate\Support\Collection {
        return ServiceLabor::with('getOrderDetails', 'labor', 'service')->get();
    }*/

    protected function records(): \Illuminate\Support\Collection
    {     // Se nenhum filtro estiver definido, retorna todos os registros
        if (
            empty($this->selectedOrderNumber) &&
            empty($this->selectedDepartment) &&
            (empty($this->selectedOrderAndDepartment_order_number) || empty($this->selectedOrderAndDepartment_department))
        ) {
            return ServiceLabor::all();
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

        return ServiceLabor::with('labor', 'service')
            ->when($this->selectedOrderNumber, function ($query) {
                return $query->whereHas('service', function ($subQuery) {
                    $subQuery->where('order_number', $this->selectedOrderNumber);
                });
            })
            ->when($this->selectedDepartment, function ($query) {
                return $query->whereHas('service', function ($subQuery) {
                    $subQuery->where('department_id', $this->selectedDepartment);
                });
            })
            ->when($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department, function ($query) {
                return $query->whereHas('service', function ($subQuery) {
                    $subQuery->where('department_id', $this->selectedOrderAndDepartment_department)
                        ->where('order_number', $this->selectedOrderAndDepartment_order_number);
                });
            })
            ->get();
    }

}
