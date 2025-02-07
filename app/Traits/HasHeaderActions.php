<?php
namespace App\Traits;

use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;

trait HasHeaderActions
{
protected function getHeaderActions(): array
{
return [
Action::make('edit'),
$this->createFilterAction('filterByOrderNumber', 'Ordem de Serviço', 'selectedOrderNumber', $this->getOrderNumbers()),
$this->createFilterAction('filterByClientName', 'Cliente', 'selectedClientName', $this->getClients()),
$this->createFilterAction('filterByDepartment', 'Departamento', 'selectedDepartment', $this->getDepartments()),
$this->createCombinedFilterAction(),
];
}

private function createFilterAction(string $name, string $label, string $field, Collection $options): Action
{
return Action::make($name)
->label("Filtrar por $label")
->form([
Select::make($field)
->label($label)
->options($options)
->placeholder("Selecione um(a) $label")
->searchable()
->required()
->live(),
])
->action(fn (array $data) => $this->resetFilters([$field => $data[$field]]));
}

private function createCombinedFilterAction(): Action
{
return Action::make('filterByOrderAndDepartment')
->label('Filtrar por Ordem e Departamento')
->form([
Select::make('selectedOrderNumber')
->options($this->getOrderNumbers())
->placeholder('Selecione uma Ordem')
->searchable()
->live(),

Select::make('selectedDepartment')
->options($this->getDepartments())
->placeholder('Selecione um Departamento')
->searchable()
->live(),
])
->action(fn (array $data) => $this->resetFilters([
'selectedOrderNumber' => $data['selectedOrderNumber'],
'selectedDepartment' => $data['selectedDepartment'],
]));
}

private function getOrderNumbers(): Collection
{
return \App\Models\Service::pluck('order_number', 'order_number');
}

private function getClients(): Collection
{
return \App\Models\Order::with('client', 'vehicle')->get()
->mapWithKeys(fn ($order) => [
$order->order_number => "Ordem: {$order->order_number} - {$order->client->name} - {$order->vehicle->factory}/{$order->vehicle->model}"
]);
}

private function getDepartments(): Collection
{
return \App\Models\Department::with('user')->get()
->mapWithKeys(fn ($department) => [
$department->id => "{$department->title} - {$department->user->name}"
]);
}

protected function resetFilters(array $filters): void
{
foreach ($filters as $key => $value) {
$this->$key = $value;
}
}
}
