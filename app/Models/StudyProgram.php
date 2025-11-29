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
        'email',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'id_univ');
    }

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'id_level');
    }
}
