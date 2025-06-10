@extends('layouts.app')

<div>
    <!--header!-->
    <div>
        <div style="background-color:rgb(31, 31, 32); padding: 10px; border-radius: 8px; margin-bottom: 20px;">
            <span>Razão do impedimento:</span><br />
            <div class="bg-black border-gray-600 shadow-md rounded-xl pt-0 mb-4"
                style="border: 1px #4b5563; background-color: #4b5563">
                <div
                    class="flex content-between text-sm text-white mb-2 p-1 rounded-t-xl"
                    style="font-size: x-small; background-color: #2b2f32; color: #9ca3af; width: 100%; text-align: center">
                    <x-filament::icon
                        icon="heroicon-o-chat-bubble-left"
                        class="h-4 w-4 mr-3"
                        style="margin-top:4px; margin-right:8px; margin-left:7px" />

                    <span style="margin-right: 6px; padding-bottom: 5px; font-weight: bold">
                        {!! app('userName')($registro->complainant_id) !!}
                    </span>
                </div>

                <div class="flex items-start p-4">
                    <div class="avatar mr-3">
                        {!! app('userAvatar')($registro->complainant_id) !!}
                    </div>

                    <div class="text text-white p-2">
                        {{ $registro->reason }}
                    </div>

                </div>
                <div class="date p-2 rounded-b-xl"
                    style="font-size: x-small; background-color: #2b2f32; color: #9ca3af; width: 100%;">
                    em: {{ $registro->created_at }}</div>
            </div>

            <!-- form para resposta!-->
            <div x-data="{ openForm: false, isLoading:false, responseText: '', selectedStatus: @js($status) }" style=" padding: 10px; margin-left:20px; border-radius: 8px; font-size:10px; color:#2b2f32;">
                <x-filament::button @click="openForm = !openForm" color="gray" icon="heroicon-o-chat-bubble-left-right" icon-position="before">
                    <span class="flex items-center justify-between gap-2">Responder
                        <svg :class="{ 'rotate-180': openForm }" class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </x-filament::button>
                <div x-show="openForm" class="mt-2">
                    <form wire:submit.prevent="
                    isLoading = true;
                        // Atualiza as propriedades do Livewire com os valores do Alpine
                        await $wire.set('response', responseText);
                        await $wire.set('status', selectedStatus);
                        // Chama a action do Livewire
                        try {
                            await $wire.submitResponse();
                            responseText = ''; // Limpa o campo no frontend
                            // selectedStatus já deve ser atualizado pelo binding do Livewire se $this->status mudar no PHP
                            // openForm = false; // Opcional: fechar o formulário após o sucesso
                        } catch (error) {
                            console.error('Erro ao submeter:', error);
                            alert('Ocorreu um erro ao enviar a resposta. Verifique o console para detalhes.');
                        } finally {
                            isLoading = false;
                        }" class="space-y-4">
                        <div>
                            <label for="alpine-response" class="block text-sm font-medium text-white">Resposta</label>
                            <textarea id="alpine-response" x-model="responseText" rows="3"
                                class="mt-1 block w-[70%] rounded-lg border shadow-sm transition duration-75 text-sm leading-6 py-1.5 px-3 border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/50"
                                :disabled="isLoading"></textarea>
                            @if($errors->has('response'))
                            <span class="text-red-500 text-xs">{{ $errors->first('response') }}</span>
                            @endif
                        </div>

                        <label for="response" class="block text-sm font-medium text-white">Status</label>
                        <div class="flex items-center space-x-4">
                            <x-filament::input.wrapper>
                                <select x-model="selectedStatus" :disabled="isLoading"
                                    class="block w-full rounded-lg border shadow-sm transition duration-75 text-sm leading-6 py-1.5 px-3 border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/50">
                                    @foreach(App\Enums\TypeOfLaborImpedimentStatus::options() as $value)
                                    <option value="{{ $value }}">{{ ucfirst($value) }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('status'))
                                <span class="text-red-500 text-xs">{{ $errors->first('status') }}</span>
                                @endif
                            </x-filament::input.wrapper>

                            <x-filament::button type="submit" class="btn btn-success">Enviar Resposta</x-filament::button>
                        </div>
                    </form>

                </div>
                <!-- form para resposta !-->
            </div>
        </div>
        <!--/header!-->

        <!-- interacoes !-->
        <ul>
            @foreach($registro->logs as $logItem)
            <li class="mt-4 mb-4">
                <div class="bg-black border-gray-600 shadow-md rounded-xl pt-0 mb-4"
                    style="border: 1px #4b5563; background-color: #4b5563">
                    <div
                        class="flex content-between text-sm text-white mb-2 p-1 rounded-t-xl"
                        style="font-size: x-small; background-color: #2b2f32; color: #9ca3af; width: 100%; text-align: center">
                        <x-filament::icon
                            icon="heroicon-o-chat-bubble-left"
                            class="h-4 w-4 mr-3"
                            style="margin-top:4px; margin-right:8px; margin-left:7px" />

                        <span style="margin-right: 6px; padding-bottom: 5px; font-weight: bold">
                            {!! app('userName')($registro->complained_id) !!}
                        </span>
                    </div>

                    <div class="flex items-start p-4">
                        <div class="avatar mr-3">
                            {!! app('userAvatar')($registro->complained_id) !!}
                        </div>

                        <div class="text text-white p-2">
                            {{ $logItem['observation'] }}
                        </div>

                    </div>
                    <div class="date p-2 rounded-b-xl"
                        style="font-size: x-small; background-color: #2b2f32; color: #9ca3af; width: 100%;">
                        em: {{ $logItem['date'] }}</div>
                </div>

            </li>
            @endforeach
        </ul>
        <!--/ Interações !-->
    </div>
