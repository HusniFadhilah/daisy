<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElemenStandar extends Model
{
    protected $table = 'elemen_standar';

    protected $fillable = [
        'id_kriteria',
        'kode_elemen',
        'pernyataan_elemen',
        'keterangan',
    ];

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class, 'id_kriteria');
    }

    public function indikator()
    {
        return $this->hasMany(Indikator::class, 'id_elemen');
    }

    public function pernyataan()
    {
        return $this->hasMany(Pernyataan::class, 'id_elemen');
    }

    public function penilaian()
    {
        return $this->hasMany(PenilaianElemen::class, 'id_elemen');
    }

    public function indikatorPenilaian()
    {
        return $this->hasMany(IndikatorPenilaianElemen::class, 'id_elemen')->with('jenjangPenilaian')
            ->orderBy('id_jenjang_penilaian');
    }
}
