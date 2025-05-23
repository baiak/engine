<script>
    function onStart() {
        setTimeout(() => document.body.classList.add("grabbing"))
    }

    function onEnd() {
        document.body.classList.remove("grabbing")
    }

    function setData(dataTransfer, el) {
        dataTransfer.setData('id', el.id)
    }
       // Define o valor do status "Cancelado" para ser usado no JavaScript
    // Isso garante que estamos usando o mesmo valor definido no Enum PHP.
    const canceladoStatusValue = '{{ \App\Enums\TypeOfLaborStatus::cancelado->value }}';
    
    function onAdd(e) {
        const recordId = e.item.id;
        console.log('Disparando openCancelLaborModal com recordId:', recordId, 'Tipo:', typeof recordId); // Adicione esta linha
        const newStatus = e.to.dataset.statusId; // O ID do status da coluna de destino (e.g., 'cancelado')
        const fromList = e.from; // A lista de origem
        const item = e.item; // O item (card) que foi movido
        const oldIndex = e.oldDraggableIndex; // O índice original do item na lista de origem

        if (newStatus === canceladoStatusValue) {
            // Se o card foi movido para a coluna "Cancelado":
            // 1. Reverta a mudança visual no DOM.
            //    SortableJS já moveu o item para e.to. Precisamos movê-lo de volta para e.from.
            fromList.insertBefore(item, fromList.children[oldIndex]);

            // 2. Dispare o evento Livewire para abrir o modal de cancelamento.
            //    O ServiceLaborBoard.php já escuta 'openCancelLaborModal'.
            Livewire.dispatch('openCancelLaborModal', [recordId]);
            
            // Importante: Não dispare 'status-changed' aqui, pois o modal
            // e sua ação de confirmação cuidarão da atualização do status.
        } else {
            // Comportamento padrão para outras mudanças de status:
            const fromOrderedIds = [].slice.call(fromList.children).map(child => child.id);
            const toOrderedIds = [].slice.call(e.to.children).map(child => child.id);
            Livewire.dispatch('status-changed', {recordId, status: newStatus, fromOrderedIds, toOrderedIds});
        }
    }

    function onUpdate(e) {
        const recordId = e.item.id
        const status = e.from.dataset.statusId
        const orderedIds = [].slice.call(e.from.children).map(child => child.id)

        Livewire.dispatch('sort-changed', {recordId, status, orderedIds})
    }




    document.addEventListener('livewire:navigated', () => {
        const statuses = @js($statuses->map(fn ($status) => $status['id']))

        statuses.forEach(status => Sortable.create(document.querySelector(`[data-status-id='${status}']`), {
            group: 'filament-kanban',
            ghostClass: 'opacity-50',
            animation: 150,

            onStart,
            onEnd,
            onUpdate,
            setData,
            onAdd,
        }))
    })
    document.addEventListener('laborStatusUpdated', event => {
            const { laborPivotId, status } = event.detail
            // You can add any global handlers here
            console.log(`Labor status updated: ${laborPivotId} to ${status}`)
            
            // Optionally trigger any other actions based on status changes
            // For example, you could refresh the kanban board or show notifications
        })
</script>