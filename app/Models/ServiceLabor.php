<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Observation;


class ServiceLabor extends Model

{
    protected static function booted()
    {
        static::created(function ($serviceLabor) {
            $serviceLabor->logs()->create([
                'event' => 'created',
                'old_values' => null,
                'new_values' => json_encode($serviceLabor->getAttributes()),
                'user_id' => Auth::id(),
            ]);
        });
    }

    use Notifiable;
    use HasFactory;
    protected $table = 'service_labors';
    protected $fillable =
    [
        'id',
        'user_id',
        'order_id',
        'service_id',
        'labor_id',
        'includedAt',
        'approvedAt',
        'startedAt',
        'finishedAt',
        'status',
        'description',
    ];



    public function logs(): HasMany
    {
        return $this->hasMany(ServiceLaborLog::class, 'service_labor_id');
    }
    public function labor(): BelongsTo
    {
        return $this->belongsTo(Labor::class, 'labor_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getOrderDetails(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getVehicleDetailsAttribute()
    {
        if (!$this->service || !$this->service->order || !$this->service->order->vehicle) {
            return 'Sem veículo';
        }

        $vehicle = $this->service->order->vehicle;

        return "{$vehicle->factory}/{$vehicle->model}/{$vehicle->motor}";
    }
    // In App\Models\Order.php, App\Models\Service.php, and App\Models\ServiceLabor.php
   
    

    // ... inside the respective model class ...
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }



    public function user(): HasOneOrMany
    {
        return $this->hasOne(User::class);
    }
    public function impediments()
    {
        return $this->hasMany(LaborImpediment::class);
    }
    public function scopeOrderNumberById($id)
    {
        return Order::where('id', $id)->value('order_number');
    }
}
