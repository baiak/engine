<div class="p-4">
    <div x-data="{ open: false }" style="display: flex; flex-direction: column;">
        <!-- Botão de notificações -->
        <h3
            @click="open = !open"
            style="color: #6b7280; font-weight: bold; margin-bottom: 1rem; cursor: pointer; display: flex; align-items: center;">
            Notificações
            @if($unreadCount > 0)
                <span style="background-color: #3b82f6; color: white; border-radius: 9999px; padding: 0.25rem 0.5rem; margin-left: 0.5rem; font-size: 0.875rem;">
                    {{ $unreadCount }}
                </span>
            @endif
        </h3>

        <!-- Lista de notificações -->
        <div
            x-show="open"
            style="display: flex; flex-direction: column; gap: 0.75rem; background-color: white; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); border-radius: 0.5rem; margin-top: 0.5rem; padding: 1rem; max-height: 16rem; overflow-y: auto;"
            x-transition>
            @forelse ($notifications as $notification)
                <div style="background-color: #3b3c3e; padding: 1rem; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.1); border-radius: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>{!! $notification->data['body'] ?? 'Mensagem sem título' !!}</span>
                    <button
                        wire:click="markAsRead('{{ $notification->id }}')"
                        style="color: #3b82f6; text-decoration: underline; cursor: pointer;">
                        Marcar como lida
                    </button>
                </div>
            @empty
                <div style="color: #9ca3af;">Sem notificações no momento.</div>
            @endforelse
        </div>
    </div>

    <!-- Modal com todas as notificações -->
    <x-filament::modal>
        <x-slot name="header">
            <h2 style="font-size: 1.125rem; font-weight: 600;">Notificações</h2>
        </x-slot>

        <x-slot name="trigger">
            <x-filament::button>
                Ver todos
            </x-filament::button>
        </x-slot>

        <div style="padding: 1rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Todas as notificações</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @forelse ($notifications as $notification)
                    <div style="background-color: #f9fafb; padding: 1rem; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.1); border-radius: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span>{!! $notification->data['body'] ?? 'Mensagem sem título' !!}</span>
                        <button
                            wire:click="markAsRead('{{ $notification->id }}')"
                            style="color: #3b82f6; text-decoration: under


                            line; cursor: pointer;">
                            Marcar como lida
                        </button>
                    </div>
                @empty
                    <div style="color: #9ca3af;">Sem notificações no momento.</div>
                @endforelse
            </div>
        </div>
    </x-filament::modal>
</div>
