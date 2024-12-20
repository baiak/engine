<!--{{$impedimentId}}!-->
@if(session()->has('LogSuccess'))
    <div style="background-color: #7ec699; color: #1b1e21">
      <div class="flex justify-between items-center">
        <span><strong>{{ session('LogSuccess') }}</strong></span>
      </div>
    </div>
@endif
@if(session()->has('LogError'))
    <div style="background-color: #ef0543; color: #fafbfb">
        <div class="flex justify-between items-center">
            <span><strong>{{ session('LogError') }}</strong></span>
        </div>
    </div>
@endif


<form x-on:submit.prevent="$wire.setImpedimentFormId({{$impedimentId}})">

<div class="mb-4" style="font-size: x-small">
    <input type="hidden" value="{{$impedimentId}}" wire:model.defer="impedimentId">

        Escreva uma observação:<br />
        <input  wire:model.defer="observation" type="text" id="obervation" name="observation" class="p-1 m-3 border-b-white rounded-xl" style="font-size: x-small;  background-color: transparent;"/>

    </div>
    <div class="mt-3">
        <x-filament::input.wrapper>
            <x-slot name="prefix" style="font-size: x-small">
                Status:
            </x-slot>
            <x-filament::input.select class="text-xs w-32" wire:model.defer="selectedImpedimentStatus"
                                      style=" font-size: x-small; padding: 0.25rem;">

                @foreach ($enumOptions as $option)
                    <option value="{{ (string)$option }}">{{ $option }}</option>
                @endforeach

            </x-filament::input.select>

        </x-filament::input.wrapper>
        <x-filament::button type="submit">Ok</x-filament::button>
    </div>
</form>
