<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Facades\DB;


class ServiceLabor extends Model

{
    use HasFactory;
    protected $table = 'service_labors';
    protected $fillable =
        [   'id',
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

    public function user():HasOneOrMany
    {
        return $this->hasOne(User::class);
    }


}
