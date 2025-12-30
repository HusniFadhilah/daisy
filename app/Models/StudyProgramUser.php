<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgramUser extends Model
{
    protected $table = 'study_program_users';

    protected $fillable = [
        'id_user',
        'id_study_program',
        'role_in_prodi',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke study program
    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program');
    }

    // Scopes (opsional tapi kepake)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoleInProdi($query, string $role)
    {
        return $query->where('role_in_prodi', $role);
    }

    public function scopeAdminProdi($query)
    {
        return $query->where('role_in_prodi', 'admin_prodi');
    }
}
