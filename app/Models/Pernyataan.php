<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pernyataan extends Model
{
    protected $fillable = [
        'id_elemen',
        'code',
        'pernyataan',
    ];

    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }
}
