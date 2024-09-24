<?php

namespace App\Models;

use App\Enums\TypeOforderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Vehicle::class);
    }
    public function service(): HasMany
    {
        return $this->hasMany(Service::class);
    }
    public function labor_service():BelongsToMany
    {
        return $this->belongsToMany(Labor::class, 'service_labors', 'labor_id', 'service_id')
            ->withPivot('user_id',
                'order_id',
                'service_id',
                'labor_id',
                'includedAt',
                'approvedAt',
                'startedAt',
                'finishedAt',
                'status',
                'description')
            ->withTimestamps();
    }

    protected $casts = [
        'status' => TypeOforderStatus::class
    ];
}
