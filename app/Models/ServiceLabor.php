<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceLabor extends Pivot
{
    use HasFactory;
    protected $table = 'service_labors';
    protected $fillable =
        [
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

    public function labor(): BelongsToMany
    {
        return $this->belongsToMany(Labor::class);
    }

    public function service():BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }
}
