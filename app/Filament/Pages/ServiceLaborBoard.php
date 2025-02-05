<?php

namespace App\Filament\Pages;

use App\Enums\TypeOfLaborStatus;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceLabor;
use Illuminate\Support\Collection;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class ServiceLaborBoard extends KanbanBoard
{
    public $getOrderNumber;

    protected function getOrderNumber($id){
        return([Order::query()->where('id', $id)->get()]);
    }
    protected static string $model = ServiceLabor::class;
    protected static string $statusEnum = TypeOfLaborStatus::class;

    protected static string $view = 'service-labor-kanban.kanban-board';
    protected static string $headerView = 'service-labor-kanban.kanban-header';
    protected static string $recordView = 'service-labor-kanban.kanban-record';
    protected static string $statusView = 'service-labor-kanban.kanban-status';
    protected static string $scriptsView = 'service-labor-kanban.kanban-scripts';

    public bool $disableEditModal = true;


    protected function records(): \Illuminate\Support\Collection {
        return ServiceLabor::with('getOrderDetails', 'labor', 'service')->get();
    }
}
