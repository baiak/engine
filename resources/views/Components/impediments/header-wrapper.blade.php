@php
    $recordData = $getRecord(); // Acessa o registro atual do Infolist
@endphp

{{--
    Renderize seu componente Livewire aqui.
    O nome do parâmetro ('record' no exemplo abaixo) deve corresponder
    ao nome do parâmetro no método mount() do seu componente Livewire.
--}}

{{-- Opção 1: Passar o objeto $record inteiro (se o seu componente Livewire espera o objeto) --}}
<livewire:kanbanImpediments.impediment-header :record="$recordData" :key="'header-lw-' . $recordData->id" />

{{-- Opção 2: Passar apenas o ID do registro (se o seu componente Livewire espera o ID e busca o modelo) --}}
{{-- <livewire:meu-cabecalho-livewire :record="$recordData->id" :key="'header-lw-' . $recordData->id" /> --}}

{{-- Opção 3: Passar atributos específicos (se o seu componente Livewire espera atributos individuais) --}}
{{--
<livewire:meu-cabecalho-livewire
    :reclamanteNome="$recordData->reclamante->nome ?? null"
    :statusProcesso="$recordData->status"
    :key="'header-lw-' . $recordData->id"
/>
--}}