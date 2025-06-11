<div>
    <div class="p-4">
        <div x-data="{ open: false }" class="relative">
            <div
                @click="open = !open"
                @click.away="open = false"
                class="flex cursor-pointer items-center text-gray-600 transition-colors duration-200 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">

                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>

                <h3 class="ml-2 text-lg font-semibold">
                    Notificações
                </h3>

                @if($unreadCount > 0)
                <span style="margin-left:5px;"  class="mx-2 flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">
                    {{ $unreadCount }}
                </span>
                @endif
            </div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="absolute z-10 mt-2 w-80 rounded-lg bg-white shadow-xl dark:bg-gray-800"
                style="display: none;">

                <div class="max-h-96 overflow-y-auto p-2">
                    <div class="flex flex-col gap-2">
                        @forelse ($notifications->take(5) as $notification) {{-- Mostra apenas as 5 mais recentes --}}
                        <div class="group flex items-start justify-between gap-3 rounded-lg p-3 transition-colors hover:bg-gray-500 dark:hover:bg-gray-700">
                            <div class="text-sm text-gray-400 dark:text-gray-400">
                                {!! $notification->data['body'] ?? 'Mensagem sem título' !!}
                                <div class="mt-1 text-xs text-gray-400 dark:text-gray-900">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <button
                                wire:click="markAsRead('{{ $notification->id }}')"
                                title="Marcar como lida"
                                class="flex-shrink-0 text-gray-400 opacity-0 transition-opacity group-hover:opacity-100 hover:text-primary-500">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        @empty
                        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Sem notificações novas.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-gray-900 p-2 text-center dark:border-gray-700 dark:bg-gray-800/50">
                    <x-filament::button wire:click="$dispatch('open-modal', { id: 'todas-notificacoes' })" size="sm">
                        Ver todas
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>

    <x-filament::modal id="todas-notificacoes">
        <x-slot name="header">
            <h2 class="text-lg font-semibold">Todas as Notificações</h2>
        </x-slot>

        {{-- O conteúdo do modal usa a mesma estrutura do dropdown para consistência --}}
        <div class="max-h-[70vh] overflow-y-auto p-2">
            <div class="flex flex-col gap-2">
                @forelse ($notifications as $notification)
                <div class="group flex items-start justify-between gap-3 rounded-lg p-3 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        {!! $notification->data['body'] ?? 'Mensagem sem título' !!}
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @if (is_null($notification->read_at))
                    <button
                        wire:click="markAsRead('{{ $notification->id }}')"
                        title="Marcar como lida"
                        class="flex-shrink-0 text-gray-400 transition-colors hover:text-primary-500">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    @endif
                </div>
                @empty
                <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    Nenhuma notificação encontrada.
                </div>
                @endforelse
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'todas-notificacoes' })">
                Fechar
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>