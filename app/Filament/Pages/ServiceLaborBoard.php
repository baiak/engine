<?php

namespace App\Filament\Pages;

use AllowDynamicProperties;
use App\Enums\TypeOfLaborStatus;
use App\Models\Department;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceLabor;
use App\Traits\HasHeaderActions;
use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

#[AllowDynamicProperties] class ServiceLaborBoard extends KanbanBoard
{
    use HasHeaderActions;
    public $selectedOrderNumber;
    public $selectedDepartment;

    public $selectedOrderAndDepartment;
    public $selectedOrderAndDepartment_order_number;
    public $selectedOrderAndDepartment_department;
    public $selectedClient;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filterForm')
            ->label('Filtrar')
            ->form([
                //filtrar por numero de ordem
                \Filament\Forms\Components\Select::make('selectedOrderNumber')
                    ->label('Número de Ordem')
                    ->options(
                        Service::pluck('order_number', 'order_number') // Más eficiente
                    )
                    ->placeholder('Selecione uma ordem')
                    ->searchable() // Permite buscar en la lista
                    ->reactive() // Hace que el campo sea reactivo
                    ->rules(['exists:services,order_number'])// Valida que el valor exista en la base de datos
                    ->live(),

                //filtrar por nome de cliente
                \Filament\Forms\Components\Select::make('selectedClientName')
                    ->label('Cliente')
                    ->options(
                        Order::with('client') // Carrega o relacionamento com Cliente e veiculo
                        ->get()
                            ->mapWithKeys(function ($order) {
                                // Formata a chave e o valor para o select
                                return [
                                    $order->client_id => "{$order->client->name}"
                                ];
                            })
                    )
                    ->searchable(),


                 //filtrar por departamento
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
            ])
            ->action(function(array $data){
                $this->selectedOrderNumber = $data['selectedOrderNumber'];
                $this->selectedClientName = $data['selectedClientName'];
                $this->selectedDepartment = $data['selectedDepartment'];
            }),
        ];
    }


    protected function getOrderNumber($id)
    {
        return ([Order::query()->where('id', $id)->get()]);
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
    {
        // Se nenhum filtro estiver definido, retorna todos os registros
        if (
            empty($this->selectedOrderNumber) &&
            empty($this->selectedDepartment)&&
            empty($this->selectedClientName)
        )
        {
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

        /*return ServiceLabor::with('labor', 'service')
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
            ->get();*/
        // Start with the base query
        $query = ServiceLabor::with('labor', 'service', 'order');

        // Filter by selected order number
        if ($this->selectedOrderNumber) {
            $query->whereHas('service', function ($subQuery) {
                $subQuery->where('order_number', $this->selectedOrderNumber);
            });
        }

        // Filter by selected client
        if ($this->selectedClient) {

            $query->whereHas('order', function ($subQuery) {
                $subQuery->where('client_id', $this->selectedClient);
            });
        }

        // Filter by selected department
        if ($this->selectedDepartment) {
            $query->whereHas('service', function ($subQuery) {
                $subQuery->where('department_id', $this->selectedDepartment);
            });
        }

        // Filter by selected order and department
        if ($this->selectedOrderAndDepartment_order_number && $this->selectedOrderAndDepartment_department) {
            $query->whereHas('service', function ($subQuery) {
                $subQuery->where('order_number', $this->selectedOrderAndDepartment_order_number)
                    ->where('department_id', $this->selectedOrderAndDepartment_department);
            });
        }

        // Execute the query and return the results
        return $query->get();

    }

}
