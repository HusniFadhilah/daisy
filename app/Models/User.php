<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Seeders\RoleSeeder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'roles',
        'role_selected',
        'last_role_switch',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array', // Cast to array
            'last_role_switch' => 'datetime',
        ];
    }

    /**
     * Get role alias for display
     */
    public function getRoleAliasAttribute()
    {
        $roles = RoleSeeder::getRoles();
        return $roles->firstWhere('name', $this->role_selected)['alias'] ?? 'User';
    }

    /**
     * Get all available roles for this user
     */
    public function getAvailableRolesAttribute()
    {
        $allRoles = RoleSeeder::getRoles();
        $userRoles = $this->roles ?? [];

        return $allRoles->whereIn('name', $userRoles);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role_selected, $role);
        }

        return strtolower($this->role_selected) === strtolower($role);
    }

    /**
     * Check if user has any of the given roles in their roles array
     */
    public function hasAnyRole($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $userRoles = $this->roles ?? [];

        return !empty(array_intersect($roles, $userRoles));
    }

    /**
     * Sync roles from asesmen assignments
     */
    public function syncRolesFromAssignments()
    {
        // Get all unique roles from accepted assignments
        $assignedRoles = $this->asesmenUserRoles()
            ->where('status_penawaran', 'accepted')
            ->with('role')
            ->get()
            ->pluck('role.name')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // Always include base role
        if ($this->role && !in_array($this->role, $assignedRoles)) {
            $assignedRoles[] = $this->role;
        }

        // Always include current role_selected if not in list
        if ($this->role_selected && !in_array($this->role_selected, $assignedRoles)) {
            $assignedRoles[] = $this->role_selected;
        }

        // Update roles column
        $this->update(['roles' => array_values(array_unique($assignedRoles))]);

        return $this;
    }

    /**
     * Switch to a specific role
     */
    public function switchRole($roleName)
    {
        // Sync roles first to ensure up-to-date
        $this->syncRolesFromAssignments();

        // Check if user has this role
        if (!in_array($roleName, $this->roles ?? [])) {
            return false;
        }

        $this->update([
            'role_selected' => $roleName,
            'last_role_switch' => now(),
        ]);

        return true;
    }

    /**
     * Get count of roles
     */
    public function getRolesCountAttribute()
    {
        return count($this->roles ?? []);
    }

    /**
     * Check if user has multiple roles
     */
    public function hasMultipleRoles()
    {
        return $this->roles_count > 1;
    }

    // Relations
    public function asesmenUserRoles()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_user');
    }

    public function asesmens()
    {
        return $this->belongsToMany(
            Asesmen::class,
            'asesmen_user_roles',
            'id_user',
            'id_asesmen'
        )
            ->withPivot([
                'id_role',
                'jenis_asesmen',
                'status_penawaran',
                'responded_at',
                'response_note',
                'status_pekerjaan',
                'submitted_at',
                'approved_at',
                'approved_by',
            ])
            ->withTimestamps();
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

    public function activeStudyPrograms()
    {
        return $this->studyPrograms()->wherePivot('is_active', true);
    }

    public function adminStudyPrograms()
    {
        return $this->studyPrograms()->wherePivot('role_in_prodi', 'admin_prodi');
    }

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
}
