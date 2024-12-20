@extends('layouts.app')
<div xmlns:x-filament="http://www.w3.org/1999/html"><!--{{$serviceLaborId}}!-->
    @php
        use App\Models\LaborImpediment;
        use App\Enums\TypeOfLaborImpedimentStatus;
        $listImpedimentsLogs = LaborImpediment::loadLogs($serviceLaborId);
        $impediments =  LaborImpediment::listImpediments($serviceLaborId);
        $enumOptions = \App\Enums\TypeOfLaborImpedimentStatus::getValues();
        $avatar = app('userAvatar');
        $name = app('userName');
        $impedimentCounter = 1;

    @endphp
    @if($impediments->isEmpty())
        Sem impedimentos cadastrados
    @endif

    <ul>
        @foreach($impediments as $impedimentsData)
            <li class="mt-2 mb-2 p-3" x-data="{ open: false }">
                <button x-on:click="open = ! open" style="font-size: large; display: flex; justify-content: start; align-items: center;" class="d-flex center">
                    Impedimento {{$impedimentCounter++}}
                    <!-- Seta apontando para a direita -->
                    <x-filament::icon
                        icon="heroicon-c-chevron-right"
                        class="h-4 w-4 "
                        x-show="!open"
                    />

                    <!-- Seta apontando para baixo -->
                    <x-filament::icon
                        icon="heroicon-c-chevron-down"
                        class="h-4 w-4 "
                        x-show="open"
                    />
                    <x-filament::badge color="info" size="sm">
                        {{$impedimentsData->status}}
                    </x-filament::badge>
                </button>
                <div x-show="open" x-transition>
                    <x-filament::section>
                        <x-slot name="heading">
                            {!! $avatar($impedimentsData->complainant_id) !!}
                        </x-slot>
                        <x-slot name="headerEnd">
                            <div>
                    <span style="font-weight: bolder" class="mb-2">
                        {!! $name($impedimentsData->complainant_id) !!}
                    </span><br/>
                                <span style="font-size: x-small" class="mt-3">
                            {{$impedimentsData->created_at->format('d/m/Y H:i')}}
                        </span><br/>
                                <span style="font-size: x-small" class="mb-3">
                            para: {!!$name($impedimentsData->complained_id)!!}
                        </span>
                                <br/>
                                <span style="font-size: x-small;" class="mt-3">
                              impedimento {{ $impedimentsData->status }}
                            </span>
                            </div>
                        </x-slot>


                        <!--box descricao do impedimento !-->
                        <div class="rounded-xl border mb-4" style="border-color: #4b5563">
                            <!--cabecalho!-->
                            <div class="p-2" style="width: 100%; border-bottom: #4b5563 solid 1px; font-size: x-small">
                                Motivo do impedimento:
                            </div>
                            <!--/cabecalho!-->
                            <div class="mb-4 p-3 w-full h-full text-center rounded-xl" style="font-size: small">
                                {{$impedimentsData->reason}}
                            </div>

                            <!--footer !-->
                            <div class="d-flex content-center p-1" style="width:100%; border-top: #9ca3af solid 1px; text-align: left; font-size: x-small">
                                <x-filament::modal id="impedimentModal" sticky-header>
                                    <x-slot name="trigger">
                                        <button class="p-1 rounded-lg relative"
                                                style="margin-top:5px; font-size:x-small; background-color: #359a35; color: #d6d6d6;"
                                                x-data="{ showTooltip: false }"
                                                @mouseenter="showTooltip = true"
                                                @mouseleave="showTooltip = false">
                                            <!-- Ícone -->
                                            <x-hugeicons-bubble-chat-add class="w-4 h-4"/>

                                            <!-- Tooltip -->
                                            <span x-show="showTooltip" x-transition
                                                  style="position: absolute; top: 100%; left: 50%; transform: translateX(-50%); background-color: #333333; color: #ffffff; padding: 5px 10px; border-radius: 5px; font-size: 0.75rem; white-space: nowrap; opacity: 0.9; margin-top: 5px;">
                                            Adicionar resposta
                                        </span>
                                        </button>
                                    </x-slot>

                                    <x-slot name="heading">
                                        Resolver impedimento
                                    </x-slot>

                                    <x-slot name="description">
                                        {{$impedimentsData->reason}}
                                    </x-slot>

                                    <x-filament::fieldset>
                                        @include('livewire.impediments.impediment-response-form', ['impedimentId'=>$impedimentsData->id])
                                    </x-filament::fieldset>
                                </x-filament::modal>
                            </div>
                            <!--/footer!-->

                        </div>
                        <!--/box descricao do impedimento !-->

                        @if($impedimentsData->logs)
                            <!--carregar logs!-->
                            <ul>
                                @foreach($impedimentsData->logs as $logItem)
                                    <li class="mt-4 mb-4">
                                        <div class="bg-black border-gray-600 shadow-md rounded-xl pt-0 mb-4"
                                             style="border: 1px #4b5563; background-color: #4b5563">
                                            <div
                                                class="flex content-between text-sm text-gray-600 mb-2 p-1 rounded-t-xl"
                                                style="font-size: x-small; background-color: #2b2f32; color: #9ca3af; width: 100%; text-align: center">
                                                <x-filament::icon
                                                    icon="heroicon-o-chat-bubble-left"
                                                    class="h-4 w-4 mr-3"
                                                    style="margin-top:4px; margin-right:8px; margin-left:7px"
                                                />

                                                <span style="margin-right: 6px; padding-bottom: 5px; font-weight: bold">
                                                {!! $name($impedimentsData->complained_id) !!}
                                            </span>
                                            </div>

                                            <div class="flex items-start p-4">
                                                <div class="avatar mr-3">
                                                    {!! $avatar($impedimentsData->complained_id) !!}
                                                </div>

                                                <div class="text text-gray-800 p-2">
                                                    {{ $logItem['observation'] }}
                                                </div>

                                            </div>
                                            <div class="date p-2 rounded-b-xl"
                                                 style="font-size: x-small; background-color: #2b2f32; color: #9ca3af; width: 100%; text-align: center">
                                                em: {{ $logItem['date'] }}</div>
                                        </div>

                                    </li>
                                @endforeach

                            </ul>
                        @endif
                        <!--carregar logs fim !-->

                    </x-filament::section>
                </div>
            </li>
        @endforeach
    </ul>
</div>
