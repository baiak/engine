<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_responsible', 'is_active', 'admission_date', 'dismissal_date')
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