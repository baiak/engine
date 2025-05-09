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
        background-color:hsl(0, 0.00%, 100.00%);
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
                            {{ app('userName')($userId) }}
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
                        line-height: 1.4;
                    ">

                        @foreach($record->labor as $getLabors)
                         <li class="labor-item flex items-center justify-between" style="border-bottom:  #cacfd2  solid 1px; padding:4px 3px 6px;">
                           <div><b>{{$getLabors->title}}</b></div>  
                           <div>{{$getLabors->pivot->status}}</div>
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