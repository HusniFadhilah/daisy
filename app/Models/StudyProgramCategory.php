<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgramCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Relasi ke StudyProgram
     */
    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class, 'id_category');
    }

    /**
     * Relasi ke BobotPenilaian
     */
    public function bobotPenilaian()
    {
        return $this->hasMany(BobotPenilaian::class, 'id_category');
    }
}
