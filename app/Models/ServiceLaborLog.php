<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function ServiceLabor(): BelongsTo
    {
        return $this->belongsTo(ServiceLabor::class);
    }

/*    public function ServiceLabor(): HasMany
    {
        return $this->hasMany(ServiceLabor::class);
    }*/
}
