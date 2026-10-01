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

    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    /**
     * True untuk SuperAdmin MAUPUN Koordinator - dipakai di tempat-tempat
     * yang boleh diakses keduanya (beda dengan isSuperAdmin() yang hanya
     * true untuk SuperAdmin murni).
     */
    public function isAtLeastKoordinator(): bool
    {
        return in_array($this->role, ['superadmin', 'koordinator'], true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => 'SuperAdmin',
            'koordinator' => 'Koordinator',
            default => 'PIC Telemarketing',
        };
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
