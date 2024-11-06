@if ($getRecord()->statusHistory->isNotEmpty())
    <div x-data="{ open: false }">
        <button
            class="rounded-xl p-2 mb-3 whitespace-nowrap flex items-center space-x-1"
            style="background-color: #9ca3af;
                   color:#333334;
                   font-size:small;
                   text-align: start;
                   font-weight: bold"
            @click="open = ! open"
        >
            <x-filament::icon
                icon="tabler-history"
                class="h-4 w-4 mr-3"

            />
            <span class="ml-2">Histórico</span>
            <!-- Seta apontando para a direita -->
            <x-filament::icon
                icon="heroicon-c-chevron-right"
                class="h-4 w-4"
                x-show="!open"
            />
            <!-- Seta apontando para baixo -->
            <x-filament::icon
                icon="heroicon-c-chevron-down"
                class="h-4 w-4"
                x-show="open"
            />
        </button>

        <fieldset x-show="open"
                  x-transition:enter.duration.500ms
                  x-transition:leave.duration.400ms
                  @click.outside="open = false"
                  class="rounded-xl border border-gray-600 p-3 mt-2"
                  style="/*background-color: #9ca3af;*/
                         color: #333334;
                         font-size: small
                         ">

            @php
                $firstIteration = true; // Variável de controle para exibir $oldValues apenas uma vez
                $legendCount = 1;
            @endphp

            @foreach($getRecord()->statusHistory as $item)
                @php
                    $newValues = json_decode($item->new_values, true); // Decodifica JSON como array associativo
                    $oldValues = json_decode($item->old_values, true);
                @endphp

                @if($firstIteration && isset($oldValues))
                    <fieldset class="m-2 mb-3 p-2 border border-gray-600 rounded-xl"
                              x-show="open"
                              x-transition.duration.1000ms
                              x-transition.scale.origin.top
                              style="/*background-color:#cccbcb*/;
                                color: #d6d6d6">

                            {{ \Carbon\Carbon::parse($oldValues['created_at'])->format('d/m/Y - H:i') }}<br />

                        Cadastrado por:<strong>{{ $item->user->name }}</strong><br />
                        <strong>Status:</strong> {{ $oldValues['status'] }}
                    </fieldset>
                    @php
                        $firstIteration = false; // Define como false após exibir $oldValues uma vez
                        $legendCount++;
                    @endphp
                @endif

                @if(isset($newValues))
                    <fieldset class="m-2 mt-3 p-2 border border-gray-600 rounded-xl"
                              style="/*background-color:#454545;*/
                                color: #d6d6d6"
                              x-show="open"
                              x-transition.scale.origin.top>


                            {{ \Carbon\Carbon::parse($newValues['updated_at'])->format('d/m/Y - H:i') }}<br />

                        <strong>Status:</strong> {{ $newValues['status'] }}
                    </fieldset>
                    @php
                        $legendCount++; // Incrementa o contador após exibir o $newValues
                    @endphp
                @endif
            @endforeach
        </fieldset>
    </div>
@endif
