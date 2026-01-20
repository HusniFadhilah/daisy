<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StudyProgram extends Model
{
    protected $fillable = [
        'name',
        'full_name',
        'code',
        'id_university',
        'id_degree_level',
        'id_category',
        'bentuk_pt',
        'email',
        'peringkat_akreditasi',
        'tanggal_kedaluwarsa',
        'status_kedaluwarsa',
        'is_active',
        'is_example'
    ];

    protected $casts = [
        'tanggal_kedaluwarsa' => 'date',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'id_university');
    }

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'id_degree_level');
    }

    public function category()
    {
        return $this->belongsTo(StudyProgramCategory::class, 'id_category');
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
