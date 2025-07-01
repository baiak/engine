<?php

namespace App\Livewire\KanbanImpediments;

use App\Models\LaborImpediment;
use Livewire\Component;
use App\Enums\TypeOfLaborImpedimentStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ImpedimentHeader extends Component
{
    public LaborImpediment $registro;
    public string $response = '';
    public string $status = ''; // Vai guardar o valor do novo status selecionado

    public function mount(LaborImpediment $record)
    {
        $this->registro = $record;
        // Inicializa o status com o status atual do impedimento
        if ($this->registro->status instanceof TypeOfLaborImpedimentStatus) {
            $this->status = $this->registro->status->value;
        } else {
            // Fallback se o status não for uma instância do Enum (pode acontecer se os dados forem antigos)
            $this->status = (string) $this->registro->status;
        }
    }

    public function submitResponse()
    {
        // 1. Validação dos campos
        $validatedData = $this->validate([
            'response' => 'required|string|min:3',
            'status'   => 'required|in:' . implode(',', TypeOfLaborImpedimentStatus::getValues()),
        ]);

        // 2. Preparar a nova entrada de log
        $newLogEntry = [
            'user_id'     => Auth::id(), // ID do usuário que está respondendo
            'observation' => $this->response,
            'date'        => Carbon::now()->toDateTimeString(),
            // Garante que old_status e new_status sejam strings para consistência no log JSON
            'old_status'  => (string) ($this->registro->status instanceof TypeOfLaborImpedimentStatus ? $this->registro->status->value : $this->registro->status),
            'new_status'  => (string) $this->status, // Novo status selecionado no formulário
        ];

        // 3. Obter os logs existentes de forma segura e adicionar o novo
        // O cast 'json' no modelo garante que $this->registro->logs já é um array ou null.
        // Se for null, o operador coalescente (??) garante um array vazio para começar.
        $currentLogs = $this->registro->logs ?? [];

        // Adiciona o novo log no início do array para exibição mais recente primeiro
        array_unshift($currentLogs, $newLogEntry);

        // 4. Atribuir o array modificado de volta ao atributo 'logs' do modelo
        // ESSENCIAL: Esta reatribuição explícita sinaliza ao Eloquent que o atributo foi modificado.
        $this->registro->logs = $currentLogs;

        // 5. Atualizar o status principal do impedimento
        // Converte o valor da string para a instância do Enum
        $this->registro->status = TypeOfLaborImpedimentStatus::from($this->status);

        // 6. Salvar as alterações no banco de dados
        try {
            $this->registro->save();

            // 7. Recarregar o registro para garantir que Livewire tem o estado mais recente do DB
            // (incluindo quaisquer processamentos de cast ou mutators que possam ter ocorrido)
            $this->registro->refresh();

            // 8. Limpar o campo de resposta e (opcionalmente) atualizar o status no frontend
            $this->response = '';
            // Se você quiser que o select do status no formulário Livewire reflita o novo status do registro
            $this->status = (string) $this->registro->status->value;

            // 9. Emitir uma notificação de sucesso (usando Filament Notifications)
            Notification::make()
                ->title('Resposta e status atualizados com sucesso!')
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Logar o erro para depuração
            Log::error("Erro ao salvar resposta de impedimento: " . $e->getMessage(), [
                'impediment_id' => $this->registro->id,
                'user_id' => Auth::id(),
                'new_log_entry' => $newLogEntry,
                'exception' => $e
            ]);

            // Notificar o usuário sobre o erro
            Notification::make()
                ->title('Erro ao atualizar impedimento!')
                ->body('Ocorreu um erro ao salvar sua resposta. Por favor, tente novamente.')
                ->danger()
                ->send();
        }
    }

public function render()
    {
        // Certifique-se de que os logs estão ordenados para a renderização da view.
        // O método `refresh()` garante que $this->registro->logs está atualizado do DB.
        // A ordenação manual aqui é para a apresentação na view.
        $displayLogs = $this->registro->logs;
        if (is_array($displayLogs)) {
            usort($displayLogs, function ($a, $b) {
                $dateA = $a['date'];
                $dateB = $b['date'];
                //return $dateB <=> $dateA; // Ordenação decrescente (mais recente primeiro)
                return $dateA <=> $dateB; // Ordenação crescente (mais antigo primeiro)
            });
        }

        return view('livewire.kanban-impediments.impediment-header', [
            'registro' => $this->registro, // Passa o registro completo para a view
            'displayableLogs' => $displayLogs // Passa os logs já ordenados para exibição
        ]);
    }
}