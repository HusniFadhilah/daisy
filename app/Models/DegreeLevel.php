<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'id_category',
        'alias',
        'name',
        'is_active',
    ];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class, 'id_degree_level');
    }

    public function category()
    {
        return $this->belongsTo(StudyProgramCategory::class, 'id_category');
    }
}
