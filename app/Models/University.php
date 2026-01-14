<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = [
        'code',
        'name',
        'logo_path',
    ];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class, 'id_university');
    }
}
