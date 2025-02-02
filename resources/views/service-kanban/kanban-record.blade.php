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
    style="background-color: #FFFFFF"
>

    <div style="
        padding: 12px;
        margin: 12px 0;
        font-family: Arial, sans-serif;
        line-height: 1.4;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 14px;
    ">
        <!-- Informações principais visíveis -->
        <h3 style="
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        ">
            Ordem #{{ $record->order->order_number ?? 'N/A' }}
        </h3>
        <p style="margin: 8px 0; color: #555;">
            <strong>Cliente:</strong> {{ $record->order->client->name ?? 'N/A' }}
        </p>
        <p style="margin: 8px 0; color: #555;">
            <strong>Peça:</strong> {{ $record->part->title ?? 'N/A' }}
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
                    background-color: #f0f0f0;
                    padding: 8px;
                    border-radius: 6px;
                ">
                    {!! $this->userAvatar = app('userAvatar')($record->department->user->id) !!}
                    <span style="
                        margin-left: 8px;
                        font-size: 14px;
                        color: #181918;
                        font-weight: bold;
                    ">
                        {{$this->userName = app('userName')($record->department->user->id)}}
                    </span>
                </div>
                <p style="margin: 8px 0; color: #555; font-size: small">
                    <strong>Mão de obras ({{$record->labor->count()}}):</strong>
                </p>
                @if($record->labor->count() > 0)
                    <ul style="
                        list-style-type: none;
                        padding: 0;
                        margin: 0;
                        border: 1px solid #e0e0e0;
                        border-radius: 6px;
                        background-color: #ffffff;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    ">
                        @foreach($record->labor as $getLabors)
                            <li style="
                                padding: 8px 12px;
                                border-bottom: 1px solid #e0e0e0;
                                color: #555;
                                font-size: 12px;
                            ">
                                <strong>{{ $this->laborTitle = app('laborTitle')($getLabors->pivot->labor_id) }}</strong> -
                                <span style="color: {{$getLabors->pivot->status === 'concluído' ? '#4caf50' : '#f44336'}};">
                                    {{$getLabors->pivot->status}}
                                </span>
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
