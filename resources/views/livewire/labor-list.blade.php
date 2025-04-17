<!-- {{$var}}!-->@php $item = $var; @endphp


<div class="flex items-left flex-col" x-data="{ expanded: false }">

    <!-- Titulo da mao de obra como botao de expansao!-->
    <div wire:key="{{ $item->pivot->id }}">
        <button
            style=" width: 100%; display: flex; justify-content: start; font-size: small; align-items: center;"
            @click="expanded = ! expanded">
            <!-- Seta apontando para a direita -->
            <x-filament::icon
                icon="heroicon-c-chevron-right"
                class="h-4 w-4 "
                x-show="!expanded"
            />

            <!-- Seta apontando para baixo -->
            <x-filament::icon
                icon="heroicon-c-chevron-down"
                class="h-4 w-4 "
                x-show="expanded"
            />
            <span class="whitespace-nowrap">
                {{($item->title)}}
            </span>
        </button>
    </div>

    <!--box de informacoes!-->
    <div x-show="expanded"
         x-collapse.duration.500ms
         id="description{{$item->pivot->id}}"
         style="background-color: #cccbcb;color: #202020"
         class="pb-3 pl-3 pr-3 m-2 rounded-xl border border-gray-600">
        <div class="p-1 mb-3 rounded-t-xl"
             style="width: 100%;border-bottom: #1b1e21 1px solid; background-color:#9ca3af;display: flex; color: #0062cc; justify-content: space-between; align-items: center;">
            <x-filament::icon
                icon="css-info"
                class="h-4 w-4 mx-2"
            />
            <small style="color: #4b5563; font-weight: bold">
                Informações
            </small>
            <button @click="expanded = ! expanded" style="color: #9d174d"
                    title="Clique para fechar">
                <x-filament::icon
                    icon="eva-close-square-outline"
                    class="h-4 w-4 mx-2 text-red"
                />
            </button>
        </div>
        <div class="p-3">
            <small>
                {!!$item->pivot->description!!}
            </small>
        </div>
    </div>

    <!--Botao de status !-->
    <div class="p-2" style="font-size: x-small">
        <div x-data="{ currentStatus: @entangle('status') }"
             x-init="$wire.on('statusUpdated', newStatus => currentStatus = newStatus)">
        </div>
        <div x-data="{ expanded: false }">
            <button @click="expanded = ! expanded"
                    class="inline-flex items-center px-1 py-1 pe-3 rounded-full whitespace-nowrap"
                    @switch($item->pivot->status)
                        @case('Aguardando aprovacao')
                            style="background-color:#fef9c3;color: #854d0e; font-size: x-small"
                    @break
                    @case('Aprovado')
                        style="background-color:#d1fae5; color: #065f46; font-size: x-small"
                    @break
                    @case('Rejeitado')
                        style="background-color:#f3f4f6; color: #1f2937; font-size: x-small"
                    @break
                    @case('Em Andamento')
                        style="background-color:#dbeafe; color: #1e40af; font-size: x-small"
                    @break
                    @case('Impedido')
                        style="background-color:#fee2e2; color: #991b1b; font-size: x-small"
                    @break
                    @case('Finalizado')
                        style="background-color:#71d871; color: #1f2937;font-size: x-small"
                    @break
                    @default
                        style="background-color:#f3f4f6; color: #1f2937; font-size: x-small"
                @endswitch >
                <!-- Icones svg!-->
                @switch($item->pivot->status)
                    @case('Aguardando aprovacao')
                        <x-filament::icon
                            icon="pepicon-hourglass-circle"
                            class="h-4 w-4 mx-1 "
                        />
                        @break
                    @case('Aprovado')
                        <x-filament::icon
                            icon="heroicon-s-check"
                            class="h-4 w-4 mx-1 "
                        />
                        @break
                    @case('Rejeitado')
                        <x-filament::icon
                            icon="uiw-dislike-o"
                            class="h-4 w-4 mx-1 "
                        />
                        @break
                    @case('Em Andamento')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             class="mx-1 h-4 w-4 size-6"
                             viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        @break
                    @case('Impedido')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             class="size-6 mx-1 h-4 w-4 text-red-500 dark:text-red-400"
                             viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                        @break
                    @case('Finalizado')
                        <x-filament::icon
                            icon="elemplus-finished"
                            class="h-4 w-4 mx-1 "
                        />
                        @break
                    @case('Condenado')
                        <x-filament::icon
                            icon="hugeicons-dead"
                            class="h-4 w-4 mx-1 "
                        />
                        @break
                @endswitch

                {{ $item->pivot->status }}

                <!-- Seta apontando para a direita -->
                <x-filament::icon
                    icon="heroicon-c-chevron-right"
                    class="h-4 w-4 "
                    x-show="!expanded"
                />

                <!-- Seta apontando para baixo -->
                <x-filament::icon
                    icon="heroicon-c-chevron-down"
                    class="h-4 w-4 "
                    x-show="expanded"
                />
            </button>
            <div x-show="expanded" class="p-3 mt-3 " x-collapse
                 x-on:click="laborTitle = 'open =! open'">

                <!--FORMULARIO DE STATUS!-->
                <x-filament::section>
                    <form wire:submit.prevent="updateStatus"
                          style="display: flex;
                                             align-items: center;
                                             gap: 4px; font-size: x-small">
                        <x-slot name="heading" class="align-content-end whitespace-nowrap">
                            <span style="font-size: x-small" class="my-4">Alterar status da mão de obra</span>
                            <button title="Cancelar"
                                    @click="expanded = ! expanded"
                                    style="background-color: #ef0543; margin-top:3px; color: white; "
                                    class="m-4 p-1 px-1 bg-gray-700 rounded small-button border-gray-600">
                                <x-filament::icon
                                    icon="heroicon-m-x-mark"
                                    class="h-3 w-3 m-3"
                                    x-show="expanded"/>
                            </button>
                        </x-slot>

                        <x-filament::input.wrapper>
                            <x-filament::input.select style="font-size: x-small"
                                                      wire:model="selectedStatus"
                                                      class="small-select p-1"
                                                      x-on:change="$wire.setServiceLaborId({{$item->pivot->id}}), expanded = ! expanded">
                                <option value="" selected>Selecione uma opção</option>
                                @foreach (\App\Enums\TypeOfLaborStatus::cases() as $status)
                                    <option value="{{ $status->value }}">
                                        {{ $status->getLabel() }}
                                    </option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </form><!--formulario de alteracao de status !-->

                    <!--historico da mao de obra !-->
                    <div style="display: flex; justify-content: center">
                        <x-filament::modal>
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-start px-1 py-1 pe-3 rounded-full whitespace-nowrap m-3"
                                    style=" font-size:x-small; background-color: #4b5563; color: white; margin-top: 20px; margin-bottom: 5px;">
                                    <x-filament::icon
                                        icon="tabler-history"
                                        class="h-4 w-4 mr-3"
                                    />
                                    Histórico de status
                                </button>
                            </x-slot>
                            <div style=" font-size: small; color: #9ca3af"
                                 x-data="{logs: [],async fetchLogs(serviceLaborId){const response = await @this.call('getServiceLaborLogs', serviceLaborId);this.logs = response;},init(){const serviceLaborId = {{ $item->pivot->id }};this.fetchLogs(serviceLaborId);}}">
                                <template x-if="logs.length > 0">
                                    <ul>
                                        <template x-for="log in logs" :key="log.id">
                                            <li class="flex items-center m-2 p-2 rounded-xl mb-3 mt-2 border border-gray-600">
                                                <div
                                                    x-html="log.new_values?.user_avatar || log.old_values?.user_avatar"
                                                    class="m-2 p-2"></div>
                                                <div class="m-2 p-2">
                                                                <span style="font-weight: bold"
                                                                      x-text="log.new_values?.status || 'N/A'"></span>
                                                    <br/>
                                                    <span
                                                        x-text="log.new_values?.updated_at || log.old_values?.updated_at"></span>
                                                    <!--<span x-text="log.new_values?.user_avatar || log.old_values?.user_avatar"></span>==!-->
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                            {{-- Modal content --}}
                        </x-filament::modal>
                    </div>
                    <!-- fim historico da mao de obra !-->
                </x-filament::section>
            </div>
        </div>


    </div>

    <!--data de criacao!-->
    <div class="flex items-center mt-3" style="font-size: x-small">
        <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 mr-3"/> &nbsp;
        <span class="m-2">Adicionado em: {{$item->pivot->created_at->format('d/m/Y H:i')}}</span>
    </div>

    <!--footer da caixa de mao de obra!-->
    <div class=" inline-flex items-center mt-2 pt-2" style=" border-top: 1px solid #9ca3af; width: 100%; display: flex; justify-content: space-between;">

        <!--modal para adicionar impedimento!-->
        <x-filament::modal>
            <x-slot name="trigger" x-data="{ showText: false }">
                <button class="rounded-md m-2"
                        style="padding:0.60em; background-color: #2b2f32; margin-right: 3px; font-size: x-small; color: white; position: relative;"
                        @mouseenter="showText = true"
                        @mouseleave="showText = false">
                    <!-- Ícone -->
                    <x-icon.add-impediment-icon class="h-5 w-5" style="margin-right: 5px;" />
                    <!-- Tooltip com texto -->
                    <span x-show="showText" x-transition
                          style="transition: opacity 0.6s; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
                     background-color: #374151; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.75rem;
                     white-space: nowrap; opacity: 0.9; margin-top: 5px;">
                        Adicionar impedimento
                    </span>
                </button>
            </x-slot>

            <!-- Conteúdo do Modal -->
            <div style="border: 1px solid #4b5563; border-radius: 10px; padding: 15px; margin-top: 10px;">
                @include('livewire.impediments.labor-impediment-form', [
                    'service_labor_id' => $item->pivot->id,
                    'users' => \App\Models\User::all(),
                ])
            </div>
        </x-filament::modal>
        <!--/modal para adicionar impedimento!-->

        <!--modal para visualizar impedimento !-->
        <x-filament::modal>
            <x-slot name="trigger" x-data="{ showText: false }">
                @include('livewire.impediments.view-impediments-button', ['service_labor_id' => $item->pivot->id])
            </x-slot>

            <!-- Conteúdo do Modal -->
            <div style="border: 1px solid #4b5563; border-radius: 10px; padding: 15px; margin-top: 10px;">
                @include('livewire.impediments.impediment-list', ['serviceLaborId' => $item->pivot->id])
            </div>
        </x-filament::modal>
        <!--modal para visualizar impedimento !-->

        <!--modal para historico de status!-->
        @include('livewire.labor-status-history', ['service_labor_id' => $item->pivot->id,])
        <!--/modal para historico de status !-->

    </div>
    <!--/footer da caixa de mao de obra!-->
</div>

