<!--{{$service_labor_id}}!-->

<span
    x-data="{
        serviceLaborId: {{ $service_labor_id }},
        total: 0,
        async fetchCount() {
            this.total = await $wire.call('countImpediments', this.serviceLaborId);
        }
    }"
    x-init="fetchCount();"
    :style="total === 0 ? 'display: none;' : 'display: inline-block; background-color: #f87171; color: white; border-radius: 20%; width: 15px; height: 15px; text-align: center; line-height: 15px; font-size: x-small; font-weight: bold;'"
    id="labor-{{ $service_labor_id }}"
    x-text="total">
</span>
