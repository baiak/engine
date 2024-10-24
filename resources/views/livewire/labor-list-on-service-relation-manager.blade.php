@livewireStyles
@vite(['resources/js/app.js'])

<div class="p-2 m-3">
    <table class="!w-full !min-w-max !table-auto !text-left !border !border-zinc-700 !rounded-sm p-3  m-3">
        <thead>
        <tr>
            <th class="border-b !border-zinc-700 bg-blue-gray-50/50 p-3">
                <p class="block antialiased font-sans text-sm text-blue-gray-900 font-small leading-none opacity-70 text-xs font-semibold ">
                    Mao de obra</p>
            </th>
            <th class="border-b !border-zinc-700 bg-blue-gray-50/50 p-3">
                <p class="block antialiased font-sans text-sm text-blue-gray-900 font-small leading-none opacity-70 text-xs font-semibold text-center ">
                    Status</p>
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($getState() as $item)
            <tr>
                <td class="p-2 !border-b !border-zinc-700">
                    <div class="flex items-left gap-3">
                        <p class="!block !antialiased !font-sans  !text-sm-left !leading-normal !text-blue-gray-900 !font-bold">
                            <small>{{($item->title)}}</small></p>
                    </div>
                </td>
                <td class="p-2 !border-b !border-zinc-700">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          @switch($item->pivot->status)
                              @case('Aguardando aprovacao')
                                  style="background-color:#fef9c3;color: #854d0e;"
                          @break
                          @case('Aprovado')
                              style="background-color:#d1fae5; color: #065f46;"
                          @break
                          @case('Rejeitado')
                              style="background-color:#f3f4f6; color: #1f2937;"
                          @break
                          @case('Em Andamento')
                              style="background-color:#dbeafe; color: #1e40af;"
                          @break
                          @case('Impedido')
                              style="background-color:#fee2e2; color: #991b1b;"
                          @break
                          @case('finalizado')
                              style="background-color:#f3f4f6; color: #1f2937;"
                          @break
                          @default
                              style="background-color:#f3f4f6; color: #1f2937;"
                    @endswitch>

                        <!-- Icones svg!-->
                        @switch($item->pivot->status)
                            @case('Aguardando aprovacao')
                                <x-filament::icon
                                        icon="hugeicons-loading-01"
                                        class="h-4 w-4 text-gray-500 dark:text-gray-400"
                                />
                                @break
                            @case('Aprovado')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" class="size-6 mx-1 h-4 w-4 text-gray-500 dark:text-gray-400"/>
                                </svg>
                                @break
                            @case('Rejeitado')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-1 h-4 w-4 text-gray-500 dark:text-gray-400 size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                                </svg>
                                @break
                            @case('Em Andamento')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" class="mx-1 h-4 w-4 text-gray-500 dark:text-gray-400 size-6" viewBox="0 0 24 24"stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                @break
                            @case('Impedido')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" class="size-6 mx-1 h-4 w-4 text-red-500 dark:text-red-400" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                                @break
                            @case('finalizado')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" class="size-6 mx-1 h-4 w-4 mx-3 text-blue-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                @break
                            @default
                        @endswitch
                        {{ $item->pivot->status }}
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

