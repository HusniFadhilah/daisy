<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BobotPenilaian extends Model
{
    use HasFactory;

    protected $table = 'bobot_penilaian';

    protected $fillable = [
        'id_elemen',
        'id_category',
        'bobot',
        'is_active',
    ];

    protected $casts = [
        'bobot' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke ElemenStandar
     */
    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen', 'id');
    }

    /**
     * Relasi ke StudyProgramCategory
     */
    public function category()
    {
        return $this->belongsTo(StudyProgramCategory::class, 'id_category');
    }
}
