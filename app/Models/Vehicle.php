<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
