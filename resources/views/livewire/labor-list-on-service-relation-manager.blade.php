<div class="m-3" style="margin-top: 15px; width: 95%">
    @if (!empty($getState()))
        <!-- Listagem de Mão de Obra -->
        <x-filament::section>
            @forelse ($getState() as $index => $var)
                <div class="rounded-xl border mt-2 p-3 shadow-sm">
                    <!-- Conteúdo da mão de obra -->
                    <div class="flex justify-between items-start">
                        <div class="flex-grow">
                            @include('livewire.labor-list', ['var' => $var, 'index' => $index])
                        </div>


                    </div>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-gray-500">Nenhuma mão de obra encontrada</p>
                </div>
            @endforelse
        </x-filament::section>
    @else
        <div class="text-center py-10 border  rounded-lg my-4">
            <p class="text-gray-500">Sem dados disponíveis para exibição</p>
        </div>
    @endif
</div>
