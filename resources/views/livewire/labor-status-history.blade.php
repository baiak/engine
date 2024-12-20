<div><!--{{$service_labor_id}}!-->
    <x-filament::modal>
        <x-slot name="trigger">
            <button
                class="rounded-md m-2"
                style="padding:0.60em; margin-left:3px; background-color: #2b2f32; color:#FFFFFF;  font-size: x-small; position: relative;"
                x-data="{ showTooltip: false }"
                @mouseenter="showTooltip = true"
                @mouseleave="showTooltip = false">
                <!-- Ícone -->
                <x-filament::icon
                    icon="tabler-history"
                    class="h-4 w-4 ml-2"
                />
                <!-- Tooltip -->
                <div x-show="showTooltip" x-transition
                     style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%);
                       background-color: #374151; color: white; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px;
                       white-space: nowrap; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    Histórico de status
                </div>
            </button>
        </x-slot>

        <!-- Modal content -->
        <div style="font-size: small; color: #9ca3af"
             x-data="{
             logs: [],
             async fetchLogs(serviceLaborId) {
                 const response = await @this.call('getServiceLaborLogs', serviceLaborId);
                 this.logs = response;
             },
             init() {
                 const serviceLaborId = {{ $service_labor_id }};
                 this.fetchLogs(serviceLaborId);
             }
         }">
            <template x-if="logs.length > 0">
                <ul>
                    <template x-for="log in logs" :key="log.id">
                        <li class="flex items-center m-2 p-2 rounded-xl mb-3 mt-2 border border-gray-600">
                            <div
                                x-html="log.new_values?.user_avatar || log.old_values?.user_avatar"
                                class="m-2 p-2"></div>
                            <div class="m-2 p-2">
                            <span style="font-weight: bold"
                                  x-text="log.new_values?.status || 'N/A'"></span>
                                <br/>
                                <span
                                    x-text="log.new_values?.updated_at || log.old_values?.updated_at"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </template>
        </div>
    </x-filament::modal></div>
