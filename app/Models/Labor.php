<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Labor extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'part_id'
    ];
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function vehicle(): HasOneThrough
    {
        return $this->hasOneThrough(Vehicle::class, Part::class, 'id', 'id', 'part_id');

    }

}
