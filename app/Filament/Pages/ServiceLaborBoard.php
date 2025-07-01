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

#[AllowDynamicProperties]
class ServiceLaborBoard extends KanbanBoard implements HasActions
{
    public bool $disableEditModal = true;

    use InteractsWithActions;

    public $selectedOrderNumber;
    public $selectedDepartment;
    public $selectedDepartmentUser;

    protected static ?string $title = 'Quadro de Mão de Obra';
    protected static ?string $navigationGroup = 'Quadros Kanban';
    protected static ?string $navigationLabel = 'Quadro de Mão de Obra';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-cog';


    protected static string $headerView = 'service-labor-kanban.kanban-header';
    protected static string $recordView = 'service-labor-kanban.kanban-record';


    public ?ServiceLabor $selectedLaborForAction = null;
    public ?ServiceLabor $selectedLaborForObservation = null;

    protected function getStatusColumn(): string
    {
        return 'status';
    }

    #[On('status-changed')]
    public function statusChanged($recordId, $status, $fromOrderedIds = [], $toOrderedIds = []): void
    {

        $newStatus = $status;
        $fromOrderedIds = is_array($fromOrderedIds) ? $fromOrderedIds : [];
        $toOrderedIds = is_array($toOrderedIds) ? $toOrderedIds : [];
        $safeRecordId = (string)$recordId;
        $safeNewStatus = (string)$newStatus;
        $modelClass = static::$model;

        if (!class_exists($modelClass)) {
            Notification::make()->title('Erro de Configuração')
                ->body("A classe do modelo '{$modelClass}' não foi encontrada.")->danger()->send();
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
        $lockedStatuses = [
            TypeOfLaborStatus::cancelado->value,
            TypeOfLaborStatus::finalizado->value
        ];
        if (in_array($originalStatus, $lockedStatuses, true)) { // Added missing parenthesis
            Notification::make()->title('Ação Não Permitida')
                ->body('Uma mão de obra cancelada ou finalizada não pode ter seu status alterado.')->warning()->send();
            $this->dispatch('$refresh');
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
        return Action::make('cancelLabor')
            ->label('Cancelar Mão de Obra')
            ->record(fn() => $this->selectedLaborForAction)
            ->modalHeading('Cancelar Mão de Obra')
            ->form([
                Placeholder::make('cancellation_reason_title')->label('')->content('Motivo do Cancelamento'),
                RichEditor::make('cancellation_description')->label('Descrição Detalhada')->required()->columnSpanFull(),
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
                Notification::make()->title('Mão de Obra Cancelada')->body('A mão de obra foi marcada como cancelada.')->success()->send();
                $this->dispatch('$refresh'); // Refresh para atualizar o card após ação
            })
            ->modalSubmitActionLabel('Confirmar Cancelamento')
            ->modalWidth('xl');
    }

    public function addObservation(): Action
    {
        return Action::make('addObservation')
            ->label('Adicionar Observação')
            ->record(fn() => $this->selectedLaborForObservation)
            ->modalHeading('Adicionar Nova Observação')
            ->form([
                TextInput::make('observation_title')->label('Título da Observação')->required()->maxLength(255),
                RichEditor::make('observation_description')->label('Descrição da Observação')->required()->columnSpanFull(),
            ])
            ->action(function (array $data, ServiceLabor $record) {
                Observation::create([
                    'service_labor_id' => $record->id,
                    'order_id' => $record->order_id,
                    'service_id' => $record->service_id,
                    'user_id' => Auth::id(),
                    'title' => $data['observation_title'],
                    'description' => $data['observation_description'],
                ]);
                Notification::make()->title('Observação Adicionada')->body('A observação foi adicionada com sucesso.')->success()->send();
                $this->dispatch('$refresh'); // Refresh para atualizar o card após ação
            })
            ->modalSubmitActionLabel('Salvar Observação')
            ->modalWidth('xl');
    }

    protected function getFormActions(): array
    {
        return [
            $this->cancelLabor(),
            $this->addObservation(),
        ];
    }

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
                    Notification::make()->title('Filtros Limpos')->success()->send();
                });

        $actions[] =
            Action::make('filterServiceLabor')
                ->label('Filtrar Mão de Obra')
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('selectedOrderNumber')
                        ->label('Número de Ordem')
                        ->options(Order::select(['id', 'order_number', 'client_id', 'vehicle_id'])->with(['client:id,name', 'vehicle:id,factory,model'])->get()->mapWithKeys(fn($order) => [$order->id => $order->getFormattedTitleAttribute()]))
                        ->placeholder('Todas as Ordens')
                        ->searchable()
                        ->reactive()
                        ->live(),
                    Select::make('selectedDepartment')
                        ->label('Departamento')
                        ->options(Department::all()->pluck('title', 'id'))
                        ->placeholder('Todos os Departamentos')
                        ->searchable()
                        ->reactive()
                        ->live()
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
                            }
                            return $department->users()
                                ->select('users.id', 'users.name')
                                ->get()
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->placeholder('Todos os Usuários')
                        ->searchable()
                        ->reactive()
                        ->live()
                        ->disabled(fn(Get $get) => !$get('selectedDepartment')),
                ])
                ->modalSubmitActionLabel('Aplicar Filtros')
                ->modalWidth('lg')
                ->action(function (array $data) {
                    $this->selectedOrderNumber = $data['selectedOrderNumber'] ?? null;
                    $this->selectedDepartment = $data['selectedDepartment'] ?? null;
                    $this->selectedDepartmentUser = $data['selectedDepartmentUser'] ?? null;
                });

        $pendingServicesCount = Service::where('status', TypeOfServiceStatus::pendente->value)
            ->where('user_id', $userId)
            ->count();

        $canShowAddLaborButton = $pendingServicesCount > 0;

        if ($canShowAddLaborButton) {
            $addLaborAction = Action::make('addLaborToService')
                ->label('Adicionar Minha Mão de Obra')
                ->modalHeading('Adicionar Mão de Obra ao Serviço')
                ->form([
                    Select::make('order_id')
                        ->label('Ordem de Serviço (Com seus serviços pendentes)')
                        ->options(function () use ($userId) {
                            return Order::where('status', TypeOfOrderStatus::aguardando_servicos->value)
                                ->whereHas('service', function ($query) use ($userId) {
                                    $query->where('status', TypeOfServiceStatus::pendente->value)->where('user_id', $userId);
                                })->get()->mapWithKeys(function ($order) {
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
                                    $label = "Serviço #{$service->id} ({$partDescription}) - {$service->description}";
                                    return [$service->id => strip_tags($label)];
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
                            TextInput::make('title')->label('Título da Mão de Obra')->required()->maxLength(255),
                            Textarea::make('description')->label('Descrição (Opcional)')->rows(3),
                            Select::make('part_id')->label('Peça Associada (Opcional)')->options(Part::all()->pluck('title', 'id'))->searchable()->placeholder('Selecione uma peça, se aplicável'),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Labor::create([
                                'title' => $data['title'],
                                'description' => $data['description'],
                                'part_id' => $data['part_id'] ?? null,
                            ])->id;
                        }),
                    RichEditor::make('description')
                        ->label('Observações para esta Mão de Obra no Serviço')
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
                    if (!$service || $service->user_id !== $userId || $service->status->value !== TypeOfServiceStatus::pendente->value) {
                        Notification::make()->title('Erro de Validação')->body('O serviço selecionado não está mais disponível ou não lhe pertence.')->danger()->send();
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
                    Notification::make()->title('Sucesso')->body('Mão de obra adicionada ao seu serviço pendente!')->success()->send();
                })->modalWidth('xl');

            if ($pendingServicesCount > 0) {
                $addLaborAction->badge($pendingServicesCount);
            }
            $actions[] = $addLaborAction;
        }
        return $actions;
    }

    protected static string $model = ServiceLabor::class;
    protected static string $statusEnum = TypeOfLaborStatus::class;

    protected function records(): \Illuminate\Support\Collection
    {
        $query = ServiceLabor::with(['labor', 'service.order.client', 'service.part', 'service.department', 'order.client', 'service.user', 'user', 'observations']);
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
            $order = Order::find($this->selectedOrderNumber);
            if ($order) {
                $activeFiltersMessages[] = "Exibindo serviços da ordem: <strong>{$order->order_number}</strong>";
            } else {
                $activeFiltersMessages[] = "<span class='text-red-600 dark:text-red-400'>Número da Ordem do filtro não encontrado</span>";
            }
        }

        if ($this->selectedDepartment) {
            $department = Department::find($this->selectedDepartment);
            if ($department) {
                $departmentMessage = "Departamento: <strong>{$department->title}</strong>";
                if ($this->selectedDepartmentUser) {
                    $user = User::find($this->selectedDepartmentUser);
                    if ($user) {
                        $userAvatarHtml = '';
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

        if (empty($activeFiltersMessages)) {
            return '<div class="inline-block text-sm text-gray-600 dark:text-gray-300">Exibindo todas as mãos de obra</div>';
        }

        $html = '<div class="inline-block rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-3 text-sm text-gray-800 dark:text-gray-200 space-y-2 shadow-md">';
        foreach ($activeFiltersMessages as $message) {
            $html .= "<div>{$message}</div>";
        }
        $html .= '</div>';

        return $html;
    }
}