<?php

namespace App\Livewire;

use App\Models\LaborImpediment;
use App\Models\User;
use App\Enums\TypeOfLaborImpedimentStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class LaborImpedimentLogsManager extends Component
{
    public LaborImpediment $impediment;
    public array $logEntries = [];
    public string $newObservation = '';
    public ?string $newSelectedStatus = null;

    protected function rules(): array
    {
        return [
            'newObservation' => 'required|string|min:3',
            'newSelectedStatus' => 'required|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'newObservation.required' => 'A observação é obrigatória.',
            'newObservation.min' => 'A observação deve ter pelo menos 3 caracteres.',
            'newSelectedStatus.required' => 'O novo status é obrigatório.',
        ];
    }

    public function mount(LaborImpediment $impediment)
    {
        $this->impediment = $impediment;
        $this->loadLogEntries();
        if ($this->impediment->status) {
            $this->newSelectedStatus = $this->impediment->status->value;
        }
    }
    protected function loadLogEntries()
    {
        // Não use o método estático, acesse diretamente a propriedade
        $rawLogs = $this->impediment->logs ?? [];
        
        // Se por algum motivo ainda vier como string JSON, decodifique
        if (is_string($rawLogs)) {
            $rawLogs = json_decode($rawLogs, true) ?? [];
        }
        
        // Garante que sempre será um array
        if (!is_array($rawLogs)) {
            $rawLogs = [];
        }
        
        $this->logEntries = collect($rawLogs)->map(function ($log) {
            $user = User::find($log['user_id'] ?? null);
            $log['user_name'] = $user ? $user->name : 'Usuário Desconhecido';
            return $log;
        })->sortByDesc('date')->values()->all(); // Ordena por data desc
    }

public function addLogEntry()
{
    $this->validate();

    $statusEnumCase = null;
    try {
        $statusEnumCase = TypeOfLaborImpedimentStatus::from($this->newSelectedStatus);
    } catch (\ValueError $e) {
        $this->addError('newSelectedStatus', 'O status selecionado é inválido.');
        return;
    }

    $newLog = [
        'date' => now()->format('d-m-Y - H:i:s'),
        'user_id' => Auth::id(),
        'observation' => $this->newObservation,
        'selected_status' => $statusEnumCase->value,
    ];

    // Pega os logs atuais - o Laravel já fez o cast para array
    $currentLogs = $this->impediment->logs;
    
    // Se vier null, inicializa como array vazio
    if (!is_array($currentLogs)) {
        $currentLogs = [];
    }
    
    // Adiciona o novo log
    $currentLogs[] = $newLog;

    // Salva diretamente - o Laravel fará o cast para JSON automaticamente
    $this->impediment->logs = $currentLogs;
    $this->impediment->status = $statusEnumCase;
    $this->impediment->save();

    // Recarrega os logs
    $this->loadLogEntries();
    
    // Limpa o formulário
    $this->newObservation = '';

    Notification::make()
        ->title('Log adicionado e status atualizado com sucesso!')
        ->success()
        ->sendToDatabase(Auth::user())
        ->broadcast(User::find($this->impediment->complained_id));

    $this->dispatch('logAdded');
}

    // CORREÇÃO: Método para retornar as opções de status
    public function getStatusOptions()
    {
        return TypeOfLaborImpedimentStatus::cases();
    }

    public function render()
    {
        return view('livewire.labor-impediment-logs-manager');
    }
}