<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
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



    public function labor():HasOneOrMany
    {
        return $this->hasOne(Labor::class);
    }

    public function service():HasOneOrMany
    {
        return $this->hasOne(Service::class);
    }

    public function order():HasOneOrMany
    {
        return $this->hasOne(Order::class);
    }


}
