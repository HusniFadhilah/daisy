<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgramCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Relasi ke StudyProgram
     */
    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class, 'category_id');
    }

    /**
     * Relasi ke BobotPenilaian
     */
    public function bobotPenilaian()
    {
        return $this->hasMany(BobotPenilaian::class, 'id_category');
    }
}
