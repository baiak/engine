<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Service;
use App\Models\Part;
use App\Models\Department;
use App\Models\User;
use App\Models\Order;
use App\Enums\TypeOfServiceStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AddServiceModal extends Component
{
    public bool $showModal = false;
    public ?int $orderId = null;
    public ?string $orderNumber = null;

    // Propriedades para os campos do formulário
    public $serviceDescription = '';
    public $partId = null;
    public $departmentId = null;
    public $deadline = '';
    public $status = '';
    public $userId = null;

    // Coleções para preencher os selects
    public $parts = [];
    public $departments = [];
    public $users = [];
    public $serviceStatuses = [];

    public function mount()
    {
        // Carrega os dados dos selects assim que o componente é montado
        $this->loadSelectData();
    }

    #[On('openAddServiceModal')]
    public function openModal(int $orderId)
    {
        $this->resetInputFields(); // Limpa os campos antes de abrir
        $this->orderId = $orderId;
        
        // Busca informações da ordem para pré-preencher campos
        $order = Order::find($orderId);
        if ($order) {
            $this->orderNumber = $order->order_number;
            $this->deadline = $order->deadline ?? now()->addDays(7)->format('Y-m-d');
            
            // Pré-seleciona o usuário logado, se não for definido outro
            $this->userId = Auth::id();
            
            // Define um status padrão, se disponível
            if (!empty($this->serviceStatuses)) {
                $this->status = array_key_first($this->serviceStatuses);
            }
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->orderId = null;
        $this->orderNumber = null;
        $this->serviceDescription = '';
        $this->partId = null;
        $this->departmentId = null;
        $this->deadline = '';
        $this->status = '';
        $this->userId = null;
        $this->resetErrorBag(); // Limpa erros de validação
    }

    // Carrega dados necessários para os campos <select>
    public function loadSelectData()
    {
        // Carrega as peças ordenadas por título
        $this->parts = Part::orderBy('title')->get();
        
        // Carrega departamentos ordenados por título
        $this->departments = Department::orderBy('title')->get();
        
        // Carrega usuários ordenados por nome
        $this->users = User::orderBy('name')->get();

        // Carrega os status do Enum TypeOfServiceStatus
        if (class_exists(TypeOfServiceStatus::class)) {
            $this->serviceStatuses = collect(TypeOfServiceStatus::cases())->mapWithKeys(function ($status) {
                return [$status->value => ucfirst(str_replace('_', ' ', $status->name))];
            })->all();
        } else {
            // Status manuais caso não use Enum
            $this->serviceStatuses = [
                'pendente' => 'Pendente',
                'aprovado' => 'Aprovado',
                'em_andamento' => 'Em Andamento',
                'concluido' => 'Concluído',
                'cancelado' => 'Cancelado',
            ];
        }
    }

    protected function rules(): array
    {
        return [
            'serviceDescription' => 'required|string|min:5',
            'partId' => 'nullable|integer|exists:parts,id',
            'departmentId' => 'required|integer|exists:departments,id',
            'deadline' => 'required|date',
            'status' => [
                'required',
                'string',
                Rule::in(array_keys($this->serviceStatuses))
            ],
            'userId' => 'required|integer|exists:users,id',
        ];
    }

    protected $messages = [
        'serviceDescription.required' => 'A descrição do serviço é obrigatória.',
        'serviceDescription.min' => 'A descrição deve ter pelo menos 5 caracteres.',
        'departmentId.required' => 'O departamento é obrigatório.',
        'deadline.required' => 'O prazo é obrigatório.',
        'deadline.date' => 'O prazo deve ser uma data válida.',
        'status.required' => 'O status é obrigatório.',
        'status.in' => 'O status selecionado é inválido.',
        'userId.required' => 'O usuário responsável é obrigatório.',
        'partId.exists' => 'A peça selecionada é inválida.',
        'departmentId.exists' => 'O departamento selecionado é inválido.',
        'userId.exists' => 'O usuário selecionado é inválido.',
    ];

    public function saveService()
    {
        $this->validate(); // Valida os dados usando as rules() e messages()

        try {
            // Cria o novo serviço
            $service = Service::create([
                'order_id' => $this->orderId,
                'description' => $this->serviceDescription,
                'part_id' => $this->partId,
                'department_id' => $this->departmentId,
                'deadline' => $this->deadline,
                'status' => $this->status,
                'user_id' => $this->userId,
                // Busca o order_number da ordem relacionada, se necessário
                'order_number' => $this->orderNumber ?? Order::find($this->orderId)->order_number,
            ]);

            $this->closeModal();

            // Dispara um evento para atualizar outros componentes
            $this->dispatch('serviceAdded', [
                'orderId' => $this->orderId,
                'serviceId' => $service->id
            ]);

            // Notificação de sucesso
            $this->dispatch('notify', [
                'message' => 'Serviço adicionado com sucesso!', 
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            // Tratamento de erro
            Log::error('Erro ao salvar serviço: ' . $e->getMessage());
            
            $this->dispatch('notify', [
                'message' => 'Erro ao salvar o serviço: ' . $e->getMessage(), 
                'type' => 'error'
            ]);
        }
    }

    // Método usado quando o departamento é alterado para filtrar usuários relevantes (opcional)
    public function updatedDepartmentId($value)
    {
        // Se quiser filtrar os usuários com base no departamento selecionado
        if ($value) {
            $department = Department::find($value);
            if ($department && method_exists($department, 'activeUsers')) {
                $this->users = $department->activeUsers()->get();
            } else {
                // Carrega todos os usuários se o método não existir
                $this->users = User::orderBy('name')->get();
            }
        } else {
            // Se nenhum departamento for selecionado, carrega todos os usuários
            $this->users = User::orderBy('name')->get();
        }
    }

    public function render()
    {
        // Garante que os dados dos selects estão carregados antes de renderizar
        if (empty($this->parts) || empty($this->departments) || empty($this->users) || empty($this->serviceStatuses)) {
            $this->loadSelectData();
        }
        
        return view('livewire.add-service-modal');
    }
}