<div wire:key="labor-status-{{ $laborPivotId }}">
    <div
        x-data="laborStatusManager"
        x-init="
            status = @js($status);
            statusOptions = @js($statusOptions); // Já vem formatado do controller
            currentStatus = @js($currentStatus); // Já vem formatado do controller
            initCurrentStatus();
        "
        @close-all-dropdowns.window="if ($event.detail.except !== '{{ $laborPivotId }}') closeDropdown()"
        @click.away="closeDropdown"
    >
        <!-- Loading indicator -->
        <div x-show="loading" class="text-gray-500 text-xs flex items-center">
            <svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Atualizando...</span>
        </div>

        <!-- Status display -->
        <div x-show="!loading" class="relative">
            <!-- Current status -->
            <div @click="toggleDropdown"
                 class="cursor-pointer flex items-center text-xs"
                 x-bind:style="currentStatus ? currentStatus.style : ''">
                <i x-show="currentStatus?.icon" x-bind:class="currentStatus?.icon + ' mr-1'"></i>
                <span x-text="currentStatus?.label || status"></span>
                <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            <!-- Dropdown menu -->
            <div x-show="showDropdown"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute top-full left-0 z-50 mt-1 bg-white shadow rounded-md border border-gray-200"
                 style="min-width: 150px;">
                <ul class="py-1">
                    <template x-for="(option, index) in statusOptions" :key="index">
                        <li @click="updateStatus(option.value)"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-xs flex items-center"
                            x-bind:style="option.style || ''">
                            <i x-show="option.icon" x-bind:class="option.icon + ' mr-1'"></i>
                            <span x-text="option.label || option.value"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('laborStatusManager', () => ({
                showDropdown: false,
                loading: false,
                status: '',
                statusOptions: [],
                currentStatus: null,

                initCurrentStatus() {
                    // Garante que currentStatus seja um objeto válido
                    if (this.status && Array.isArray(this.statusOptions)) {
                        if (!this.currentStatus || typeof this.currentStatus !== 'object') {
                            this.currentStatus = this.statusOptions.find(opt =>
                                opt && opt.val
                            ue === this.status
                            ) || null;
                        }
                    }
                },

                toggleDropdown() {
                    this.showDropdown = !this.showDropdown;
                    if (this.showDropdown) {
                        this.$dispatch('close-all-dropdowns', { except: '{{ $laborPivotId }}' });
                    }
                },

                closeDropdown() {
                    this.showDropdown = false;
                },

                async updateStatus(newStatus) {
                    this.closeDropdown();
                    this.loading = true;

                    const selectedOption = this.statusOptions.find(s => s.value === newStatus);
                    if (selectedOption) {
                        this.status = newStatus;
                        this.currentStatus = selectedOption;
                    }

                    try {
                        const result = await @this.updateStatus(newStatus);

                        if (!result?.success) {
                            throw new Error(result?.message || 'Failed to update status');
                        }
                    } catch (error) {
                        console.error('Erro ao atualizar status:', error);
                        // Revert to original status
                        const originalStatus = @js($status);
                        this.status = originalStatus;
                        this.currentStatus = this.statusOptions.find(s => s.value === originalStatus) || null;

                        // Optional: Show error message to user
                        this.$dispatch('notify', {
                            type: 'error',
                            message: 'Falha ao atualizar status'
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });
    </script>
</div>
