<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'no',
        'fakultas',
        'prodi_id',
        'nama_mahasiswa',
        'npm',
        'no_hp',
        'no_hp_2',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Nama fakultas untuk ditampilkan: utamakan relasi Prodi->Fakultas
     * (data baru hasil import per fakultas/prodi), fallback ke kolom teks
     * 'fakultas' lama (data yang diisi manual sebelum fitur ini ada).
     */
    public function getNamaFakultasAttribute(): ?string
    {
        return $this->prodi?->fakultas?->nama ?? $this->fakultas;
    }

    public function followUp()
    {
        return $this->hasOne(FollowUp::class);
    }
}
