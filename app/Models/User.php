<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'avatar', 'google_id', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isBendahara(): bool
    {
        return $this->hasRole('bendahara') || $this->hasRole('admin');
    }

    public function isEditor(): bool
    {
        return $this->hasRole('editor') || $this->hasRole('admin');
    }

    public function keuangan()
    {
        return $this->hasMany(Keuangan::class, 'created_by');
    }
}
