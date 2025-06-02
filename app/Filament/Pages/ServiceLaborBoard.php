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
// Removido: use App\Traits\HasHeaderActions; // HasHeaderActions não é um Trait padrão do Filament para Pages. InteractsWithActions já cobre as ações.
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

    // use HasHeaderActions; // Removido
    use InteractsWithActions;

    public $selectedOrderNumber;
    public $selectedDepartment;
    public $selectedDepartmentUser;

    protected static string $headerView = 'service-labor-kanban.kanban-header';
    protected static string $recordView = 'service-labor-kanban.kanban-record';
    // protected static string $scriptsView = 'service-labor-kanban.kanban-scripts'; // Se não estiver usando scripts customizados específicos, pode ser desnecessário.

    public ?ServiceLabor $selectedLaborForAction = null;
    public ?ServiceLabor $selectedLaborForObservation = null;

    protected function getStatusColumn(): string
    {
        return 'status';
    }

    #[On('status-changed')]
    public function statusChanged($recordId, $status, $fromOrderedIds = [], $toOrderedIds = []): void
    {
        // ... (código existente do statusChanged)
        $newStatus = $status;
        $fromOrderedIds = is_array($fromOrderedIds) ? $fromOrderedIds : [];
        $toOrderedIds = is_array($toOrderedIds) ? $toOrderedIds : [];
        $safeRecordId = (string) $recordId;
        $safeNewStatus = (string) $newStatus;
        $modelClass = static::$model;

        if (!class_exists($modelClass)) {
            Notification::make()->title('Erro de Configuração')->body("A classe do modelo '{$modelClass}' não foi encontrada.")->danger()->send();
            $this->dispatch('$refresh');
            return;
        }
        $record = $modelClass::find($safeRecordId);
        if (!$record) {
            Notification::make()->title('Erro')->body('Registro não encontrado.')->danger()->send();
            $this->dispatch('$refresh');
            return;
        }
        $statusColumn = $this->getStatusColumn();
        $originalStatus = $record->{$statusColumn};
        if (($originalStatus === TypeOfLaborStatus::cancelado->value && $safeNewStatus !== TypeOfLaborStatus::cancelado->value) || $originalStatus === TypeOfLaborStatus::finalizado->value) {
            Notification::make()->title('Ação Não Permitida')->body('Uma mão de obra cancelada ou finalizada não pode ter seu status alterado.')->warning()->send();
            $this->dispatch('$refresh'); // Mantém o refresh aqui pois é uma ação direta no card, não em modal de filtro
            return;
        }
        $record->update([$statusColumn => $safeNewStatus]);
        if (method_exists($this, 'onStatusChanged')) {
            $this->onStatusChanged($safeRecordId, $safeNewStatus, $fromOrderedIds, $toOrderedIds);
        }
    }

    #[On('openCancelLaborModal')]
    public function openCancelModal($recordId): void
    {
        // ... (código existente)
        if (empty($recordId)) {
            Notification::make()->title('Erro')->body('ID do registro não fornecido.')->danger()->send();
            return;
        }
        $this->selectedLaborForAction = ServiceLabor::find($recordId);
        if ($this->selectedLaborForAction) {
            if (in_array($this->selectedLaborForAction->status, [TypeOfLaborStatus::cancelado->value, TypeOfLaborStatus::finalizado->value])) {
                Notification::make()->title('Ação não permitida')->body('Esta mão de obra já está finalizada ou cancelada.')->warning()->send();
                return;
            }
            $this->mountAction('cancelLabor');
        } else {
            Notification::make()->title('Erro')->body('Mão de obra não encontrada para cancelamento.')->danger()->send();
            Log::error('Mão de obra não encontrada em openCancelModal.', ['recordId' => $recordId]);
        }
    }

    #[On('openAddObservationModal')]
    public function openAddObservationModal($recordId): void
    {
        // ... (código existente)
        if (empty($recordId)) {
            Notification::make()->title('Erro')->body('ID do registro não fornecido.')->danger()->send();
            return;
        }
        $this->selectedLaborForObservation = ServiceLabor::find($recordId);
        if ($this->selectedLaborForObservation) {
            $this->mountAction('addObservation');
        } else {
            Notification::make()->title('Erro')->body('Mão de obra não encontrada para adicionar observação.')->danger()->send();
            Log::error('Mão de obra não encontrada em openAddObservationModal.', ['recordId' => $recordId]);
        }
    }

    public function cancelLabor(): Action
    {
        // ... (código existente)
        return Action::make('cancelLabor')
            ->label('Cancelar Mão de Obra')->record(fn() => $this->selectedLaborForAction)->modalHeading('Cancelar Mão de Obra')
            ->form([
                Placeholder::make('cancellation_reason_title')->label('')->content('Motivo do Cancelamento'),
                RichEditor::make('cancellation_description')->label('Descrição Detalhada')->required()->columnSpanFull(),
            ])
            ->action(function (array $data, ServiceLabor $record) {
                Observation::create(['service_labor_id' => $record->id, 'order_id' => $record->order_id, 'service_id' => $record->service_id, 'user_id' => Auth::id(), 'title' => 'Mão de obra cancelada', 'description' => $data['cancellation_description'],]);
                $record->status = TypeOfLaborStatus::cancelado->value;
                $record->save();
                Notification::make()->title('Mão de Obra Cancelada')->body('A mão de obra foi marcada como cancelada.')->success()->send();
                $this->dispatch('$refresh'); // Refresh para atualizar o card após ação
            })
            ->modalSubmitActionLabel('Confirmar Cancelamento')->modalWidth('xl');
    }

    public function addObservation(): Action
    {
        // ... (código existente)
        return Action::make('addObservation')
            ->label('Adicionar Observação')->record(fn() => $this->selectedLaborForObservation)->modalHeading('Adicionar Nova Observação')
            ->form([
                TextInput::make('observation_title')->label('Título da Observação')->required()->maxLength(255),
                RichEditor::make('observation_description')->label('Descrição da Observação')->required()->columnSpanFull(),
            ])
            ->action(function (array $data, ServiceLabor $record) {
                Observation::create(['service_labor_id' => $record->id, 'order_id' => $record->order_id, 'service_id' => $record->service_id, 'user_id' => Auth::id(), 'title' => $data['observation_title'], 'description' => $data['observation_description'],]);
                Notification::make()->title('Observação Adicionada')->body('A observação foi adicionada com sucesso.')->success()->send();
                $this->dispatch('$refresh'); // Refresh para atualizar o card após ação
            })
            ->modalSubmitActionLabel('Salvar Observação')->modalWidth('xl');
    }

    // getFormActions é usado para ações que não são primariamente botões de cabeçalho,
    // mas podem ser montadas programaticamente (como modais de edição de records que você desabilitou).
    // Para cancelLabor e addObservation chamados por botões nos cards, eles são montados via $this->mountAction('actionName').
    // Se não houver outras "form actions" globais, este método pode não ser estritamente necessário aqui,
    // mas não causa problema.
    protected function getFormActions(): array
    {
        return [
            $this->cancelLabor(),
            $this->addObservation(),
        ];
    }

    // Este método é para definir ações que aparecem no cabeçalho da página/kanban.
    protected function getHeaderActions(): array
    {
        $actions = [];
        $userId = Auth::id();

        $actions[] =
            Action::make('clearFilters')
            ->label('Limpar Filtros')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->visible(fn() => !empty($this->selectedOrderNumber) || !empty($this->selectedDepartment) || !empty($this->selectedDepartmentUser))
            ->action(function () {
                $this->reset(['selectedOrderNumber', 'selectedDepartment', 'selectedDepartmentUser']);
                // O refresh do Kanban deve ser automático ao resetar as propriedades públicas via Livewire
                // ou o Filament pode lidar com isso ao fechar a "ação" (mesmo que não seja modal).
                // Se não atualizar, adicione $this->dispatch('$refresh');
                Notification::make()->title('Filtros Limpos')->success()->send();
            });

        $actions[] =
            Action::make('filterServiceLabor')
            ->label('Filtrar Mão de Obra')->icon('heroicon-o-funnel')
            ->form([
                Select::make('selectedOrderNumber')
                    ->label('Número de Ordem')
                    ->options(Order::select(['id', 'order_number', 'client_id', 'vehicle_id'])->with(['client:id,name', 'vehicle:id,factory,model'])->get()->mapWithKeys(fn($order) => [$order->id => $order->getFormattedTitleAttribute()]))
                    ->placeholder('Todas as Ordens')->searchable()->reactive()->live(), // live() é importante aqui
                Select::make('selectedDepartment')
                    ->label('Departamento')
                    ->options(Department::all()->pluck('title', 'id'))
                    ->placeholder('Todos os Departamentos')->searchable()->reactive()->live() // live() é importante aqui
                    ->afterStateUpdated(fn(callable $set) => $set('selectedDepartmentUser', null)),
                Select::make('selectedDepartmentUser')
                    ->label('Usuário do Departamento')
                    ->options(function (Get $get) {
                        $departmentId = $get('selectedDepartment');
                        if (!$departmentId) {
                            return [];
                        }
                        $department = Department::find($departmentId);
                        if (!$department) {
                            return [];
                        } // Checar se o departamento foi encontrado
                        return $department->users()
                            ->select('users.id', 'users.name') // Selecionar explicitamente de qual tabela virão as colunas
                            ->get() // Obter a coleção com os campos selecionados
                            ->pluck('name', 'id') // Agora o pluck é feito sobre uma coleção com 'id' não ambíguo
                            ->toArray();
                    })
                    ->placeholder('Todos os Usuários')->searchable()->reactive()->live() // live() é importante aqui
                    ->disabled(fn(Get $get) => !$get('selectedDepartment'))
            ])
            ->modalSubmitActionLabel('Aplicar Filtros') // Este é o botão DENTRO do modal
            // ->modalCancelAction(false) // Descomente se não quiser botão de cancelar no modal
            ->modalWidth('lg')
            ->action(function (array $data) {
                // As propriedades já foram atualizadas pelos campos live() e reactive().
                // A ação de submissão do modal aqui serve para "confirmar" e fechar o modal.
                // O Kanban irá re-renderizar automaticamente devido à mudança nas propriedades públicas.
                // Não é necessário $this->dispatch('$refresh'); aqui.
                $this->selectedOrderNumber = $data['selectedOrderNumber'] ?? null;
                $this->selectedDepartment = $data['selectedDepartment'] ?? null;
                $this->selectedDepartmentUser = $data['selectedDepartmentUser'] ?? null;
                // Se o refresh não ocorrer automaticamente após o modal fechar,
                // pode ser necessário um $this->dispatchSelf('processFilters'); e um listener para chamar o refresh.
                // Mas geralmente, a atualização de propriedades públicas já faz o Livewire recarregar.
            });

        // START: Badge Logic
        $pendingServicesCount = Service::where('status', TypeOfServiceStatus::pendente->value)
            ->where('user_id', $userId)
            ->count(); //
        // END: Badge Logic

        // A visibilidade do botão já considera se há serviços pendentes para o usuário.
        // A lógica do badge é um adicional visual.
        $canShowAddLaborButton = $pendingServicesCount > 0; // // Ou mantenha a lógica anterior se ela for diferente e mais complexa

        if ($canShowAddLaborButton) { //
            $addLaborAction = Action::make('addLaborToService')
                ->label('Adicionar Minha Mão de Obra')->modalHeading('Adicionar Mão de Obra ao Serviço')
                ->form([
                    Select::make('order_id')
                        ->label('Ordem de Serviço (Com seus serviços pendentes)')
                        ->options(function () use ($userId) {
                            return Order::where('status', TypeOfOrderStatus::aguardando_servicos->value)->whereHas('service', function ($query) use ($userId) { //
                                $query->where('status', TypeOfServiceStatus::pendente->value)->where('user_id', $userId); //
                            })->get()->mapWithKeys(function ($order) {
                                return [$order->id => $order->getFormattedTitleAttribute()];
                            });
                        })
                        ->live()->searchable()->required()->placeholder('Selecione uma Ordem de Serviço'),
                    Select::make('service_id')
                        ->label('Seu Serviço Pendente')
                        ->options(function (Get $get) use ($userId) {
                            $orderId = $get('order_id');
                            if (!$orderId) {
                                return [];
                            }

                            return Service::where('order_id', $orderId)
                                ->where('status', TypeOfServiceStatus::pendente->value) //
                                ->where('user_id', $userId) //
                                ->get()
                                ->mapWithKeys(function ($service) {
                                    $partDescription = $service->part ? $service->part->title : 'Peça não especificada';
                                    $label = "Serviço #{$service->id} ({$partDescription}) - {$service->description}";
                                    return [$service->id => strip_tags($label)];
                                });
                        })
                        ->disabled(fn(Get $get) => !$get('order_id'))->live()->searchable()->required()->placeholder('Selecione um Serviço'),
                    Select::make('labor_id')
                        ->label('Mão de Obra a Executar')->options(Labor::all()->pluck('title', 'id'))->searchable()->required()->placeholder('Selecione a Mão de Obra')
                        ->createOptionForm([
                            TextInput::make('title')->label('Título da Mão de Obra')->required()->maxLength(255),
                            Textarea::make('description')->label('Descrição (Opcional)')->rows(3),
                            Select::make('part_id')->label('Peça Associada (Opcional)')->options(Part::all()->pluck('title', 'id'))->searchable()->placeholder('Selecione uma peça, se aplicável'),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Labor::create(['title' => $data['title'], 'description' => $data['description'], 'part_id' => $data['part_id'] ?? null,])->id;
                        }),
                    RichEditor::make('description')->label('Observações para esta Mão de Obra no Serviço')
                        ->placeholder('Descreva detalhes adicionais sobre esta mão de obra')
                        ->columnSpanFull()
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'attachFiles',
                        ]),
                ])
                ->action(function (array $data) use ($userId) {
                    $service = Service::find($data['service_id']);
                    if (!$service || $service->user_id !== $userId || $service->status->value !== TypeOfServiceStatus::pendente->value) { //
                        Notification::make()->title('Erro de Validação')->body('O serviço selecionado não está mais disponível ou não lhe pertence.')->danger()->send();
                        return;
                    }
                    ServiceLabor::create(['order_id' => $service->order_id, 'service_id' => $data['service_id'], 'labor_id' => $data['labor_id'], 'user_id' => $userId, 'description' => $data['description'], 'status' => TypeOfLaborStatus::Aguardando_aprovacao->value, 'includedAt' => now(),]);
                    Notification::make()->title('Sucesso')->body('Mão de obra adicionada ao seu serviço pendente!')->success()->send();
                    // $this->dispatch('$refresh'); // Removido, refresh deve ser automático
                })->modalWidth('xl');

            if ($pendingServicesCount > 0) { //
                $addLaborAction->badge($pendingServicesCount); //

            }
            $actions[] = $addLaborAction;
        }
        return $actions;
    }



    protected static string $model = ServiceLabor::class;
    protected static string $statusEnum = TypeOfLaborStatus::class;

    protected function records(): \Illuminate\Support\Collection
    {
        // ... (método records como definido anteriormente, sem alterações)
        $query = ServiceLabor::with(['labor', 'service.order.client', 'service.part', 'service.department', 'order.client', 'service.user', 'user', 'observations',]);
        if ($this->selectedOrderNumber) {
            $query->where('order_id', $this->selectedOrderNumber);
        }
        if ($this->selectedDepartment) {
            $query->whereHas('service', function ($subQuery) {
                $subQuery->where('department_id', $this->selectedDepartment);
            });
        }
        if ($this->selectedDepartmentUser) {
            $query->where('user_id', $this->selectedDepartmentUser);
        }
        return $query->get();
    }
    public function getAdditionalData(): string
    {
        $activeFiltersMessages = [];

        if ($this->selectedOrderNumber) {
            // Assuming Order::find() might be useful if you need more than just the number
            // For now, using the number directly as in your original code.
            $activeFiltersMessages[] = "Exibindo serviços da ordem: <strong>{$this->selectedOrderNumber}</strong>";
        }

        if ($this->selectedDepartment) {
            $department = Department::find($this->selectedDepartment);
            if ($department) {
                $departmentMessage = "Departamento: <strong>{$department->title}</strong>";
                if ($this->selectedDepartmentUser) {
                    $user = User::find($this->selectedDepartmentUser);
                    if ($user) {
                        // You might want to include the avatar here if desired, similar to your original complex HTML
                        $userAvatarHtml = ''; // Placeholder for avatar logic if needed
                        if (function_exists('app') && app()->has('userAvatar')) {
                            $userAvatarHtml = app('userAvatar')($user->id) . ' ';
                        }
                        $departmentMessage .= " <div class='flex items-center gap-1 mt-1 text-xs'>{$userAvatarHtml}Filtrado por usuário: <strong>{$user->name}</strong></div>";
                    } else {
                        $departmentMessage .= " <span class='text-xs'>(Usuário do filtro não encontrado)</span>";
                    }
                }
                $activeFiltersMessages[] = $departmentMessage;
            } else {
                $activeFiltersMessages[] = "<span class='text-red-600 dark:text-red-400'>Departamento do filtro não encontrado</span>";
            }
        }
        // Add checks for other independent filters here if any

        if (empty($activeFiltersMessages)) {
            return '<div class="inline-block text-sm text-gray-600 dark:text-gray-300">Exibindo todas as mãos de obra</div>';
        }

        // Construct the HTML to display all active filters
        $html = '<div class="inline-block rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-3 text-sm text-gray-800 dark:text-gray-200 space-y-2 shadow-md">';
        foreach ($activeFiltersMessages as $message) {
            $html .= "<div>{$message}</div>"; // Each filter on a new line within the styled box
        }
        $html .= '</div>';

        return $html;
    }
}
