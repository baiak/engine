<!-- {{$service_labor_id}} !-->

<div style="font-size: x-small" class="mb-3 p-2 rounded rounded-md" >
    @if (session()->has('success'))
        <div
             x-init="setTimeout(() => show = false, 4000)"
             x-show="@entangle('showError')"
             x-transition:leave="transition ease-in duration-1000"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="p-3 rounded relative bg-green-500 text-white"
             role="alert"
             style="background-color: #7ec699; color: #1b1e21">
            <div class="flex justify-between items-center">
                <span><strong>{{ session('success') }}</strong></span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div
             x-init="setTimeout(() => show = false, 4000)"
             x-show="@entangle('showError')"
             x-transition:leave="transition ease-in duration-1000"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="p-3 rounded relative bg-red-500 text-white"
             role="alert"
             style="background-color: #ef0543; color: #1b1e21">
            <div class="flex justify-between items-center">
                <span><strong>{{ session('error') }}</strong></span>
            </div>
        </div>
    @endif

        <form x-data="{ reason: '', responsible: ''}" x-on:submit.prevent="$wire.addServiceLaborIdImpediment({{$service_labor_id}})">
            <div class="m-2  mt-2 p-3">
                <input type="hidden" wire:model="ServiceLaborId" value="{{$service_labor_id}}">
                <label for="impediment_reason" class="block font-bold mb-2 m-4">
                    Motivo do Impedimento:
                </label>
                <textarea x-model="reason"
                          wire:model.defer="impediment_reason"
                          id="impediment_reason"
                          class="form-textarea w-full mb-2"
                          style="color: #171a1d; font-size: x-small">
               </textarea>
                @error("impediment_reason")
                <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror


            <div class="mt-3">
                <label for="responsible_user_id" class="block font-bold mb-2">
                    Usuário Responsável:
                </label>
              <x-filament::input.select x-model="responsible"
                        wire:model.defer="responsible_user_id"
                        id="responsible_user_id"
                        class="border border-gray-600 form-select w-full"
                        style="font-size: x-small">
                    <option value="">Selecione</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
              </x-filament::input.select>
                @error("responsible_user_id")
                <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
                <x-filament::button
                class="mt-2"
                type="submit"
                icon="heroicon-m-plus-circle"
                color="info"
                size="xs">
                Adicionar impedimento
                </x-filament::button>
            </div>
            </div>
        </form>
</div>
