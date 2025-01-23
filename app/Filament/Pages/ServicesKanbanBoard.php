<?php

namespace App\Filament\Pages;

use App\Enums\TypeOfServiceStatus;
use App\Models\Service;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Ramsey\Collection\Collection;

class ServicesKanbanBoard extends KanbanBoard
{
    protected static string $model = Service::class;
    protected static string $statusEnum = TypeOfServiceStatus::class;

    protected static string $recordTitleAttribute = 'formatted_title';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')

        ];
    }

    protected function records(): \Illuminate\Support\Collection
    {
        return Service::all();
    }

    protected static string $view = 'service-kanban.kanban-board';

    protected static string $headerView = 'service-kanban.kanban-header';

    protected static string $recordView = 'service-kanban.kanban-record';

    protected static string $statusView = 'service-kanban.kanban-status';

    protected static string $scriptsView = 'service-kanban.kanban-scripts';
    public bool $disableEditModal = true;

}
