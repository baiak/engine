@if (null !== $getState() && count($getState()) > 0)
    <table class="text-xs" style="width: 100%; border-radius: 10px; overflow: hidden; border-collapse: separate;">
        <thead class="rounded-lg p-5 text-xs"
               style="border-bottom: 1px solid #4a5568; padding: 12px 16px; text-align: center; color: #bcbcbd;">
        <tr class="text-left" style="border-bottom: 1px solid #4a5568;">
            <th style="border-bottom: 1px solid #4a5568;">Listagem de mão de obras:</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($getState() as $item)
            <tr>
                <td style="padding: 12px 16px; border-bottom: 1px solid #4a5568;">
                    <div style="display: flex; justify-content: space-around;">
                        <!-- Título -->
                        <span style="font-weight: 500;">{{ $item['title'] }}</span>
                        <!-- Status -->
                        @php
                            $status = $item['pivot']['status'];
                            $statusStyles = match($status) {
                                'aprovado' => 'background-color: #c6f6d5; color: #22543d;',
                                'aguardando_aprovação' => 'background-color: #fefcbf; color: #975a16;',
                                'rejeitado' => 'background-color: #fed7d7; color: #742a2a;',
                                default => 'background-color: #e2e8f0; color: #4a5568;',
                            };

                        @endphp
                        <span style="margin-left:15px; {{ $statusStyles }} font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 5px;">
                                {{ $status }}
                            </span>

                        <!-- Botão Editar -->
                        <button class="btn btn-primary" wire:click="$emit(openEditModal({{ $item['id']}})">
                            Editar Status
                        </button>

                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif


