<div class="m-y-3" xmlns:x-filament="http://www.w3.org/1999/html">
    @if($getState() != "")

        <div x-data="{ tab: 'tab1' }">
            <x-filament::tabs label="Content tabs">
                <x-filament::tabs.item @click="tab = 'tab1'" :alpine-active="'tab === \'tab1\''">
                    Mão de obra
                </x-filament::tabs.item>

                <x-filament::tabs.item @click="tab = 'tab2'" :alpine-active="'tab === \'tab2\''">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>Impedimentos</span>
                        <x-filament::badge color="info">
                            5
                        </x-filament::badge>
                    </div>
                </x-filament::tabs.item>

            </x-filament::tabs>

            <div>
                <!--listagem mao de obra!-->
                <div x-show="tab === 'tab1'">
                    <x-filament::section>
                        @foreach($getState() as $var)
                            <div class="rounded-xl border mt-2 p-3" style="border-color: #9ca3af">
                                @include('livewire.labor-list', ['var' => $var])
                            </div>
                        @endforeach
                    </x-filament::section>
                </div>
                <!--listagem mao de obra!-->

                <!-- Sessão de Impedimento -->
                <div x-show="tab === 'tab2'">
                    @foreach($getState() as $varImpediment)
                        <div style="border: #9ca3af 1px solid; border-radius: 10px; padding: 15px; margin: 10px;">
                        <!-- Cabeçalho -->
                        <div style="display: flex; align-items: start; justify-content: space-between; margin-bottom: 15px;">
                            <h3 style="margin: 0; font-size: 12px; font-weight: bold; margin-bottom: 3px;">Impedimentos</h3>
                            <x-filament::modal>
                                <x-slot name="trigger">
                                    <x-filament::button
                                        style="font-size: x-small; padding: 2px 2px; background-color: #3b82f6; color: white; border-radius: 5px; border: none; cursor: pointer;">
                                        + Adicionar
                                    </x-filament::button>
                                </x-slot>
                                <!-- Conteúdo do Modal -->
                                <div style="border: 1px solid #4b5563; border-radius: 10px; padding: 15px; margin-top: 10px;">
                                    @include('livewire.impediments.labor-impediment-form', [
                                        'service_labor_id' => $varImpediment->pivot->id,
                                        'users' => \App\Models\User::all(),
                                    ])
                                </div>
                            </x-filament::modal>
                        </div>

                        <!-- Badge e botão Ver -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                            <x-filament::badge color="warning">
                                @include('livewire.impediments.Impediment-Counter', ['service_labor_id' => $varImpediment->pivot->id])
                            </x-filament::badge>
                            <x-filament::modal>
                                <x-slot name="trigger">
                                    <x-filament::button
                                        style="font-size: x-small; padding: 5px 10px; background-color: #6b7280; color: white; border-radius: 5px; border: none; cursor: pointer;">
                                        Ver
                                    </x-filament::button>
                                </x-slot>
                                <!-- Conteúdo do Modal -->
                                <div style="padding: 15px; margin-top: 10px; border: 1px solid #d1d5db; border-radius: 10px;">
                                    @include('livewire.impediments.impediment-list', ['serviceLaborId' => $varImpediment->pivot->id])
                                </div>
                            </x-filament::modal>
                        </div>
                    </div>
                    @endforeach

                </div>
                <!-- Fim da Sessão de Impedimento -->

            </div>
        </div>
    @endif
</div>

