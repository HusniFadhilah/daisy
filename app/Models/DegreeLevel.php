<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DegreeLevel extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class, 'id_level');
    }
}
