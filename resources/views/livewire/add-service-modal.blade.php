<div>
    <h3 class="text-lg font-medium mb-4 p-2 rounded-md" style="color: #333333; background-color: #F0F0F0;">
        Adicionar Novo Serviço à Ordem #{{ $orderId }}
    </h3>

    <form wire:submit.prevent="saveService">
        <div class="grid grid-cols-2 gap-4 p-4">
            <!-- Coluna 1 -->
            <div class="space-y-4">
                <!-- Descrição do Serviço -->
                <div>
                    <label for="service_description" class="block text-sm font-medium" style="color: #333333;">Descrição do Serviço <span style="color: #D32F2F;">*</span></label>
                    <textarea id="service_description" wire:model="serviceDescription" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md" style="border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #000000;" placeholder="Descreva o serviço..."></textarea>
                    @error('serviceDescription') <span style="color: #D32F2F; font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Peça -->
                <div>
                    <label for="part_id" class="block text-sm font-medium" style="color: #333333;">Peça</label>
                    <select id="part_id" wire:model="partId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" style="border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #000000;">
                        <option value="">Selecione uma peça</option>
                        @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->title }}</option>
                        @endforeach
                    </select>
                    @error('partId') <span style="color: #D32F2F; font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Departamento -->
                <div>
                    <label for="department_id" class="block text-sm font-medium" style="color: #333333;">Departamento <span style="color: #D32F2F;">*</span></label>
                    <select id="department_id" wire:model="departmentId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" style="border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #000000;">
                        <option value="">Selecione um departamento</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->title }}</option>
                        @endforeach
                    </select>
                    @error('departmentId') <span style="color: #D32F2F; font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Coluna 2 -->
            <div class="space-y-4">
                <!-- Prazo -->
                <div>
                    <label for="deadline" class="block text-sm font-medium" style="color: #333333;">Prazo <span style="color: #D32F2F;">*</span></label>
                    <input type="date" id="deadline" wire:model="deadline" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md" style="border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #000000;">
                    @error('deadline') <span style="color: #D32F2F; font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium" style="color: #333333;">Status <span style="color: #D32F2F;">*</span></label>
                    <select id="status" wire:model="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" style="border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #000000;">
                        <option value="">Selecione o status</option>
                        @foreach($serviceStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <span style="color: #D32F2F; font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Usuário Responsável -->
                <div>
                    <label for="user_id" class="block text-sm font-medium" style="color: #333333;">Usuário Responsável <span style="color: #D32F2F;">*</span></label>
                    <select id="user_id" wire:model="userId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" style="border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #000000;">
                        <option value="">Selecione um usuário</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('userId') <span style="color: #D32F2F; font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Número da Ordem (somente leitura) -->
                <div>
                    <label for="order_number" class="block text-sm font-medium" style="color: #333333;">Número da Ordem</label>
                    <input type="text" id="order_number" value="{{ $orderNumber ?? 'Será preenchido automaticamente' }}" readonly class="mt-1 shadow-sm block w-full sm:text-sm rounded-md bg-gray-100" style="border: 1px solid #CCCCCC; color: #666666;">
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3 p-3 rounded-b-md" style="background-color: #F9F9F9;">
            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm" style="background-color: #28A745; color: #FFFFFF;">
                Salvar Serviço
            </button>
            <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md shadow-sm px-4 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm" style="background-color: #6C757D; color: #FFFFFF; border: 1px solid #6C757D;">
                Fechar
            </button>
        </div>
    </form>
</div>