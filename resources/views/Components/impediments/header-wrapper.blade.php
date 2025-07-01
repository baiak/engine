@php
    $recordData = $getRecord(); // Acessa o registro atual do Infolist
@endphp

{{--
    Renderize seu componente Livewire aqui.
    O nome do parâmetro ('record' no exemplo abaixo) deve corresponder
    ao nome do parâmetro no método mount() do seu componente Livewire.
--}}

{{-- Passar o objeto $record inteiro  --}}
<livewire:kanbanImpediments.impediment-header :record="$recordData" :key="'header-lw-' . $recordData->id" />
