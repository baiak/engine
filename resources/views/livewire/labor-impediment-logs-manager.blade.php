<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- Left Column: Section to Display Existing Logs --}}
    <div class="lg:col-span-7">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Histórico de Interações</h3>
        @if (empty($logEntries))
        <div class="p-4 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-md shadow">
            Nenhum log registrado para este impedimento ainda.
        </div>
        @else
        <div class="space-y-4">
            @foreach ($logEntries as $log)
            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800">
                <div class="flex justify-between items-start mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <strong class="dark:text-white">Usuário:</strong> {{ $log['user_name'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log['date'] }}</span>
                </div>
                <p class="mb-1 text-sm text-gray-600 dark:text-gray-400">
                    <strong class="dark:text-white">Status Definido:</strong>
                    <span class="font-semibold px-2 py-0.5 rounded-full text-xs
                                @switch(App\Enums\TypeOfLaborImpedimentStatus::tryFrom($log['selected_status']))
                                    @case(App\Enums\TypeOfLaborImpedimentStatus::em_aberto) bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100 @break
                                    @case(App\Enums\TypeOfLaborImpedimentStatus::resolvido) bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100 @break
                                    @case(App\Enums\TypeOfLaborImpedimentStatus::cancelado) bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100 @break
                                    @case(App\Enums\TypeOfLaborImpedimentStatus::sem_solucao) bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @break
                                    @default bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @break
                                @endswitch">
                        {{ App\Enums\TypeOfLaborImpedimentStatus::tryFrom($log['selected_status'])?->value ?? $log['selected_status'] }}
                    </span>
                </p>
                <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 bg-white dark:bg-gray-700 p-2 rounded">
                    {{ $log['observation'] }}
                </p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Right Column: Section to Add New Log --}}
    <div class="lg:col-span-5">
        <div class="sticky top-6"> {{-- Wrapper to make the form section sticky --}}
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Adicionar Novo Registro / Atualizar Status Geral</h4>
            <form wire:submit.prevent="addLogEntry" class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-800">
                <div class="mb-4">
                    <label for="newObservation" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nova Observação:</label>
                    <textarea wire:model.lazy="newObservation" id="newObservation" rows="4"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"></textarea>
                    @error('newObservation') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <label for="newSelectedStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Atualizar Status do Impedimento Para:</label>
                    <select wire:model.lazy="newSelectedStatus" id="newSelectedStatus"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        <option value="">Selecione o novo status...</option>
                        @foreach ($this->getStatusOptions() as $statusCase)
                        <option value="{{ $statusCase->value }}">{{ $statusCase->value }}</option>
                        @endforeach
                    </select>
                    @error('newSelectedStatus') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="addLogEntry"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 disabled:opacity-50">
                        <svg wire:loading wire:target="addLogEntry" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="addLogEntry">Adicionar Registro</span>
                        <span wire:loading wire:target="addLogEntry">Salvando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>