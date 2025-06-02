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
                class="flex items-center justify-between cursor-pointer" @click="open = !open, openHistoryService = !openHistoryService">
                <span>Ordem #{{ $record->order->order_number ?? 'N/A' }}</span>
                <!-- Ícone dropdown (seta para baixo) -->
                <span style="margin: 3px; color: #555;">
                    {{ $record->part->title ?? 'N/A' }}
                </span>
                <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </h3>
            <div class="text-sm mt-2 text-gray-600">
                <div>Cliente: <strong>{{ $record->order->client->name }}</strong></div>
                <div>Veículo: <strong>{{ $record->order->vehicle->formatted_vehicle }}</strong></div>
                <div>Prazo: <strong>{{ \Carbon\Carbon::parse($record->deadline)->format('d/m/Y') }}</strong></div>
                <div>Status: <strong>{{$record->status}}</strong></div>


            </div>
            <!-- Detalhes colapsáveis -->
            <div x-show="open" x-transition class="mt-2 text-sm text-gray-700 space-y-1">
                <div>
                    <strong>Descrição:</strong>
                    <p class="text-gray-600">{!! $record->description ?? 'N/A' !!}</p>
                </div>
                <div style="margin-top: 10px;">
                    <span style="margin-left:10px;">Responsável/Departamento:</span>
                    <div style="padding: 10px;">
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
                            @foreach($record->serviceLabors as $getLabors)
                            <li class="labor-item" style="border-bottom: #cacfd2 solid 1px; padding:8px 6px;"
                                x-data="{ open: false, currentStatus: '{{ $getLabors->status }}', observationsOpen: false }">
                                {{-- Flex container para Titulo/Obs Toggle e Status/Status Dropdown --}}
                                <div class="flex items-center justify-between">
                                    {{-- Title and Observations Toggle --}}
                                    <div class="flex-grow min-w-0">
                                        <div class="flex items-start justify-start gap-2">

                                            <!--dropdown da mao de obra  -->
                                            <div x-data="{openLaborInfos:false}">
                                                <button class="truncate flex items-center justify-start" @click="openLaborInfos = !openLaborInfos">
                                                    {{ $getLabors->labor->title }}
                                                    <svg :class="{ 'rotate-180': openLaborInfos }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                <div x-show="openLaborInfos" @click="openHistoryService = !openHistoryService"

                                                    style="display:none; border-left: #333 1px solid; margin-left:5px; padding: 2px; color: #333;">
                                                    @if($getLabors->labor->description)
                                                    <strong>Descrição:</strong> {{ $getLabors->labor->description }}
                                                    @endif
                                                    <div class="flex items-end justify-start gap-2">
                                                        <span style="padding-bottom:1px;">Status:</span>
                                                        {{-- Dropdown --}}
                                                        <div class="flex items-center flex-shrink-0 ml-4">
                                                            <div x-text="currentStatus" :style="`color: {{ \App\Enums\TypeOfLaborStatus::tryFrom($getLabors->status)->getColor() ?? '#000' }};`"></div>
                                                            <div class="relative">
                                                                <button 
                                                                    @click="open = !open" 
                                                                    class="text-sm text-gray-500 hover:text-gray-700 focus:outline-none"
                                                                    x-bind:disabled="currentStatus === 'Cancelado' || currentStatus === 'Finalizado'"
                                                                    :class="{ 'opacity-50 cursor-not-allowed': currentStatus === 'Cancelado' || currentStatus === 'Finalizado' }"
                                                                    style="font-size:5px;">
                                                                    
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm0 14a1 1 0 01-.707-.293l-3-3a1 1 0 011.414-1.414L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3A1 1 0 0110 17z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </button>
                                                                {{-- Status change dropdown panel --}}
                                                                <div x-show="open" @click.away="open = false"
                                                                    class="absolute z-20 right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1"
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
                                                       $wire.updateLaborStatus({{ $getLabors->id }}, '{{ $status->value }}', {{ $record->getKey() }})
                                                     "
                                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                                        :style="currentStatus === '{{ $status->value }}' ? 'font-weight: bold; color: {{ $status->getColor() }};' : 'color: {{ $status->getColor() }};'">
                                                                        {{ $status->getLabel() }}
                                                                    </a>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($getLabors->observations->count() > 0)
                                                    <button
                                                        @click="observationsOpen = !observationsOpen"
                                                        class="text-xs text-blue-500 hover:text-blue-700 focus:outline-none ml-2 flex items-center gap-1"
                                                        title="Ver/Ocultar Observações">
                                                        <span class="flex items-center justify-between" style="margin-left:4px;">
                                                            <span>Observações({{ $getLabors->observations->count() }})</span>

                                                            <svg x-show="!observationsOpen" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                            </svg>

                                                            <svg x-show="observationsOpen" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                                            </svg>
                                                        </span>
                                                    </button>
                                                    @endif
                                                    @if($getLabors->created_at)
                                                    <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                                                        <span style="font-weight: bold;">Mao de obra incluída em:</span> {{\Carbon\Carbon::parse($getLabors->created_at)->format('d/m/Y - H:i') ?? 'N/A'}}
                                                    </p>
                                                    @endif

                                                    @if($getLabors->approvedAt)
                                                    <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                                                        <span style="font-weight: bold;">Aprovada em:</span> {{\Carbon\Carbon::parse($getLabors->approvedAt)->format('d/m/Y - H:i') ?? 'N/A'}}
                                                    </p>
                                                    @endif

                                                    @if($getLabors->startedAt)
                                                    <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                                                        <span style=" font-weight: bold;">Iniciada em:</span> {{\Carbon\Carbon::parse($getLabors->starteddAt)->format('d/m/Y - H:i') ?? 'N/A'}} {{-- Corrected typo from starteddAt to startedAt --}}
                                                    </p>
                                                    @endif

                                                    @if($getLabors->finishedAt)
                                                    <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                                                        <span style=" font-weight: bold;">Finalizada em:</span> {{\Carbon\Carbon::parse($getLabors->finishedAt)->format('d/m/Y - H:i') ?? 'N/A'}}
                                                    </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- dropdown mao de obra -->

                                        </div>
                                    </div>
                                </div>

                                {{-- dropdown com observacoes e historico de datas --}}
                                @if($getLabors->observations->count() > 0)
                                <div x-show="observationsOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="mt-2 space-y-1 pl-4 px-3 border-gray-200 dark:border-gray-600 pt-2" style="display:none;">
                                    @foreach($getLabors->observations as $observation)
                                    <div class="text-xs p-1 bg-yellow-50 dark:bg-gray-650 border border-yellow-200 dark:border-gray-500 rounded">
                                        <p class="font-semibold">{{ $observation->title }}</p>
                                        <p class="text-gray-600 dark:text-gray-300">{!! $observation->description !!}</p>
                                        <p class="text-xxs text-gray-400 dark:text-gray-500">
                                            {{ $observation->user->name ?? 'Sistema' }} em {{ $observation->created_at->format('d/m/y H:i') }}
                                        </p>
                                    </div>
                                    @endforeach

                                </div>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p style="margin: 8px 0; color: #999; font-style: italic;">Sem mão de obras cadastradas</p>
                        @endif

                        <!--Sessao de historico -->
                        <div x-data="{openHistoryService: false}">
                            <div class="mt-2 flex items-center justify-between cursor-pointer" @click="openHistoryService = !openHistoryService">
                                <div class="flex items-center justify-between">
                                    <x-heroicon-s-list-bullet class="w-5 h-5 mx-2 text-gray-600" />
                                    Histórico do serviço
                                    <svg :class="{ 'rotate-180': openHistoryService }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <div x-show="openHistoryService" x-transition.duration.1000ms x-transition.scale.origin.top style="border: #333 1px solid; border-radius: 6px; padding: 4px; background-color: #f0f0f0; color: #333;">
                                <span style="font-weight: bold;">{{ $record->statusHistory->count() }}</span> registros
                                @php
                                $firstIteration = true; // Variável de controle para exibir $oldValues apenas uma vez
                                $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
                                @endphp

                                @foreach($record->statusHistory as $item)
                                @php
                                $newValues = json_decode($item->new_values, true); // Decodifica JSON como array associativo
                                $oldValues = json_decode($item->old_values, true);
                                @endphp

                                @if($firstIteration && isset($oldValues))
                                <fieldset
                                    class="m-2 mt-3 p-2 border border-gray-600 rounded-xl  whitespace-nowrap flex items-center space-x-1"

                                    style="/*background-color:#cccbcb*/
                                color:rgb(44, 43, 43)">
                                    <div class="flex flex-col items-center" style="margin-right: 10px">
                                        <!-- Imagem de perfil redonda -->
                                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-600">
                                            <img src="{{ $baseUrl }}/storage/{{ $item->user->profileImg }}"
                                                alt="Imagem de perfil de {{ $item->user->name }}"
                                                x-tooltip="'{{ $item->user->name }}'">
                                        </div>
                                    </div>


                                    <div>

                                        {{ \Carbon\Carbon::parse($oldValues['created_at'])->format('d/m/Y - H:i') }}<br />
                                        <strong>Status:</strong> {{ $oldValues['status'] }}
                                    </div>

                                </fieldset>
                                @php
                                $firstIteration = false; // Define como false após exibir $oldValues uma vez
                                @endphp
                                @endif

                                @if(isset($newValues))
                                <fieldset class="m-2 mt-3 p-2 border border-gray-600 rounded-xl flex items-center space-x-1"
                                    style="/*background-color:#454545;*/
                                color:rgb(44, 43, 43)">
                                    <div class="flex flex-col items-center" style="margin-right: 10px">
                                        <!-- Imagem de perfil redonda -->
                                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-600">
                                            <img src="{{ $baseUrl }}/storage/{{ $item->user->profileImg }}"
                                                alt="Imagem de perfil de {{ $item->user->name }}"
                                                x-tooltip="'{{ $item->user->name }}'">

                                        </div>
                                    </div>
                                    <div>
                                        {{ $item->user->name }} - {{ \Carbon\Carbon::parse($newValues['updated_at'])->format('d/m/Y - H:i') }}<br />
                                        <strong>Status:</strong> {{ $newValues['status'] }}
                                    </div>
                                </fieldset>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        <!--Sessao de historico -->
                    </div>
                </div>
            </div>
        </div>
</div>