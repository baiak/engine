<!-- Arquivo: resources/views/order-kanban/kanban-layout.blade.php -->
<x-filament::layouts.base :livewire="$livewire">
    <!-- Conteúdo principal do Kanban -->
    <div class="filament-kanban-main">
        {{ $slot }}
    </div>
    
    <!-- Incluir o componente do modal aqui -->
    @livewire('add-service-modal')
</x-filament::layouts.base>