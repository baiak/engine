<?php

namespace App\Models;

use App\Enums\TypeOforderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use App\Models\Observation;

class Order extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    protected $fillable =
    [
        'user_id',
        'client_id',
        'vehicle_id',
        'order_number',
        'deadline',
        'status'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function service(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function ServiceAuditLogs(): HasMany
    {
        return $this->hasMany(ServiceAuditLog::class);
    }
    public function labor(): BelongsToMany
    {
        return $this->belongsToMany(Labor::class, 'service_labors', 'labor_id', 'service_id')
            ->withPivot(
                'user_id',
                'order_id',
                'service_id',
                'labor_id',
                'includedAt',
                'approvedAt',
                'startedAt',
                'finishedAt',
                'status',
                'description'
            )
            ->withTimestamps();
    }

    public function getClientNameAttribute(): string
    {
        return $this->client->name ?? 'Cliente Desconhecido';
    }
    public function getFormattedTitleAttribute(): string
    {
        $orderNumber = $this->order_number ?? 'Sem número';
        $clientName = $this->client->name ?? 'Cliente Desconhecido';
        $vehicleModel = $this->vehicle->factory . '/' . $this->vehicle->model ?? 'Carro Desconhecido';

        return "{$orderNumber} - {$vehicleModel} - {$clientName}";
    }

    public function allServiceLabors()
    {
        return ServiceLabor::whereIn('service_id', $this->service->pluck('id'))->get();
    }
    // In App\Models\Order.php, App\Models\Service.php, and App\Models\ServiceLabor.php


    // ... inside the respective model class ...
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    protected $casts = [
        'status' => TypeOforderStatus::class
    ];
}
