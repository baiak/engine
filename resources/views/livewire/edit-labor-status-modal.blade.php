<!-- resources/views/livewire/edit-labor-status-modal.blade.php -->
<div x-data="{ open: false }" @open-modal.window="open = true">
    <div x-show="open">
        <div class="modal">
            <!-- Conteúdo do modal -->
            <p>Modal aberto para o registro ID: {{ $recordId }}</p>
            <button @click="open = false">Fechar</button>
        </div>
    </div>
</div>
