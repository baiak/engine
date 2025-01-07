<div x-data="{ open: false }">
    <h3
        @click="open = !open"
        style="color: #9ca3af; font-weight: bold; margin-bottom: 1rem; cursor: pointer;">
        Notificações
        @if($unreadCount > 0)

            <span style="background-color: #3b82f6; color: white; border-radius: 9999px; padding: 0.2rem 0.5rem; margin-left: 0.5rem; font-size: small;">

                {{ $unreadCount }}

            </span>

        @endif

    </h3>
    <ul
        x-show="open"
        style="list-style-type: none; padding: 0; display: none; margin-top: 20px"
        x-transition

    >
        @forelse ($notifications as $notification)
            <li style="color: #1b1e21; padding: 1rem; background-color: #f7fafc; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); border-radius: 0.375rem; display: flex; justify-content: space-between; align-items: center;">
                <span>{!!  $notification->data['body'] ?? 'Mensagem sem título' !!}</span>
                <button
                    wire:click="markAsRead('{{ $notification->id }}')"
                    style="color: #3b82f6; text-decoration: underline; cursor: pointer;">
                    Marcar como lida
                </button>
            </li>
        @empty
            <li style="color: #6b7280;">Sem notificações no momento.</li>
        @endforelse
    </ul>
</div>
