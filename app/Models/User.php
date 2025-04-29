<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel\Concerns\HasNotifications;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasNotifications;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profileImg',
        'is_admin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)
            ->withPivot('is_responsible', 'is_active', 'admission_date', 'dismissal_date')
            ->withTimestamps();
    }

    public function activeDepartments(): BelongsToMany
    {
        return $this->departments()->wherePivot('is_active', true);
    }

    public function responsibleDepartments()
    {
        return $this->departments()->wherePivot('is_responsible', true);
    }
    
}