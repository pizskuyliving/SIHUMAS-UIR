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
        'nama_mahasiswa',
        'npm',
        'no_hp',
    ];

    public function followUp()
    {
        return $this->hasOne(FollowUp::class);
    }
}
