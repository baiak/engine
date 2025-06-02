<div>
    <!--header!-->
    <div>
        @php
        //dd($registro);
        @endphp

        <span>Peça:</span><span>{{ $registro->serviceLabor->service->part->title }}</span><br />
        <span>Mao de obra:</span><span>{{$registro->serviceLabor->labor->title}}</span><br />
        <span>Aberto por:</span><span>{!! app('userName')($registro->complainant_id) !!}</span><br />
        <span>Razao do impedimento:</span><span>{{ $registro->reason }}</span><br />
        <span>Status:</span><span>{{ $registro->status }}</span><br />
        <span>Data de abertura:</span><span>{{ $registro->created_at->format('d/m/Y') }}</span><br />
        <!-- form para resposta!-->
        <div x-data="{ openForm: false }">
            <button @click="openForm = !openForm" class="btn btn-primary flex items-center justify-between gap-2">
                Responder
                <svg :class="{ 'rotate-180': openForm }" class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="openForm" class="mt-2">
                <form wire:submit.prevent="submitResponse" class="space-y-4">
                    <div>
                        <label for="response" class="block text-sm font-medium text-gray-700">Resposta</label>
                        <textarea id="response" wire:model.defer="response" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        @error('response') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-success">Enviar Resposta</button>
                </form>

            </div>
            <!-- form para resposta !-->
        </div>
        <!--/header!-->

        <!-- interacoes !-->
        <ul>
            @foreach($registro->logs as $logItem)
            <li class="mt-4 mb-4">
                <div class="bg-black border-gray-600 shadow-md rounded-xl pt-0 mb-4"
                    style="border: 1px #4b5563; background-color: #4b5563">
                    <div
                        class="flex content-between text-sm text-gray-600 mb-2 p-1 rounded-t-xl"
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

                        <div class="text text-gray-800 p-2">
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