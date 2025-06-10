<?php

namespace App\Livewire\KanbanImpediments;

use App\Models\Labor;
use App\Models\LaborImpediment;
use Livewire\Component;
// use App\Livewire\KanbanImpediments\TypeOfLaborImpedimentStatus;
use App\Enums\TypeOfLaborImpedimentStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        // Validação dos campos
        $validatedData = $this->validate([
            'response' => 'required|string|min:3',
            'status'   => 'required|in:' . implode(',', TypeOfLaborImpedimentStatus::getValues()),
        ]);

        // Carrega os logs existentes ou inicializa um array vazio
        $currentLogs = $this->registro->logs ?? []; // Se logs for null, usa array vazio

        // Cria a nova entrada de log
        $newLogEntry = [
            'user_id'     => Auth::id(), // ID do usuário que está respondendo
            'observation' => $this->response,
            'date'        => Carbon::now()->toDateTimeString(),
            'old_status'  => $this->registro->status instanceof TypeOfLaborImpedimentStatus ? $this->registro->status->value : (string) $this->registro->status,
            'new_status'  => $this->status, // Novo status selecionado no formulário
        ];

        // Adiciona o novo log no início do array (para aparecer primeiro, se não houver outra ordenação)
        array_unshift($currentLogs, $newLogEntry);

        $this->registro->logs = $currentLogs; // O cast 'json' no modelo cuidará da conversão
        $this->registro->status = TypeOfLaborImpedimentStatus::from($this->status); // Atualiza o status principal do impedimento
        $this->registro->save();

        // Recarrega o registro para garantir que todas as relações e casts estão atualizados
        // Isso é importante para que a view reflita o estado mais recente.
        $this->registro->refresh();

        // Ordena os logs na memória para a re-renderização da view,
        // replicando a ordenação decrescente por data do método loadLogs do modelo
        if (is_array($this->registro->logs)) {
            usort($this->registro->logs, function ($a, $b) {
                return strtotime($b['date']) <=> strtotime($a['date']);
            });
        }

        // Limpa o campo de resposta após o envio
        $this->response = '';
        // Opcional: resetar o 'status' para o novo status do registro ou manter como está
        // $this->status = $this->registro->status->value;

        // Não é necessário emitir evento se o próprio Livewire re-renderiza com os dados atualizados
    }

    public function render()
    {
        // Garante que os logs estão ordenados para a renderização inicial também, se necessário
        // Se $this->registro->logs já é preenchido e ordenado corretamente no mount ou por casts/accessors,
        // esta ordenação aqui pode não ser necessária.
        // No entanto, após a atualização em submitResponse, a ordenação manual lá é crucial.
        $displayLogs = $this->registro->logs ?? [];
        if (is_array($displayLogs)) {
            usort($displayLogs, function ($a, $b) {
                return strtotime($b['date']) <=> strtotime($a['date']); // Ordenação decrescente
            });
        }
        // Passa os logs ordenados para a view se você quiser usar uma variável separada
        // ou confia que $this->registro->logs está corretamente ordenado.

        return view('livewire.kanban-impediments.impediment-header', [
            'displayableLogs' => $displayLogs // Ou apenas use $registro->logs na view
        ]);
    }
}
