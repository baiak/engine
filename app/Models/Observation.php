<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'service_labor_id',
        'user_id',
        'title',
        'description',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceLabor(): BelongsTo
    {
        return $this->belongsTo(ServiceLabor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}