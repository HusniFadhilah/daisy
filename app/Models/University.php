<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class University extends Model
{
    protected $fillable = [
        'code',
        'name',
        'logo_path',
        'is_active',
        'is_example',
        'email',
    ];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class, 'id_university');
    }

    public function studyProgramsWithExample()
    {
        return $this->hasMany(StudyProgram::class, 'id_university')
            ->withoutGlobalScope('exclude_example');
    }

    public function scopeNonExample($query)
    {
        return $query->where('is_example', false);
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope('exclude_example', function (Builder $builder) {
    //         $builder->where(function ($q) {
    //             $q->whereNull('is_example')
    //                 ->orWhere('is_example', false)
    //                 ->orWhere('is_example', 0);
    //         });
    //     });
    // }
}
