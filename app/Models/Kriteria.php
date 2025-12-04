<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    protected $table = 'kriteria';
    
    protected $primaryKey = 'id_kriteria';
    
    protected $fillable = [
        'kode_kriteria',
        'nama_kriteria',
        'keterangan',
    ];

    public function elemenStandar()
    {
        return $this->hasMany(ElemenStandar::class, 'id_kriteria', 'id_kriteria');
    }
}
