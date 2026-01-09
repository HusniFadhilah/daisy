<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indikator extends Model
{
    protected $table = 'indikator';

    protected $fillable = [
        'id_elemen',
        'id_jenis',
        'kode_indikator',
        'deskripsi_indikator',
    ];

    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function jenisIndikator()
    {
        return $this->belongsTo(JenisIndikator::class, 'id_jenis');
    }
}
