<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLaborLog extends Model
{
    protected $table = 'service_labor_logs';

    protected $fillable = [
        'service_labor_id',
        'event',
        'old_values',
        'new_values',
        'user_id'
    ];

    public function serviceLabor(): BelongsTo
    {
        return $this->belongsTo(ServiceLabor::class);
    }
}
