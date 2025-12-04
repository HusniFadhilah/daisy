<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standar extends Model
{
    protected $table = 'standar';
    
    protected $primaryKey = 'id_standar';
    
    protected $fillable = [
        'kode_standar',
        'nama_standar',
    ];

    public function elemenStandar()
    {
        return $this->hasMany(ElemenStandar::class, 'id_standar', 'id_standar');
    }
}
