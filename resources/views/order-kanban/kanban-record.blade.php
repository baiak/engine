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
        style="background-color: #FFF">

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
            <h3 style="
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        "
                class="flex items-center justify-between cursor-pointer" @click="open = !open">
                <span>Ordem #{{ $record->order_number ?? 'N/A' }}</span>
                <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </h3>

            <div class="text-sm mt-2 text-gray-600">
                <div>Cliente: <strong>{{ $record->client->name }}</strong></div>
                <div>Veículo: <strong>{{ $record->vehicle->factory }}/{{ $record->vehicle->model }}</strong></div>
                <div>Prazo da ordem: <strong>{{ \Carbon\Carbon::parse($record->deadline)->format('d/m/Y') }}</strong></div>
                <div>Status: <strong>{{$record->status}}</strong></div>
            </div>

            <div x-show="open" x-transition class="mt-3 text-sm text-gray-700 space-y-1">
                <fieldset class="border rounded-md p-3 border-gray-200">
                    <legend class="px-2 font-medium text-indigo-600">Serviços e Mão de Obra</legend>

                    <div x-show="open" x-transition class="mt-3 text-sm text-gray-700 space-y-1">


                        @if($record->service->count() > 0)

                        <ul class="list-none p-0 m-0 space-y-3">
                            @foreach($record->service as $serviceIndex => $service)
                            <li class="border border-black rounded-lg p-3 bg-white shadow-sm" style="border-color: #333;">
                                <div x-data="{ serviceOpen: false }" class="w-full">
                                    <div @click="serviceOpen = !serviceOpen" class="border-black flex items-center justify-between cursor-pointer p-2 bg-gray-50 rounded-md mb-2">
                                        <div class="flex items-center">

                                            <span class="font-medium">Serviço {{ $serviceIndex + 1 }}: {{ $service->part->title ?? 'Sem peça específica' }}</span>
                                        </div>
                                        <svg :class="{ 'rotate-180': serviceOpen }" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>

                                    <div x-show="serviceOpen" x-transition class="pl-2">
                                        <div class="flex items-top mb-3 ">
                                            <div class="flex-shrink-0 mr-3 px-3">
                                                {!! app('userAvatar')($service->user_id) !!}
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray">
                                                    Responsável: <strong>{{ app('userName')($service->user_id) }}</strong>
                                                </p>

                                                <p class="text-xs">
                                                    Departamento: <strong>{{$service->department->title}}</strong>
                                                </p>

                                                <p class="text-xs">
                                                    Prazo do serviço:
                                                    <strong>
                                                        {{ \Carbon\Carbon::parse($service->deadline)->format('d/m/Y') }}
                                                    </strong>
                                                </p>

                                                <p class="text-xs">
                                                    Lançado em:<br />
                                                    <strong>
                                                        {{ \Carbon\Carbon::parse($service->created_at)->format('d/m/Y - H:i:s') }}
                                                    </strong>
                                                </p>

                                                <p class="text-xs mt-1">
                                                    Status:
                                                    <span class="px-2 py-1 rounded-full text-xs 
                                                @if($service->status->value === 'concluido') bg-green-100 text-green-800
                                                @elseif($service->status->value === 'em_andamento') bg-yellow-100 text-yellow-800
                                                @elseif($service->status->value === 'aprovado') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $service->status->value ?? 'pendente')) }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-2 p-2 bg-gray-50 rounded border border-gray">
                                            <p class="text-xs font-medium text-gray-700 mb-1">Descrição:</p>
                                            <div class="text-xs prose prose-sm max-w-none">{!! $service->description !!}</div>
                                        </div>

                                        <div class="mt-3 border-l-2 border-indigo-200 pl-3">
                                            <p class="text-xs font-medium text-gray-700 mb-1">Mão de Obra:</p>

                                            @if($service->labor->count() > 0)
                                            <ul class="space-y-1" style="border-left: 1px solid #ccc;">
                                                @foreach($service->labor as $laborIndex => $labor)
                                                <li class="flex items-center bg-gray-50 rounded justify-between py-1 px-2 rounded hover:bg-gray">
                                                    <div class="flex items-center">
                                                        <span class="h-2 w-2 rounded-full mr-2
                                                            @if($labor->pivot->status === 'concluido') bg-green-500
                                                            @elseif($labor->pivot->status === 'em_andamento') bg-yellow-500
                                                            @elseif($labor->pivot->status === 'aprovado') bg-blue-500
                                                            @else bg-gray-400 @endif">
                                                        </span>
                                                        <span class="text-xs">{{ $labor->title }}</span>
                                                    </div>

                                                    <span class="text-xs px-1.5 py-0.5 rounded-full
                                                        @if($labor->pivot->status === 'concluido') bg-green-100 text-green-800
                                                        @elseif($labor->pivot->status === 'em_andamento') bg-yellow-100 text-yellow-800
                                                        @elseif($labor->pivot->status === 'aprovado') bg-blue-100 text-blue-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ $labor->pivot->status ? ucfirst(str_replace('_', ' ', $labor->pivot->status)) : 'Pendente' }}
                                                    </span>
                                                </li>
                                                @endforeach
                                            </ul>
                                            @else
                                            <div class="text-xs text-gray-500 italic py-1">Sem mão de obra registrada</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="text-sm text-gray-500 italic p-3">Nenhum serviço registrado para esta ordem</div>
                        @endif
                    </div>

            </div>
        </div>
</div>