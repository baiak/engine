<div class="m-y-3">
    <table class="!w-full !min-w-max !table-auto !text-left !border !border-zinc-700 !rounded-sm p-3  m-3">
        <thead>
        <tr>
            <th class="border-b !border-zinc-700 bg-blue-gray-50/50 p-3">
                <p class="block antialiased font-sans text-sm text-blue-gray-900 font-small leading-none opacity-70 text-xs font-semibold ">
                    Mao de obra
                </p>
            </th>
            <th class="border-b !border-zinc-700 bg-blue-gray-50/50 p-3">
                <p class="block antialiased font-sans text-sm text-blue-gray-900 font-small leading-none opacity-70 text-xs font-semibold text-center ">
                    Status
                </p>
            </th>
            <th class="border-b !border-zinc-700 bg-blue-gray-50/50 p-3" colspan="2">

            </th>
        </tr>
        </thead>
        <tbody>
        @if($getState() != "")
            @foreach ($getState() as $item)
                <tr style="border-bottom: 1px solid; border-color: #3f3f46;" wire:key="{{ $item->id }}">
                    <td class="p-2" >
                        <div class="flex items-left gap-2">
                          <span class="!block !antialiased !font-sans  !text-sm-left !leading-normal !text-blue-gray-900 !font-bold" style="font-size:small">
                               {{($item->title)}}
                          </span>
                        </div>
                    </td>
                    <td class="p-2" style="font-size: x-small">

                        <div x-data="{ expanded: false }">
                            <small>
                               <button @click="expanded = ! expanded" class="inline-flex items-center px-1 py-1 pe-3 rounded-full"
                                  @switch($item->pivot->status)
                                      @case('Aguardando aprovacao')
                                          style="background-color:#fef9c3;color: #854d0e; font-size: x-small"
                                  @break
                                  @case('Aprovado')
                                      style="background-color:#d1fae5; color: #065f46; font-size: x-small"
                                  @break
                                  @case('Rejeitado')
                                      style="background-color:#f3f4f6; color: #1f2937; font-size: x-small"
                                  @break
                                  @case('Em Andamento')
                                      style="background-color:#dbeafe; color: #1e40af; font-size: x-small"
                                  @break
                                  @case('Impedido')
                                      style="background-color:#fee2e2; color: #991b1b; font-size: x-small"
                                  @break
                                  @case('finalizado')
                                      style="background-color:#f3f4f6; color: #1f2937;font-size: x-small"
                                  @break
                                  @default
                                      style="background-color:#f3f4f6; color: #1f2937; font-size: x-small"
                    @endswitch
                               >
                                 <!-- Icones svg!-->
                                 @switch($item->pivot->status)
                                    @case('Aguardando aprovacao')
                                        <x-filament::icon
                                            icon="pepicon-hourglass-circle"
                                            class="h-4 w-4 mx-1 "
                                        />
                                        @break
                                    @case('Aprovado')
                                        <x-filament::icon
                                            icon="heroicon-s-check"
                                            class="h-4 w-4 mx-1 "
                                        />
                                        @break
                                    @case('Rejeitado')
                                        <x-filament::icon
                                            icon="uiw-dislike-o"
                                            class="h-4 w-4 mx-1 "
                                        />
                                        @break
                                    @case('Em Andamento')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" class="mx-1 h-4 w-4 size-6"
                                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                        @break
                                    @case('Impedido')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                             class="size-6 mx-1 h-4 w-4 text-red-500 dark:text-red-400"
                                             viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                                        @break
                                    @case('finalizado')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                             class="size-6 mx-1 h-4 w-4 mx-3 text-blue-500" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                        @break
                                @endswitch
                                {{ $item->pivot->status }}

                                   <!-- Seta apontando para a direita -->
                                   <svg x-show="!expanded" style="color: #636b6f" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        class="w-6 h-6" viewBox="0 0 24 24" title="Clique para alterar o status">
                                       <path d="M9 5l7 7-7 7"></path>
                                   </svg>
                                   <!-- Seta apontando para baixo -->
                                   <svg x-show="expanded" style="color: #636b6f" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        class="w-6 h-6" viewBox="0 0 24 24" title="Clique para alterar o status">
                                       <path d="M19 9l-7 7-7-7"></path>
                                   </svg>
                               </button>
                            </small>

                            <div x-show="expanded" class="p-3 mt-3 border border-gray-600 rounded " x-collapse>
                                <!-- Formulário com o elemento select para os status -->
                            <form wire:submit.prevent="updateStatus"
                                  tyle="display: flex; align-items: center; gap: 4px;">
                                <select wire:model="selectedStatus" class="small-select rounded" style="color: #9ca3af; background-color: transparent; font-size: small">
                                    <option value="" disabled selected>Alterar status</option>
                                    @foreach (\App\Enums\TypeOfLaborStatus::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                </select>
                                <p>
                                    <button type="submit" style="background-color: #0062cc; margin-top:3px; color: white; " class="p-1 bg-gray-700 rounded small-button border-gray-600">Salvar</button>
                                </p>
                              </form>
                            </div>
                        </div>
                    </td>
                </tr>

            @endforeach
        @endif
        </tbody>
    </table>
</div>

