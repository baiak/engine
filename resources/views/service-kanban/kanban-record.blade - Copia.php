<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}) < 3)
        x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
        @endif

        @php
        $statusColors=[ 'Aprovado'=> '#d4edda',
        'Pendente' => '#fff3cd',
        'Finalizado' => '#d1ecf1',
        ];
        $currentStatus = $record->status->value;
        $backgroundColor = $statusColors[$currentStatus] ?? '#FFFFFF';
        @endphp

        style="background-color: {{ $backgroundColor }}"
        >

        <div x-data="{ open: false }" style="
        padding: 12px;
        margin: 12px 0;
        font-family: Arial, sans-serif;
        line-height: 1.4;
        border: 1px solid #545454;
        border-radius: 12px;
        
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 14px;
    ">
            <!-- Informações principais visíveis -->
            <h3 style="
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        "
                class="flex items-center justify-between cursor-pointer" @click="open = !open">
                <span>Ordem #{{ $record->order->order_number ?? 'N/A' }}</span>
                <!-- Ícone dropdown (seta para baixo) -->
                <span style="margin: 3px; color: #555;">
                    {{ $record->part->title ?? 'N/A' }}
                </span>
                <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </h3>


            <!-- Detalhes colapsáveis -->
            <div x-show="open" x-transition class="mt-2 text-sm text-gray-700 space-y-1">

                <p style="margin: 8px 0; color: #555;">
                    <strong>Cliente:</strong> {{ $record->order->client->name ?? 'N/A' }}
                </p>

                <p style="margin: 8px 0; color: #555;">
                    <strong>Veículo:</strong> {{ $record->order->vehicle->factory ?? 'N/A' }} / {{ $record->order->vehicle->model ?? 'N/A' }} / {{ $record->order->vehicle->motor ?? 'N/A' }}
                </p>

                <div style="margin-top: 10px;">
                    <div style="margin-top: 10px; padding: 10px;">
                        <div style="
                    display: flex;
                    align-items: center;
                    margin: 8px 0;
                    background-color:rgba(240, 240, 240, 0.23);
                    padding: 8px;
                    border-radius: 6px;
                    border: 1px solid rgb(34, 34, 34);
                ">
                            @php
                            $userId = $record->user_id ?? null;
                            @endphp
                            @if($userId)
                            {!! app('userAvatar')($userId) !!}
                            <span style="
                            margin-left: 8px;
                            font-size: 14px;
                            color: #181918;
                            font-weight: bold;
                            
                        ">
                                {{ app('userName')($userId) }} - {{$record->department->title}}
                            </span>
                            @else
                            <span style="
                            font-size: 14px;
                            color: #888;
                            font-style: italic;
                        ">
                                Usuário não atribuído
                            </span>
                            @endif
                        </div>
                        <p style="margin: 8px 0; color: #555; font-size: small">
                            <strong>Mão de obras ({{$record->labor->count()}}):</strong>
                        </p>
                        @if($record->labor->count() > 0)
                        <ul style="
                        list-style-type: none;
                        padding: 5px;
                        margin: 0;
                        border: 1px solid #e0e0e0;
                        border-radius: 6px;
                        background-color: #ffffff;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        color: #333;
                        font-size: 12px;
                        line-height: 1.4;">
                            @foreach($record->labor as $getLabors)
                            <li class="labor-item flex items-center justify-between" style="border-bottom: #cacfd2 solid 1px; padding:4px 3px 6px;" x-data="{ open: false, currentStatus: '{{ $getLabors->pivot->status }}' }">
                                <div><b>{{$getLabors->title}}</b></div>
                                <div class="flex items-center">
                                    <div x-text="currentStatus" :style="`color: {{ \App\Enums\TypeOfLaborStatus::tryFrom($getLabors->pivot->status)->getColor() ?? '#000' }}; font-size: small; margin-right: 8px;`"></div>
                                    <div class="relative">
                                        <button @click="open = !open" class="text-sm text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm0 14a1 1 0 01-.707-.293l-3-3a1 1 0 011.414-1.414L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3A1 1 0 0110 17z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false"
                                            class="absolute z-10 right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 transform scale-95"
                                            x-transition:enter-end="opacity-100 transform scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 transform scale-100"
                                            x-transition:leave-end="opacity-0 transform scale-95"
                                            style="display: none;">
                                            @foreach(\App\Enums\TypeOfLaborStatus::cases() as $status)
                                            <a href="#"
                                                @click.prevent="
                                   currentStatus = '{{ $status->value }}';
                                   open = false;
                                   $wire.updateLaborStatus({{ $getLabors->pivot->id }}, '{{ $status->value }}', {{ $record->getKey() }})
                                 "
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                :style="currentStatus === '{{ $status->value }}' ? 'font-weight: bold; color: {{ $status->getColor() }};' : 'color: {{ $status->getColor() }};'">
                                                {{ $status->getLabel() }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p style="margin: 8px 0; color: #999; font-style: italic;">Sem mão de obras cadastradas</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
</div>