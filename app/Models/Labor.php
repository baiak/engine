<?php

namespace App\Models;

use App\Enums\TypeOfLaborStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Labor extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'part_id'
    ];
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function vehicle(): HasOneThrough
    {
        return $this->hasOneThrough(Vehicle::class, Part::class, 'id', 'id', 'part_id');

    }

    public function labor():BelongsToMany
    {
        return $this->belongsToMany(Labor::class, 'service_labors', 'service_id', 'labor_id')
            ->withPivot('id','user_id',
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
    public function service():BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    public function order(): HasOneOrMany
    {
        return $this->hasOne(Order::class);
    }


    protected $casts = [
        'status' => TypeOfLaborStatus::class
    ];

}
