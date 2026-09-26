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
        'keterangan',
        'follow_up_berikutnya',
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
}
