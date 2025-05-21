<?php

namespace App\Filament\Pages;

use App\Enums\TypeOforderStatus;
use App\Enums\TypeOfLaborStatus;
use App\Enums\TypeOfServiceStatus;
use App\Models\Client;
use App\Models\Department;
use App\Models\Order;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceAuditLog;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\ServiceLabor;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On; // Import the correct namespace for the On attribute

class OrdersKanbanBoard extends KanbanBoard
{
    public bool $disableEditModal = true;

    protected static string $model = Order::class;
    protected static string $statusEnum = TypeOforderStatus::class;


    protected static string $headerView = 'order-kanban.kanban-header';
    protected static string $recordView = 'order-kanban.kanban-record';
    protected static string $scriptsView = 'order-kanban.kanban-scripts';

    public function getServiceLaborsForOrder(Order $order)
    {
        return $order->allServiceLabors();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createOrder')
                ->label('Nova Ordem')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('order_number')
                        ->required()
                        ->label('Número da Ordem')
                        ->placeholder('Digite o número da ordem'),

                    Select::make('client_id')
                        ->label('Cliente')
                        ->options(Client::all()->pluck('name', 'id'))
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->label('Nome do Cliente')
                                ->placeholder('Digite o nome do cliente'),

                            TextInput::make('city')
                                ->label('Cidade')
                                ->placeholder('Digite a cidade do cliente'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            return Client::create($data)->getKey();
                        })
                        ->required()
                        ->searchable(),

                    Select::make('vehicle_id')
                        ->label('Veículo')
                        ->options(Vehicle::all()->pluck('title', 'id'))
                        ->createOptionForm([
                            TextInput::make('factory')
                                ->required()
                                ->label('Fabricante')
                                ->placeholder('Ex: Ford, Fiat, etc'),

                            TextInput::make('model')
                                ->required()
                                ->label('Modelo')
                                ->placeholder('Ex: Fiesta, Palio, etc'),

                            TextInput::make('year')
                                ->label('Ano')
                                ->placeholder('Ex: 2023'),

                            TextInput::make('motor')
                                ->label('Motor')
                                ->placeholder('Ex: 1.0, 2.0, etc'),

                            Select::make('fuel')
                                ->label('Combustível')
                                ->options([
                                    'flex' => 'Flex',
                                    'gasoline' => 'Gasolina',
                                    'ethanol' => 'Etanol',
                                    'diesel' => 'Diesel',
                                    'electric' => 'Elétrico',
                                    'hybrid' => 'Híbrido'
                                ]),

                            TextInput::make('infos')
                                ->label('Informações Adicionais')
                                ->placeholder('Informações extras sobre o veículo')
                        ])
                        ->createOptionUsing(function (array $data) {
                            return Vehicle::create($data)->getKey();
                        })
                        ->required()
                        ->searchable(),

                    DatePicker::make('deadline')
                        ->label('Prazo de Entrega')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    // Create new order with form data
                    $order = Order::create([
                        'user_id' => Auth::id(), // Current authenticated user
                        'client_id' => $data['client_id'],
                        'vehicle_id' => $data['vehicle_id'],
                        'order_number' => $data['order_number'],
                        'deadline' => $data['deadline'],
                        'status' => TypeOforderStatus::aguardando_servicos->value // Default status
                    ]);

                    // Show success notification
                    Notification::make()
                        ->title('Ordem criada com sucesso!')
                        ->body('Ordem #' . $order->order_number . ' foi adicionada ao quadro.')
                        ->success()
                        ->send();

                    // Refresh the page to show the new order
                    $this->redirect(OrdersKanbanBoard::getUrl());
                }),
                
            Action::make('addService')
                ->label('Novo Serviço')
                ->icon('heroicon-o-wrench')
                ->visible(function (): bool {
                    // Verifica se existe alguma ordem com o status 'aguardando_servicos'
                    return Order::where('status', TypeOforderStatus::aguardando_servicos->value)->exists();
                })
                ->form([
                    Select::make('order_id')
                        ->label('Selecione a Ordem')
                        ->options(function() {
                            // 1. Busca as ordens filtradas com as relações necessárias para o acessor
                            $orders = Order::with(['client', 'vehicle']) // Carrega as relações
                                ->where('status', TypeOforderStatus::aguardando_servicos->value)
                                ->get();

                            // 2. Mapeia para o formato [id => formatted_title]
                            return $orders->mapWithKeys(function ($order) {
                                return [$order->id => $order->formatted_title]; // Usa o acessor aqui
                            });
                        })
                        ->required()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Limpa os campos dependentes quando a ordem muda
                            $set('part_id', null);
                            $set('department_id', null);
                        }),
                        
                    Select::make('part_id')
                        ->label('Peça/Componente')
                        ->options(function (callable $get) {
                            if (!$get('order_id')) return [];
                            
                            $order = Order::find($get('order_id'));
                            if (!$order) return [];
                            
                            $vehicleId = $order->vehicle_id;
                            
                            // Filtra peças pelo veículo ou retorna todas se não houver filtro específico
                            return Part::where(function ($query) use ($vehicleId) {
                                $query->where('vehicle_id', $vehicleId)
                                    ->orWhereNull('vehicle_id');
                            })->pluck('title', 'id');
                        })
                        ->createOptionForm([
                            TextInput::make('title')
                                ->required()
                                ->label('Nome da Peça')
                                ->placeholder('Ex: Motor, Suspensão, etc'),
                                
                            TextInput::make('parameters')
                                ->label('Parâmetros')
                                ->placeholder('Informações técnicas sobre a peça'),
                                
                         
                        ])
                        ->createOptionUsing(function (array $data, callable $get) {
                            $order = Order::find($get('order_id'));
                            if (!$order) return null;
                            
                            $data['vehicle_id'] = $order->vehicle_id;
                            return Part::create($data)->getKey();
                        })
                        ->required()
                        ->searchable(),
                        
                    Select::make('department_id')
                        ->label('Departamento')
                        ->options(Department::all()->pluck('title', 'id'))
                        ->required()
                        ->searchable(),
                        
                    Select::make('user_id')
                        ->label('Responsável')
                        ->options(function (callable $get) {
                            if (!$get('department_id')) return User::all()->pluck('name', 'id');
                            
                            // Filtra usuários pelo departamento selecionado
                            return Department::find($get('department_id'))
                                ->activeUsers()
                                ->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),
                        
                    DatePicker::make('deadline')
                        ->label('Prazo de Entrega')
                        ->required(),
                        
                    RichEditor::make('description')
                        ->label('Descrição do Serviço')
                        ->required()
                        ->placeholder('Detalhes do serviço a ser realizado'),

    
                ])
                ->action(function (array $data): void {
                    // Inicia uma transação para garantir integridade dos dados
                    DB::beginTransaction();
                    
                    try {
                        // Cria o serviço
                        $service = Service::create([
                            'order_id' => $data['order_id'],
                            'part_id' => $data['part_id'],
                            'department_id' => $data['department_id'],
                            'deadline' => $data['deadline'],
                            'status' => TypeOfServiceStatus::pendente->value,
                            'description' => $data['description'],
                            'user_id' => $data['user_id'],
                        ]);
                        
   
                        
                        DB::commit();
                        
                        // Mostra notificação de sucesso
                        Notification::make()
                            ->title('Serviço adicionado com sucesso!')
                            ->body('O serviço foi adicionado à ordem #' . Order::find($data['order_id'])->order_number)
                            ->success()
                            ->send();
                            
                        // Recarrega a página para mostrar a atualização
                        $this->redirect(OrdersKanbanBoard::getUrl());
                    } catch (\Exception $e) {
                        DB::rollBack();
                        
                        // Mostra notificação de erro
                        Notification::make()
                            ->title('Erro ao adicionar serviço')
                            ->body('Ocorreu um erro: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            /*  public function onStatusChanged(int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Order::find($recordId)->update(['status' => $status]);
        Order::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int $recordId, string $status, array $orderedIds): void
    {
        Order::setNewOrder($orderedIds);
    }*/
        ];
    }
    public function updateLaborStatus($serviceLaborId, $newStatus, $recordId)
    {
        $serviceLabor = ServiceLabor::find($serviceLaborId);

        if ($serviceLabor) {
            try {
                $statusEnum = TypeOfLaborStatus::from($newStatus); // Valida se o status existe no Enum
                $serviceLabor->status = $statusEnum;
                $serviceLabor->save();

                // Dispara um evento para que o Alpine possa atualizar a interface se necessário,
                // ou para que o próprio Livewire possa re-renderizar partes específicas.
                // O $this->dispatch('laborStatusUpdated', recordId: $serviceLabor->service_id) pode ser usado
                // se a atualização do status da mão de obra deve refletir no card de serviço (Service).
                // Se você só quer que o status no item da lista seja atualizado visualmente pelo Alpine,
                // a chamada $wire já faz isso no Blade.

                // Se a mudança no status da mão de obra pode alterar o status do serviço principal,
                // você pode recalcular o status do serviço aqui e então:
                // $this->dispatch('refreshKanbanRecord'); // Atualiza todo o card.
                // Ou, se tiver um método específico para atualizar apenas o record:
                // $this->dispatch('recordUpdated', id: $recordId);

                // Para este caso, vamos assumir que queremos apenas notificar que foi atualizado,
                // e o Alpine já cuidou da parte visual imediata do status da mão de obra.
                // Se o card principal (Service) precisa ser re-renderizado devido a essa mudança:
                $this->dispatch('laborStatusUpdated', recordId: $recordId)->self(); // Notifica o próprio componente para se atualizar
                $this->dispatch('notify', message: 'Status da mão de obra atualizado com sucesso!', type: 'success');


            } catch (\ValueError $e) {
                // Lidar com o caso de um valor de status inválido
                $this->dispatch('notify', message: 'Erro: Status inválido selecionado.', type: 'danger');
            }
        } else {
            $this->dispatch('notify', message: 'Erro: Mão de obra não encontrada.', type: 'danger');
        }
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