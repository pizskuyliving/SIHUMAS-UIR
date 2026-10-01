<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    const UPDATED_AT = null; // cuma perlu created_at

    protected $fillable = ['user_id', 'aksi', 'deskripsi'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cara pakai di controller mana pun:
     *   ActivityLog::catat('hapus_mahasiswa', "Menghapus data \"{$nama}\" ({$npm})");
     * User yang sedang login otomatis tercatat sebagai pelakunya.
     */
    public static function catat(string $aksi, string $deskripsi): void
    {
        static::create([
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'created_at' => now(),
        ]);
    }
}
