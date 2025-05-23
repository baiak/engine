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
    const finalizadoStatusValue = '{{ \App\Enums\TypeOfLaborStatus::finalizado->value }}'; 
    
    function onAdd(e) { // Chamado quando o item é solto em uma NOVA lista DIFERENTE
        const recordId = e.item.id;
        const newStatus = e.to.dataset.statusId;
        const fromList = e.from;
        const item = e.item;
        const oldIndex = e.oldDraggableIndex;

        console.log('onAdd - recordId:', recordId, 'newStatus:', newStatus, 'fromStatus:', fromList.dataset.statusId);

        // Esta lógica é para INICIAR um cancelamento (arrastar um item NÃO cancelado para a coluna "Cancelado")
        // O onMove já terá permitido esta ação (pois fromStatus não era 'cancelado').
        if (newStatus === canceladoStatusValue) {
            // Verifica se o item realmente veio de uma coluna diferente de "Cancelado"
            // (embora onMove já devesse ter coberto isso para a lógica de "não sair de cancelado")
            if (fromList.dataset.statusId !== canceladoStatusValue) {
                console.log('Item movido PARA Cancelado. Revertendo visual e abrindo modal.');
                fromList.insertBefore(item, fromList.children[oldIndex]); // Reverte o visual para o modal confirmar
                // Usando o formato de array para recordId que resolveu o problema anterior
                Livewire.dispatch('openCancelLaborModal', [recordId]);
            } else {
                // Se, por algum motivo, onAdd for chamado para um movimento de Cancelado para Cancelado
                // (geralmente onUpdate lida com ordenação interna), tratamos como uma ordenação.
                const toOrderedIds = [].slice.call(e.to.children).map(child => child.id);
                Livewire.dispatch('sort-changed', {recordId, status: newStatus, orderedIds: toOrderedIds});
            }
        } else {
            // Comportamento para outras mudanças de status (ex: de 'Pendente' para 'Em Andamento')
            // onMove já garantiu que não estamos vindo da coluna "Cancelado".
            console.log('Item movido para uma coluna não-Cancelado. Despachando status-changed.');
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
            onMove: function (evt, originalEvent) {
                        const fromStatus = evt.from.dataset.statusId; // Status da coluna de origem
                        const toStatus = evt.to.dataset.statusId;     // Status da coluna de destino (onde o mouse está)

                        // Se o item está sendo arrastado DA coluna "Cancelado"
                        // PARA QUALQUER OUTRA coluna que NÃO SEJA "Cancelado"
                        if (fromStatus === canceladoStatusValue || fromStatus === finalizadoStatusValue && toStatus !== canceladoStatusValue) {
                            console.log('Movimentação bloqueada: Não é possível mover um item "Cancelado" para outro status.');
                            return false; // Impede a movimentação
                        }

                        // Permite todas as outras movimentações:
                        // - De não-cancelado para não-cancelado
                        // - De não-cancelado para cancelado (o onAdd vai pegar para abrir o modal)
                        // - Ordenação dentro de qualquer coluna (incluindo a coluna "Cancelado")
                        return true;
                    }
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