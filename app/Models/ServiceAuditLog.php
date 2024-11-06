<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceAuditLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_id',
        'user_id',
        'old_status',
        'new_status',
        'changed_at',
        'user_id',
    ];

    public function User(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function Service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
