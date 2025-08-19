<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'client_id',
        'rua',
        'numero',
        'bairro',
        'estado',
        'cidade',
        'complemento',
        'cep',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
