<div>
    @if($loading)
        <span class="text-gray-500">
            <svg class="animate-spin inline h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Atualizando...
        </span>
    @else
        <div class="relative">
            <!-- Status atual -->
            <div wire:click="toggleDropdown" class="cursor-pointer" {!! $currentStatus ? $currentStatus->getStyle() : '' !!}>
                @if($currentStatus && method_exists($currentStatus, 'getIcon'))
                    <i class="{{ $currentStatus->getIcon() }} mr-1"></i>
                @endif
                {{ $currentStatus ? $currentStatus->getLabel() : $status }}
                <svg class="inline-block ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            <!-- Dropdown -->
            @if($showDropdown)
                <div class="absolute top-full left-0 z-50 mt-1 bg-white shadow rounded-md border border-gray-200" style="min-width: 150px;">
                    <ul class="py-1">
                        @foreach($statusOptions as $option)
                            <li wire:click="updateStatus('{{ $option->value }}')"
                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-xs"
                                {!! $option->getStyle() !!}>
                                {{ $option->getLabel() }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Backdrop para fechar o dropdown ao clicar fora -->
                <div class="fixed inset-0 z-40" wire:click="closeDropdown"></div>
            @endif
        </div>
    @endif
</div>
