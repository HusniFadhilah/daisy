<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenUserRole extends Model
{
    protected $fillable = [
        'id_asesmen',
        'id_user',
        'id_role',
        'jenis_asesmen',
        'id_asesmen_kecukupan',
        'id_asesmen_lapangan',
        'urutan_asesor',
        'status_penawaran',
        'responded_at',
        'response_note',
        'status_pekerjaan',
        'submitted_at',
        'approved_at',
        'approved_by'
    ];

    public const STATUS_PEKERJAAN = [
        'not_started' => [
            'label' => 'Belum Mulai',
            'badge' => 'secondary',
            'icon'  => 'clock',
            'indicator' => 'default',
        ],
        'in_progress' => [
            'label' => 'Sedang Dikerjakan',
            'badge' => 'info',
            'icon'  => 'arrow-repeat',
            'indicator' => 'in-progress',
        ],
        'submitted' => [
            'label' => 'Sudah Di-submit',
            'badge' => 'primary',
            'icon'  => 'upload',
            'indicator' => 'default',
        ],
        'validated' => [
            'label' => 'Tervalidasi',
            'badge' => 'success',
            'icon'  => 'check-circle',
            'indicator' => 'default',
        ],
        'revision_required' => [
            'label' => 'Perlu Revisi',
            'badge' => 'danger',
            'icon'  => 'exclamation-triangle',
            'indicator' => 'revision',
        ],
        'approved' => [
            'label' => 'Disetujui',
            'badge' => 'success',
            'icon'  => 'check2-circle',
            'indicator' => 'approved',
        ],
    ];

    public const STATUS_UI = [
        'not_started' => [
            'button_text' => 'Mulai Penilaian',
            'button_class' => 'btn-primary',
            'button_icon' => 'bi-play-circle',
            'button_disabled' => false,
            'description' => 'Siap untuk memulai penilaian',
        ],
        'in_progress' => [
            'button_text' => 'Lanjutkan Penilaian',
            'button_class' => 'btn-primary',
            'button_icon' => 'bi-pencil-square',
            'button_disabled' => false,
            'description' => 'Penilaian sedang dalam proses',
        ],
        'submitted' => [
            'button_text' => 'Lihat Penilaian',
            'button_class' => 'btn-warning',
            'button_icon' => 'bi-eye',
            'button_disabled' => false,
            'description' => 'Menunggu validasi',
        ],
        'revision_required' => [
            'button_text' => 'Lakukan Revisi',
            'button_class' => 'btn-warning',
            'button_icon' => 'bi-arrow-repeat',
            'button_disabled' => false,
            'description' => 'Validator meminta revisi',
        ],
        'approved' => [
            'button_text' => 'Lihat Hasil',
            'button_class' => 'btn-success',
            'button_icon' => 'bi-file-earmark-check',
            'button_disabled' => false,
            'description' => 'Penilaian telah disetujui',
        ],
    ];

    public const STATUS_PENAWARAN_UI = [
        'pending' => [
            'badge_class' => 'bg-warning text-dark',
            'badge_icon' => 'bi-hourglass-split',
            'badge_text' => 'Menunggu Konfirmasi',
            'button_text' => 'Cek Penawaran',
            'button_class' => 'btn-warning',
            'button_icon' => 'bi-envelope-check',
            'button_disabled' => false,
            'button_route' => 'ak.berkas.penawaran',
        ],
        'rejected' => [
            'badge_class' => 'bg-danger',
            'badge_icon' => 'bi-x-circle',
            'badge_text' => 'Penawaran Ditolak',
            'button_text' => 'Ditolak',
            'button_class' => 'btn-danger',
            'button_icon' => 'bi-x-circle',
            'button_disabled' => true,
        ],
    ];

    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id');
    }

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program');
    }

    public function asesmenKecukupan()
    {
        return $this->belongsTo(AsesmenKecukupan::class, 'id_asesmen_kecukupan');
    }

    public function asesmenLapangan()
    {
        return $this->belongsTo(AsesmenLapangan::class, 'id_asesmen_lapangan');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeAsesorOnly($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', 'asesor');
        });
    }

    public function scopeValidatorOnly($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', 'validator');
        });
    }

    public function scopeForAK($query)
    {
        return $query->where('jenis_asesmen', 'ak');
    }

    public function scopeForAL($query)
    {
        return $query->where('jenis_asesmen', 'al');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status_penawaran', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_penawaran', 'rejected');
    }

    public function scopePending($query)
    {
        return $query->where('status_penawaran', 'pending');
    }

    public function getStatusMetaAttribute(): array
    {
        return self::STATUS_PEKERJAAN[$this->status_pekerjaan]
            ?? [
                'label' => ucfirst(str_replace('_', ' ', $this->status_pekerjaan)),
                'badge' => 'secondary',
                'icon'  => 'info-circle',
                'indicator' => 'default',
            ];
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status_meta['label'];
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status_meta['badge'];
    }

    public function getStatusIconAttribute(): string
    {
        return $this->status_meta['icon'];
    }

    public function getStatusIndicatorAttribute(): string
    {
        return $this->status_meta['indicator'];
    }

    public static function getStatusInfo($assignment): array
    {
        if (!$assignment) {
            return self::defaultStatus('Tidak Ada Penugasan', true);
        }

        // 1️⃣ Status penawaran override
        if ($assignment->status_penawaran !== 'accepted') {
            return self::STATUS_PENAWARAN_UI[$assignment->status_penawaran]
                + ['description' => 'Sebagai ' . Role::getRoleAlias($assignment->id_role)];
        }

        // 2️⃣ Status pekerjaan
        $status = $assignment->status_pekerjaan;

        $meta = self::STATUS_PEKERJAAN[$status] ?? null;
        $ui   = self::STATUS_UI[$status] ?? [];

        if (!$meta) {
            return self::defaultStatus();
        }

        return [
            'badge_class' => 'bg-' . $meta['badge'],
            'badge_icon'  => 'bi-' . $meta['icon'],
            'badge_text'  => $meta['label'],
        ] + $ui + [
            'description' => ($ui['description'] ?? '') .
                ' sebagai ' . Role::getRoleAlias($assignment->id_role),
        ];
    }

    private static function defaultStatus(string $text = 'Status Tidak Diketahui', bool $disabled = false): array
    {
        return [
            'badge_class' => 'bg-secondary',
            'badge_icon' => 'bi-question-circle',
            'badge_text' => $text,
            'button_text' => 'Tidak Tersedia',
            'button_class' => 'btn-secondary',
            'button_icon' => 'bi-x-circle',
            'button_disabled' => $disabled,
            'description' => '',
        ];
    }
}
