<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndikatorStandar extends Model
{
    protected $table = 'indikator';
    
    protected $primaryKey = 'id_indikator';
    
    protected $fillable = [
        'id_elemen',
        'jenis_indikator',
        'deskripsi_indikator',
    ];

    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen', 'id_elemen');
    }
}
