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
>
    {{ $record->{static::$recordTitleAttribute} }}

    <div style="background-color: #fff; border-radius: 10px; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2); padding: 16px; margin-bottom: 12px; font-family: 'Arial', sans-serif; font-size: 14px; line-height: 1.6; border-left: 6px solid #007bff; max-width: 350px; color: #222;">
        <span style="font-size: medium; font-weight: bold; display: flex; align-items: center; gap: 4px;">
            <x-heroicon-o-clipboard-document class="w-4 h-4 text-gray-500" />
            Ordem # {{$record->getOrderDetails->order_number}}
        </span>
        <span style="margin: 0 0 10px; font-size: 10px; color: #0056b3; font-weight: bold;">
            <span style="color: #555; font-size: small; display: flex; align-items: center; gap: 4px; padding-left:10px">
                <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-500" />
                {{$record->getOrderDetails->client->name}} - {{$record->vehicle_details}}
            </span>
        </span>

        <span style="margin: 6px 0; font-weight: 600;color: #555; display: flex; align-items:center; gap:4px; padding-left: 10px">
             <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-500" />
            {{$record->service->part->title}}
        </span>

        <p style="margin: 6px 0; font-weight: 600;">
            {{$record->labor->title}}
        </p>


        <p style="margin: 6px 0; font-weight: 600;">
            <span style="color: #555;">Prazo de entrega:</span>
            <span style="color: #d9534f; font-weight: bold;">{{$record->service->deadline}}</span>
        </p>


        <p style="margin: 6px 0; font-weight: 600;">
            <span style="color: #555;">Descrição:</span>
        </p>

        <div style="background-color: #f8f9fa; border-radius: 6px; padding: 10px; margin-left: 10px; font-size: 13px; line-height: 1.5; color: #444;">
            {!! $record->description !!}
        </div>

        <span style="background-color: #f8f9fa; border-radius: 6px; padding: 10px; margin-left: 10px; font-size: 10px; line-height: 1.5; color: #444;">Incluído em: {{$record->created_at}}</span>


        @if($record->approvedAt)
            <p style="margin: 6px 0; font-weight: 600;">
                <span style="color: #28a745; font-weight: bold;">Aprovado em:</span> {{$record->approvedAt}}
            </p>
        @endif


    </div>





</div>
