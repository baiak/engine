<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Part extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'parameters',
        'vehicle_id',
        'department_id'
    ];
    public function department(): HasMany
    {
        return $this->hasMany(Department::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
