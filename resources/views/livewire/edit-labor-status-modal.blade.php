<div x-data="{ open: false }" @toggle-modal.window="open = !open">
    <div x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
        <div class="bg-white p-4 rounded">
            <h2>Editar Mão de Obra</h2>
            <input type="text" wire:model="labor.id">
            <!-- Outros campos de edição -->
            <button @click="open = false">Fechar</button>
        </div>
    </div>
</div>
