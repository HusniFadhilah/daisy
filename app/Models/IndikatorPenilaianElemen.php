<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndikatorPenilaianElemen extends Model
{
    protected $table = 'indikator_penilaian_elemen';

    protected $fillable = [
        'elemen_standar_id',
        'jenjang_penilaian_id',
        'deskripsi_penilaian',
        'keterangan',
    ];

    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'elemen_standar_id');
    }

    public function jenjangPenilaian()
    {
        return $this->belongsTo(JenjangPenilaian::class, 'jenjang_penilaian_id');
    }
}
