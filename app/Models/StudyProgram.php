<?php

namespace App\Models;

use Carbon\Carbon;
use App\Libraries\Date;
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
        'rumpun',
        'peringkat_akreditasi',
        'tanggal_kedaluwarsa',
        'status_kedaluwarsa',
        'ketua_prodi_name',
        'ketua_prodi_nip',
        'ketua_tim_akreditasi',
        'akreditasi_phone',
        'akreditasi_mobile',
        'akreditasi_email',
        'city',
        'is_active',
        'is_example',
        'akreditasi_source',
        'akreditasi_locked_at',
        'akreditasi_locked_reason',
        'last_banpt_checked_at',
        'last_banpt_payload',
    ];

    protected $casts = [
        'tanggal_kedaluwarsa'   => 'date',
        'akreditasi_locked_at'  => 'datetime',
        'last_banpt_checked_at' => 'datetime',
        'last_banpt_payload'    => 'array',
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

    public function scopeWithExample($query)
    {
        return $query->whereIn('is_example', [false, true]);
    }

    public function scopeExample($query)
    {
        return $query->where('is_example', true);
    }

    public function pengingatAkreditasi()
    {
        return $this->hasMany(PengingatAkreditasi::class, 'id_program_studi');
    }

    public function pengingatAkreditasiTerbaru()
    {
        return $this->hasOne(PengingatAkreditasi::class, 'id_program_studi')
            ->latestOfMany('tanggal_dikirim');
    }

    public function getDaysLeftAttribute()
    {
        return $this->tanggal_kedaluwarsa
            ? floor(now()->diffInDays($this->tanggal_kedaluwarsa, false))
            : null;
    }

    public function getFullDaysLeftAttribute()
    {
        $diffInDays = now()->diffInDays($this->tanggal_kedaluwarsa, false);
        $diff = now()->diff($this->tanggal_kedaluwarsa);

        $class = $diffInDays < 0 ? 'text-muted' : 'text-danger';

        return <<<HTML
            <small class="$class">{$diff->y} tahun {$diff->m} bulan {$diff->d} hari</small>
        HTML;
    }

    /**
     * Helper methods for formatting
     */
    public function getPeringkatClass()
    {
        $class = 'peringkat-badge ';

        switch ($this->peringkat_akreditasi) {
            case 'Unggul':
                return $class . 'peringkat-unggul';
            case 'Baik Sekali':
                return $class . 'peringkat-baik-sekali';
            case 'Baik':
                return $class . 'peringkat-baik';
            case 'C':
                return $class . 'peringkat-c';
            default:
                return '';
        }
    }

    public function getStatusLabel($daysLeft)
    {
        if (is_null($daysLeft)) {
            return 'Belum Ditentukan';
        } elseif ($daysLeft < 0) {
            return 'Kedaluwarsa';
        } else {
            return 'Aktif';
        }
    }

    public function getStatusClass($daysLeft)
    {
        $class = 'status-badge ';

        if (is_null($daysLeft)) {
            return $class . 'status-belum';
        } elseif ($daysLeft < 0) {
            return $class . 'status-kedaluwarsa';
        } else {
            $baseClass = $class . 'status-aktif';
            return $daysLeft <= 90 && $daysLeft >= 0 ? $baseClass . ' status-urgent' : $baseClass;
        }
    }

    public function getSisaWaktuLabel($daysLeft)
    {
        if ($daysLeft === null) {
            return '-';
        } elseif ($daysLeft < 0) {
            return 'Expired';
        } else {
            return $daysLeft . ' hari';
        }
    }

    public function getStatusBadgeKedaluwarsa($additionalClass = '')
    {
        $tanggal = Carbon::parse($this->tanggal_kedaluwarsa);
        $now = now();

        $diff = $now->diff($tanggal);
        $isExpired = $now->diffInDays($tanggal, false) < 0;

        $class = $isExpired ? 'text-muted' : 'text-danger';

        return '
        ' . Date::tglIndo($tanggal) . '<br>
        <small class="' . $additionalClass . ' ' . $class . '">
            ' . $diff->y . ' tahun ' . $diff->m . ' bulan ' . $diff->d . ' hari
        </small>
    ';
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
