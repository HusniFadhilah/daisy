<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asesmen extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    public function userRoles()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen');
    }

    public function penilaianElemen()
    {
        return $this->hasMany(PenilaianElemen::class, 'id_asesmen');
    }
}
