@php
    $mensagem = $this->getAdditionalData();
@endphp

<x-filament-panels::page>
    <div>{!! $mensagem !!}</div>
    <div x-data wire:ignore.self class="md:flex overflow-x-auto overflow-y-hidden gap-2 pb-4">
        @foreach($statuses as $status)
            @include(static::$statusView, ['status' => $status])
        @endforeach

        <div wire:ignore>
            @include(static::$scriptsView)
        </div>
    </div>

    @unless($disableEditModal)
        <x-filament-kanban::edit-record-modal/>
    @endunless
</x-filament-panels::page>