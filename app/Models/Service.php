<?php

namespace App\Models;


use App\Enums\TypeOfServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Service extends Model
{
    use HasFactory;
    protected $fillable =
        [
            'order_id',
            'part_id',
            'department_id',
            'deadline',
            'status',
            'description',
        ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function laborList(): BelongsTo
    {
        return $this->belongsTo(Labor::class);
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

    protected $casts = [
        'status' => TypeOfServiceStatus::class
    ];
}
