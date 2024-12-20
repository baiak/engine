<div>
    <h3 style="font-weight: bold; font-size: 1.125rem; margin-bottom: 1rem;">Notificações</h3>
    <ul style="list-style-type: none; padding: 0;">
        @forelse ($notifications as $notification)
            <li style="padding: 1rem; background-color: #f7fafc; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); border-radius: 0.375rem; display: flex; justify-content: space-between; align-items: center;">
                <span>{{ $notification->data['message'] ?? 'Mensagem sem título' }}</span>
                <button
                    wire:click="markAsRead('{{ $notification->id }}')"
                    style="color: #3b82f6; text-decoration: underline; cursor: pointer;"
                >
                    Marcar como lida
                </button>
            </li>
        @empty
            <li style="color: #6b7280;">Sem notificações no momento.</li>
        @endforelse
    </ul>
</div>
