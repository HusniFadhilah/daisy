<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    protected $table = 'kriteria';

    protected $fillable = [
        'kode_kriteria',
        'nama_kriteria',
        'keterangan',
    ];

    public function elemenStandar()
    {
        return $this->hasMany(ElemenStandar::class, 'id_kriteria');
    }

    public function indikator()
    {
        return $this->hasManyThrough(
            Indikator::class,
            ElemenStandar::class,
            'id_kriteria', // FK di elemen_standar
            'id_elemen',   // FK di indikator
            'id',          // PK kriteria
            'id'           // PK elemen_standar
        );
    }
}
