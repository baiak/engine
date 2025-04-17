<!--{{$service_labor_id}}-->
<div
    x-data="{
        serviceLaborId: {{ $service_labor_id }},
        total: null,
        loading: true,
        showText: false,
        async fetchCount() {
            this.loading = true;

            // Delay para suavizar transição
            await new Promise(resolve => setTimeout(resolve, 400));

            this.total = await $wire.call('countImpediments', this.serviceLaborId);
            this.loading = false;
        }
    }"
    x-init="fetchCount();"
    class="inline-flex items-center space-x-1 relative rounded-md"
    @mouseenter="showText = true"
    @mouseleave="showText = false"

>

    <!-- Ícone e contador -->
    <template x-if="!loading && total > 0"
              style="padding:0.60em; background-color: #2b2f32; margin-right: 3px; font-size: x-small; color: white; position: relative;"
    >
        <div class="inline-flex items-center space-x-1">
            <!-- Ícone -->
            <x-icon.impediment-icon class="h-5 w-5 text-white" />

            <!-- Badge -->
            <span
                x-text="total"
                class="inline-flex items-center justify-center rounded-md"
                style="background-color: rgba(239,5,67,0.79); color: #FFFFFF; padding-left: 4px; padding-right: 5px; font-size: x-small; text-align: center; margin-left: 3px"
            ></span>
        </div>
    </template>

    <!-- Spinner com três pontinhos -->
    <template x-if="loading">
        <div class="flex space-x-1 ml-3 rounded-md"
        >
            <x-icon.loading-icon  />
        </div>
    </template>

    <!-- Tooltip -->
    <div
        x-show="showText && !loading && total > 0"
        x-transition
        style="transition: opacity 0.6s; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
        background-color: #374151; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8rem;
        white-space: nowrap; opacity: 0.9;"
    >
        Visualizar impedimentos
    </div>
</div>
