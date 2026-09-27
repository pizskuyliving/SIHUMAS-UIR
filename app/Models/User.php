<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'photo_path',
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
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * URL foto profil, atau null kalau belum upload foto (view akan
     * menampilkan avatar berupa huruf awal nama sebagai fallback).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('avatars/' . $this->photo_path) : null;
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?: '?', 0, 1));
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class, 'pic_id');
    }
}
