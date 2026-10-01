<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'pic_id',
        'status_follow_up_id',
        'rencana_wisuda_id',
        'pertimbangan_id',
        'keterangan', // kolom lama (teks bebas), dipertahankan untuk fallback tampilan data lama
        'follow_up_berikutnya', // sekarang ditampilkan sebagai "Catatan"
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function statusFollowUp()
    {
        return $this->belongsTo(StatusFollowUp::class);
    }

    public function rencanaWisuda()
    {
        return $this->belongsTo(RencanaWisuda::class);
    }

    public function pertimbangan()
    {
        return $this->belongsTo(Pertimbangan::class);
    }
}
