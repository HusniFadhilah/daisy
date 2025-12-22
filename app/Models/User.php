<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Seeders\RoleSeeder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_selected',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getRoleAliasAttribute()
    {
        $roles = RoleSeeder::getRoles();
        return $roles->firstWhere('name', $this->attributes['role_selected'])['alias'] ?? 'LAMDEPILAR';
    }

    public function asesmen()
    {
        return $this->hasMany(Asesmen::class, 'id_asesmen', 'id');
    }

    public function studyPrograms()
    {
        return $this->belongsToMany(
            StudyProgram::class,
            'study_program_users',
            'id_user',
            'id_study_program'
        )->select('study_programs.*')
            ->withPivot('role_in_prodi', 'is_active', 'start_date', 'end_date')
            ->withTimestamps();
    }

    /**
     * Get only active study programs
     */
    public function activeStudyPrograms()
    {
        return $this->studyPrograms()->wherePivot('is_active', true);
    }

    /**
     * Get study programs where user is admin_prodi
     */
    public function adminStudyPrograms()
    {
        return $this->studyPrograms()->wherePivot('role_in_prodi', 'admin_prodi');
    }

    /**
     * Check if user is admin of specific study program
     */
    public function isAdminOfStudyProgram($studyProgramId)
    {
        return $this->studyPrograms()
            ->where('study_programs.id', $studyProgramId)
            ->wherePivot('role_in_prodi', 'admin_prodi')
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function programStudi()
    {
        return $this->studyPrograms();
    }

    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role_selected, $role);
        }

        return strtolower($this->role_selected) === strtolower($role);
    }
}
