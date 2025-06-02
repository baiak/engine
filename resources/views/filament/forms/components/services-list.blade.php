<div class="space-y-4">
    @if(count($services) > 0)
        <div class="overflow-hidden bg-white shadow sm:rounded-md">
            <ul role="list" class="divide-y divide-gray-200">
                @foreach($services as $service)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="truncate">
                                <div class="flex text-sm">
                                    <p class="font-medium text-indigo-600 truncate">{{ $service->department->title ?? 'Sem departamento' }}</p>
                                    <p class="ml-1 text-gray-500">
                                        - {{ $service->part->title ?? 'Sem peça específica' }}
                                    </p>
                                </div>
                                <div class="mt-2">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <span class="truncate">{{ $service->description ?? 'Sem descrição' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-2 flex flex-shrink-0">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium 
                                    {{ match($service->status?->value ?? '') {
                                        'aguardando_aprovacao' => 'bg-yellow-100 text-yellow-800',
                                        'aprovado' => 'bg-blue-100 text-blue-800',
                                        'iniciado' => 'bg-purple-100 text-purple-800',
                                        'finalizado' => 'bg-green-100 text-green-800',
                                        'cancelado' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    }}">
                                    {{ $service->status?->label ?? 'Status desconhecido' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Prazo: {{ $service->deadline ? $service->deadline->format('d/m/Y') : 'Sem prazo' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        Nenhum serviço registrado para esta ordem ainda.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>