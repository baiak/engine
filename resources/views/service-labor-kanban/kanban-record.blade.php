<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}) < 3)
        x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800');
            $el.classList.remove('bg-white', 'dark:bg-gray-700');
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800');
                $el.classList.add('bg-white', 'dark:bg-gray-700');
            }, 3000)
        "
        @endif

        @php
        // Assuming $record->status->value exists and contains strings like 'Aprovado', 'Pendente', 'Finalizado'
        // Also assuming $record->order, $record->part, $record->user_id, $record->department, $record->labor exist
        // and have the necessary properties and relationships as used in 'kanban-record.blade - Copia.php'.
        // You might need to adjust these based on your actual data structure.

        $statusColors = [
        'Aprovado' => '#d4edda', // Greenish
        'Aguardando Aprovação' => '#fff3cd', // Yellowish
        'Pendente' => '#fff3cd', // Yellowish
        'Cancelado' => '#f8d7da', // Reddish
        'Finalizado' => '#d1ecf1', // Bluish
        // Add other statuses and their colors here
        ];
        $currentStatus = $record->status ?? 'Pendente'; // Default to 'Pendente' if status is not set
        $backgroundColor = $statusColors[$currentStatus] ?? '#FFFFFF'; // Default to white if status color is not found
        @endphp

        style="background-color: {{ $backgroundColor }}; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin-bottom: 12px;"
        >
        {{-- This line was in the original, displaying a title. You might want to integrate it differently or remove it
         if the new structure handles the title sufficiently.
    {{ $record->{static::$recordTitleAttribute} }}
        --}}

        <div x-data="{ open: false }" style="
        padding: 12px;
        font-family: Arial, sans-serif;
        line-height: 1.4;
        font-size: 14px;
    ">
            <h3 style="
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        "
                class="flex items-center justify-between cursor-pointer" @click="open = !open">
                <span>
                    Ordem #{{ $record->getOrderDetails->order_number ?? ($record->order->order_number ?? 'N/A') }}
                </span>
                <span style="margin: 0 5px; color: #555; text-align: center; flex-grow: 1;">
                    {{ $record->service->part->title ?? ($record->part->title ?? 'N/A') }}
                </span>
                <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform duration-300 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </h3>

            <div x-show="open" x-transition class="mt-3 text-sm text-gray-700 space-y-2">

                <p style="margin: 8px 0; color: #555;">
                    <strong>Cliente:</strong> {{ $record->getOrderDetails->client->name ?? ($record->order->client->name ?? 'N/A') }}
                </p>

                <p style="margin: 8px 0; color: #555;">
                    <strong>Veículo:</strong>
                    @if(isset($record->getOrderDetails) && $record->getOrderDetails->vehicle_details)
                    {{ $record->getOrderDetails->vehicle_details }}
                    @elseif(isset($record->order) && isset($record->order->vehicle))
                    {{ $record->order->vehicle->factory ?? 'N/A' }} / {{ $record->order->vehicle->model ?? 'N/A' }} / {{ $record->order->vehicle->motor ?? 'N/A' }}
                    @else
                    N/A
                    @endif
                </p>

                <p style="margin: 8px 0; color: #555;">
                    <strong>Serviço/Peça Principal:</strong> {{ $record->service->part->title ?? ($record->part->title ?? 'N/A') }}
                </p>

                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                    <div style="
                    display: flex;
                    align-items: center;
                    margin: 8px 0;
                    background-color:rgba(240, 240, 240, 0.3); /* Slightly transparent background */
                    padding: 8px;
                    border-radius: 6px;
                    border: 1px solid #e0e0e0; /* Softer border */
                ">
                        @php
                        // Ensure $record->user_id and $record->department are available
                        // You might need to fetch these if they are not directly on the $record object
                        $userId = $record->user_id ?? null;
                        $departmentTitle = $record->department->title ?? ''; // Handle if department is not set
                        @endphp
                        @if($userId && function_exists('app') && app()->has('userAvatar') && app()->has('userName'))
                        {!! app('userAvatar')($userId) !!}
                        <span style="
                            margin-left: 8px;
                            font-size: 14px;
                            color: #2c3e50;
                            font-weight: bold;
                        ">
                            {{ app('userName')($userId) }} @if($departmentTitle) - {{ $departmentTitle }} @endif
                        </span>
                        @else
                        <span style="
                            font-size: 14px;
                            color: #7f8c8d; /* Softer color for placeholder text */
                            font-style: italic;
                        ">
                            Usuário não atribuído
                        </span>
                        @endif
                    </div>

                    <div style="                
                    margin: 8px 0;
                    background-color:rgba(240, 240, 240, 0.3);
                    padding: 8px;
                    border-radius: 6px;
                    border: 1px solid #e0e0e0;">
                        <div style="border-bottom: 1px solid #CCC;">

                            <p style="margin: 6px 0; font-weight: 600;">
                                {{$record->labor->title}}
                            </p>

                            @if(isset($record->description) && !empty(trim($record->description)))
                            <p style="margin: 6px 0; font-weight: 600;">
                                <span style="color: #555;">Descrição:</span>
                            </p>


                            <div style="border-radius: 6px; padding: 10px; margin-left: 10px; font-size: 13px; line-height: 1.5; color: #444;">
                                {!! htmlspecialchars($record->description) !!}
                            </div>
                            @endif
                        </div>

                        @if(isset($record->observations))
                        <div style="margin-top: 10px; padding-top: 5px; border-top: 1px solid #DDD;">
                            <h6 style="font-weight: bold; color: #444; margin-bottom: 8px; font-size: 13px;">
                                Observações da Mão de Obra:
                            </h6>
                            @foreach($record->observations as $observation)

                            <div style="background-color: rgba(230,230,230,0.5); border: 1px solid #ccc; padding: 8px 10px; border-radius: 5px; margin-bottom: 8px; font-size: 12px;">
                                <p style="font-weight: bold; margin: 0 0 4px 0; color: #333; font-size: 12.5px;">{{ $observation->title }}</p>
                                <p style="margin: 0; color: #555; line-height: 1.4;">{!! $observation->description !!}</p>
                                <div style="margin-top: 5px; font-size: x-small; color: #888;">
                                    <span> {{ app('userName')($observation->user_id) }} em: {{ \Carbon\Carbon::parse($observation->created_at)->format('d/m/Y - H:i') }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @if(isset($record->service->deadline))
                        <p style="font-size:x-small; font-weight: 600;">
                            <span>Prazo de entrega:</span>
                            <span style=" font-weight: bold;">{{ \Carbon\Carbon::parse($record->service->deadline)->format('d/m/Y') ?? 'N/A' }}</span>
                            <span style="font-size: 10px; color: #888;">({{ \Carbon\Carbon::parse($record->service->deadline)->diffForHumans() }})</span>
                        </p>
                        @endif

                        @if($record-> created_at)
                        <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                            <span style="font-weight: bold;">Incluído em:</span> {{\Carbon\Carbon::parse($record->created_at)->format('d/m/Y - H:i') ?? 'N/A'}}
                        </p>
                        @endif

                        @if($record->approvedAt)
                        <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                            <span style="font-weight: bold;">Aprovado em:</span> {{\Carbon\Carbon::parse($record->approvedAt)->format('d/m/Y - H:i') ?? 'N/A'}}
                        </p>
                        @endif

                        @if($record->startedAt)
                        <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                            <span style=" font-weight: bold;">Iniciado em:</span> {{\Carbon\Carbon::parse($record->starteddAt)->format('d/m/Y - H:i') ?? 'N/A'}}
                        </p>
                        @endif


                        @if($record->finishedAt)
                        <p style="font-size:x-small; margin-top: 6px; margin-left: 10px;">
                            <span style=" font-weight: bold;">Finalizado em:</span> {{\Carbon\Carbon::parse($record->finishedAt)->format('d/m/Y - H:i') ?? 'N/A'}}
                        </p>
                        @endif
                        <div style="margin-top: 10px; text-align: right;">
                            <button
                                type="button"
                                wire:click.stop="openCancelModal('{{ $record->getKey() }}')"
                                class="filament-button filament-button-size-sm filament-button-color-danger 
       px-3 py-1 text-xs font-medium rounded-md transition 
       hover:bg-danger-600 focus:outline-none focus:ring-2 focus:ring-danger-500"
                                aria-label="Cancelar Mão de Obra">
                                Cancelar Mão de Obra
                            </button>
                        </div>


                    </div>
                </div>

            </div>
        </div>
</div>