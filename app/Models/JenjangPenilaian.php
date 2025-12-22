<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenjangPenilaian extends Model
{
    protected $table = 'jenjang_penilaian';

    protected $fillable = [
        'name',
        'color',
        'skor',
    ];

    public function indikatorPenilaianElemen()
    {
        return $this->hasMany(IndikatorPenilaianElemen::class, 'id_jenjang_penilaian');
    }

    public static function getColorBySkor($skor)
    {
        return self::where('skor', $skor)->value('color') ?? 'cccccc';
    }
}
