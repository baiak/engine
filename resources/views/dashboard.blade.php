<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-text-gray-100">

                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Olá, {{ Auth::user()->name }}!
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg">
                            <h4 class="text-xl font-bold text-blue-800 dark:text-blue-200">Serviços Pendentes</h4>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                Você tem <span class="font-bold text-2xl">{{ $servicesAwaitingLaborCount }}</span> serviço(s) aguardando o lançamento de mão de obra.
                            </p>
                            <a href="{{ route('filament.admin.resources.services.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                Ir para Serviços
                            </a>
                        </div>

                        @if($unresolvedImpediments->isNotEmpty())
                            <div class="bg-red-100 dark:bg-red-900 p-6 rounded-lg">
                                <h4 class="text-xl font-bold text-red-800 dark:text-red-200">Impedimentos em Aberto</h4>
                                <p class="mt-2 text-gray-600 dark:text-gray-300">
                                    Você possui {{ $unresolvedImpediments->count() }} impedimento(s) que requer(em) sua atenção.
                                </p>

                                <ul class="mt-4 space-y-2">
                                    @foreach($unresolvedImpediments as $impediment)
                                        <li class="text-sm">
                                           <strong>OS {{ $impediment->serviceLabor->service->order->order_number ?? 'N/A' }}:</strong> {{ $impediment->reason }} (Aberto por: {{ $impediment->complainantUser->name ?? 'N/A' }})
                                        </li>
                                    @endforeach
                                </ul>

                                <a href="{{ route('filament.admin.resources.services.index') }}" class="mt-4 inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                    Ver Serviços com Impedimentos
                                </a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>