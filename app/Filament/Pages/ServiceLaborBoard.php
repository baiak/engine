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

    // Add a listener for the event dispatched by the button
    /*protected function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            'openCancelLaborModal' => 'mountCancelLaborAction',
        ]);
    }*/

    // metodo para abrir o modal de cancelamento
    #[On('openCancelLaborModal')]
    public function openCancelModal($recordId): void
    {
        //Log::info('ID recebido em openCancelModal:', ['recordId' => $recordId]);

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
    // Define the cancel action
    public function cancelLabor(): Action
    {
        return Action::make('cancelLabor')
            ->label('Cancelar Mão de Obra')
            ->record(fn() => $this->selectedLaborForAction) // Passa o registro selecionado para o modal
            ->modalHeading('Cancelar Mão de Obra')
            ->form([
                Placeholder::make('cancellation_reason_title')
                    ->label('') // sem título
                    ->content('Motivo do Cancelamento'), 
                RichEditor::make('cancellation_description')
                    ->label('Descrição Detalhada')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, ServiceLabor $record) { // $record é passado automaticamente
                // 1. criar uma observação
                Observation::create([
                    'service_labor_id' => $record->id,
                    'order_id' => $record->order_id,     
                    'service_id' => $record->service_id,   
                    'user_id' => Auth::id(),
                    'title' => 'Mão de obra cancelada', 
                    'description' => $data['cancellation_description'],
                ]);

                // 2. atualizar o status da mão de obra
                
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

    protected function getFormActions(): array
    {
        return [
            $this->cancelLabor(),
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
                Select::make('selectedOrderNumber') // Mudado para selectedOrderId para clareza, mas o nome do campo permanece selectedOrderNumber para compatibilidade
                    ->label('Número de Ordem')
                    ->options(
                        // Mantém a busca por ID, mas exibe o número da ordem e outros detalhes
                        Order::select(['id', 'order_number', 'client_id', 'vehicle_id'])
                            ->with(['client:id,name', 'vehicle:id,factory,model']) // Otimizar carregamento
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

        // Condição para exibir o botão "Adicionar Mão de Obra ao Serviço"

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
                ->action(function (array $data) use ($userId) { // Passar $userId para a action
                    $service = Service::find($data['service_id']);

                    // Validação adicional para garantir que o serviço pertence ao usuário logado e está pendente
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
                        'user_id' => $userId, // Usuário logado (quem está adicionando a mão de obra)
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

    // ... (restante da classe ServiceLaborBoard como antes) ...
    // Certifique-se de que o método records() também respeite a lógica de visualização de dados,
    // embora o Kanban em si mostre ServiceLabors, a criação é que está sendo refinada aqui.
    // A lógica de filtros globais no records() deve permanecer como está ou ser ajustada
    // conforme a necessidade geral de visualização do Kanban.

    protected function records(): \Illuminate\Support\Collection
    {
        $userId = Auth::id();
        $query = ServiceLabor::with([
            'labor',
            'service.order.client',
            'service.part',
            'service.department',
            'order.client',
            'service.user',
            'observations',
        ]);

        $hasFilters = !empty($this->selectedOrderNumber) ||
            !empty($this->selectedClient) ||
            !empty($this->selectedDepartment) ||
            (!empty($this->selectedOrderAndDepartment_order_number) && !empty($this->selectedOrderAndDepartment_department));

        if (!$hasFilters) {
            // Se você quiser que o Kanban principal também filtre por padrão para o usuário logado (ex: apenas suas mãos de obra)
            // $query->where('user_id', $userId); // Ou se ServiceLabor tem user_id referente ao executor
            // ou $query->whereHas('service', fn($q) => $q->where('user_id', $userId)); // Se a lógica for mostrar SL de serviços do usuário
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
