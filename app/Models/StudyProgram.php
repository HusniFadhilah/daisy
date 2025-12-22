<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $fillable = [
        'name',
        'full_name',
        'code',
        'id_univ',
        'id_level',
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

    /**
     * Relasi Many-to-Many dengan Users
     * 1 prodi bisa punya banyak admin users
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'study_program_users',
            'id_study_program',
            'id_user'
        )
            ->withPivot('role_in_prodi', 'is_active', 'start_date', 'end_date')
            ->withTimestamps();
    }

    /**
     * Get only active users
     */
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * Get admin users for this study program
     */
    public function adminUsers()
    {
        return $this->users()
            ->wherePivot('role_in_prodi', 'admin_prodi')
            ->wherePivot('is_active', true);
    }

    /**
     * Get koordinator users
     */
    public function koordinators()
    {
        return $this->users()
            ->wherePivot('role_in_prodi', 'koordinator')
            ->wherePivot('is_active', true);
    }

    /**
     * Get staff users
     */
    public function staffUsers()
    {
        return $this->users()
            ->wherePivot('role_in_prodi', 'staff')
            ->wherePivot('is_active', true);
    }
}
