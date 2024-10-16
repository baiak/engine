<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class Vehicle extends Model
{
    use HasFactory;
    protected $fillable =
        [
            'factory',
            'model',
            'year',
            'motor',
            'fuel',
            'infos',
        ];
    public function part(): HasOneOrMany

    {
        return $this->hasOne(Part::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function labor(): HasOne
    {
        return $this->hasOne(Labor::class);
    }

    public function service(): HasOne
    {
        return $this->hasOne(Service::class);
    }
    public function getTitleAttribute(): string
    {
        return "{$this->factory}/{$this->model}/{$this->motor}";
    }
}
