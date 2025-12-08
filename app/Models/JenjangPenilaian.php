<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenjangPenilaian extends Model
{
    protected $table = 'jenjang_penilaian';

    protected $fillable = [
        'nama_jenjang',
        'skor',
    ];

    public function indikatorPenilaianElemen()
    {
        return $this->hasMany(IndikatorPenilaianElemen::class, 'jenjang_penilaian_id');
    }
}
