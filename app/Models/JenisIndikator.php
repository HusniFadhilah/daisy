<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisIndikator extends Model
{
    protected $table = 'jenis_indikator';
    
    protected $primaryKey = 'id_jenis';
    
    protected $fillable = [
        'nama_jenis',
        'keterangan',
    ];

    public function indikator()
    {
        return $this->hasMany(Indikator::class, 'id_jenis', 'id_jenis');
    }
}
