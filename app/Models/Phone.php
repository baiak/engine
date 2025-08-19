<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{

    protected $fillable = [
        'client_id',
        'title',
        'number',
    ];
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
