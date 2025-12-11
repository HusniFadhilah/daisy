<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndikatorPenilaianElemen extends Model
{
    protected $table = 'indikator_penilaian_elemen';

    protected $fillable = [
        'id_elemen',
        'id_jenjang_penilaian',
        'deskripsi_penilaian',
        'keterangan',
    ];

    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function jenjangPenilaian()
    {
        return $this->belongsTo(JenjangPenilaian::class, 'id_jenjang_penilaian');
    }
}
