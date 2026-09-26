<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusFollowUp extends Model
{
    protected $fillable = ['nama', 'urutan'];

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}
