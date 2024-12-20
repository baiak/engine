<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;

class ListLaborWithStatus extends Column
{
    protected string $view = 'tables.columns.List-labor-with-status';

    public function openModal($recordId)
    {
        // Emitindo o evento para abrir o modal
        $this->emit('openModal', $recordId);
    }
}
