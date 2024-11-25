<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborImpediment extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_labor_id',
        'complainant_id',
        'complained_id',
        'reason',
        'status',
        'observations',
    ];

    protected $casts = [
        'logs' => 'array',
    ];
}
