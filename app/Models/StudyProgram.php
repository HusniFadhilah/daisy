<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $fillable = [
        'name',
        'code',
        'id_univ',
        'id_level',
        'category_id',
        'email',
        'peringkat_akreditasi',
        'tanggal_kadaluarsa',
        'status_kadaluarsa',
    ];

    protected $casts = [
        'tanggal_kadaluarsa' => 'date',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'id_univ');
    }

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'id_level');
    }

    public function category()
    {
        return $this->belongsTo(StudyProgramCategory::class, 'category_id');
    }
}
