<?php

namespace App\Filament\Pages;

use App\Enums\TypeOforderStatus;
use App\Models\Client;
use App\Models\Order;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class OrdersKanbanBoard extends KanbanBoard
{
    protected static string $model = Order::class;
    protected static string $statusEnum = TypeOforderStatus::class;

    protected static string $recordTitleAttribute = 'formatted_title';

  /*  public function onStatusChanged(int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Order::find($recordId)->update(['status' => $status]);
        Order::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int $recordId, string $status, array $orderedIds): void
    {
        Order::setNewOrder($orderedIds);
    }*/
}
