<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'prodi_id', 'nama_file',
        'total_baru', 'total_diperbarui', 'total_dilewati',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
