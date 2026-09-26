<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RencanaWisuda extends Model
{
    protected $fillable = ['nama', 'urutan'];

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}
