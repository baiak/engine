<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    public function service(): HasOne
    {
        return $this->hasOne(Service::class);

    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_responsible', 'user_id', 'is_active', 'admission_date', 'dismissal_date')
            ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', true);
    }

    public function responsibleUser()
    {
        return $this->users()
            ->wherePivot('is_responsible', true)
            ->first();
    }


    protected $casts = [
        'dismissal_date' => 'datetime',
    ];
}