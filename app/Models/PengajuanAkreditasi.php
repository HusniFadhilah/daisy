<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanAkreditasi extends Model
{
    protected $table = 'pengajuan_akreditasi';

    // ============================================
    // FILLABLE: Only user-input fields
    // ============================================
    protected $fillable = [
        // Basic Info (user input)
        'nomor_pengajuan',
        'id_program_studi',
        'id_user_pengaju',
        'tahun_akreditasi',
        'jenis_akreditasi',
        'tanggal_pengajuan',
        'catatan_pengaju',

        // Assignment (admin input)
        'id_de_assigned',
        // All tracking dates - should only be set via code
        'tanggal_pengingat',
        'tanggal_surat_permohonan',
        'tanggal_borang_dikirim',
        'tanggal_draft_borang',
        'tanggal_review_kesiapan',
        'tanggal_pembayaran',
        'tanggal_borang_final',
        'tanggal_lanjut_ak',
        'tanggal_validasi_borang_assigned',
        'tanggal_validasi_borang_selesai',
        'tanggal_ak_mulai',
        'tanggal_ak_selesai',
        'tanggal_al_mulai',
        'tanggal_al_selesai',
        'tanggal_hasil_akreditasi',
        'tanggal_banding',
        'tanggal_penetapan',
        'tanggal_pengumuman',
        'tanggal_penyimpanan',
        // Status should be controlled
        'status',
    ];

    // ============================================
    // GUARDED: Protected from mass assignment
    // ============================================
    protected $guarded = [
        'id',
    ];

    // ============================================
    // TYPE CASTING
    // ============================================
    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_pengingat' => 'datetime',
        'tanggal_surat_permohonan' => 'datetime',
        'tanggal_borang_dikirim' => 'datetime',
        'tanggal_draft_borang' => 'datetime',
        'tanggal_review_kesiapan' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'tanggal_borang_final' => 'datetime',
        'tanggal_lanjut_ak' => 'datetime',
        'tanggal_validasi_borang_assigned' => 'datetime',
        'tanggal_validasi_borang_selesai' => 'datetime',
        'tanggal_ak_mulai' => 'datetime',
        'tanggal_ak_selesai' => 'datetime',
        'tanggal_al_mulai' => 'datetime',
        'tanggal_al_selesai' => 'datetime',
        'tanggal_hasil_akreditasi' => 'datetime',
        'tanggal_banding' => 'datetime',
        'tanggal_penetapan' => 'datetime',
        'tanggal_pengumuman' => 'datetime',
        'tanggal_penyimpanan' => 'datetime',
    ];

    public function asesmen()
    {
        return $this->hasOne(Asesmen::class, 'id_pengajuan');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_program_studi');
    }

    public function programStudi()
    {
        return $this->belongsTo(StudyProgram::class, 'id_program_studi');
    }

    // Alias untuk backward compatibility
    public function getProgramStudiAttribute()
    {
        return $this->studyProgram;
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'id_user_pengaju');
    }

    public function deskEvaluator()
    {
        return $this->belongsTo(User::class, 'id_de_assigned');
    }

    public function dokumen()
    {
        return $this->hasMany(PengajuanDokumen::class, 'id_pengajuan');
    }

    public function borangData()
    {
        return $this->hasMany(BorangData::class, 'id_pengajuan');
    }

    public function reviewKesiapan()
    {
        return $this->hasMany(ReviewKesiapan::class, 'id_pengajuan');
    }

    public function pembayaran()
    {
        return $this->hasOne(PembayaranAkreditasi::class, 'id_pengajuan');
    }

    public function statusLog()
    {
        return $this->hasMany(PengajuanStatusLog::class, 'id_pengajuan');
    }

    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('id_program_studi', $prodiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function borangImports()
    {
        return $this->hasMany(BorangImport::class, 'id_pengajuan');
    }

    /**
     * Get borang validation (latest)
     */
    public function borangValidation()
    {
        return $this->hasOneThrough(
            BorangValidation::class,
            Asesmen::class,
            'id_pengajuan',      // Foreign key on asesmen table
            'id_pengajuan',      // Foreign key on borang_validations table
            'id',                // Local key on pengajuan table
            'id'                 // Local key on asesmen table
        )->latest();
    }

    /**
     * Get all borang validations (if reassigned multiple times)
     */
    public function borangValidations()
    {
        return $this->hasManyThrough(
            BorangValidation::class,
            Asesmen::class,
            'id_pengajuan',
            'id_pengajuan'
        );
    }

    public function borangRevisionHistory()
    {
        return $this->hasMany(BorangRevisionHistory::class, 'id_pengajuan')
            ->orderBy('revision_number', 'desc');
    }

    public function getLatestBorangRevision()
    {
        return $this->borangRevisionHistory()->first();
    }

    public function getTotalBorangRevisions()
    {
        return $this->borangRevisionHistory()->count();
    }

    public function hasBorangRevisions()
    {
        return $this->borangRevisionHistory()->exists();
    }

    public function latestBorangImport()
    {
        return $this->hasOne(BorangImport::class, 'id_pengajuan')->latest();
    }

    // Helper Methods
    public static function generateNomorPengajuan()
    {
        $year = date('Y');
        $lastNumber = self::where('nomor_pengajuan', 'like', "AK/$year/%")
            ->orderBy('nomor_pengajuan', 'desc')
            ->first();

        if ($lastNumber) {
            $lastNum = (int) substr($lastNumber->nomor_pengajuan, -3);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return sprintf('AK/%s/%03d', $year, $newNum);
    }

    public function getStatusLabelAttribute()
    {
        return $this->statusMap()[$this->status]['label']
            ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->statusMap()[$this->status]['bg'] ?? 'bg-secondary';
    }

    public function getStatusIconAttribute()
    {
        return $this->statusMap()[$this->status]['icon'] ?? 'bi-question-circle';
    }

    /**
     * Get current validator assignment for borang
     */
    public function currentBorangValidator()
    {
        return $this->hasOne(AsesmenUserRole::class, 'id_asesmen', 'id')
            ->join('asesmens', 'asesmen_user_roles.id_asesmen', '=', 'asesmens.id')
            ->where('asesmens.id_pengajuan', $this->id)
            ->where('asesmen_user_roles.jenis_asesmen', 'borang')
            ->whereIn('asesmen_user_roles.status_penawaran', ['pending', 'accepted'])
            ->latest('asesmen_user_roles.created_at');
    }

    /**
     * Get all borang validator assignments (including rejected)
     */
    public function borangValidators()
    {
        return $this->hasManyThrough(
            AsesmenUserRole::class,
            Asesmen::class,
            'id_pengajuan', // FK on asesmens table
            'id_asesmen',   // FK on asesmen_user_roles table
            'id',           // Local key on pengajuan_akreditasi
            'id'            // Local key on asesmens
        )->where('jenis_asesmen', 'dokumen');
    }

    /**
     * Check if validator can be assigned
     */
    public function canAssignValidator(): bool
    {
        // Borang harus completed
        if (!in_array($this->status, ['borang_online_selesai', 'borang_validation_pending', 'borang_revision_required'])) {
            return false;
        }

        // Must have borang import
        if (!$this->latestBorangImport) {
            return false;
        }

        return true;
    }

    /**
     * Get active borang validator assignment
     */
    public function getCurrentBorangValidator()
    {
        if (!$this->asesmen) {
            return null;
        }

        return $this->asesmen->userRoles()
            ->with(['user', 'role', 'borangValidation.revisionHistory'])
            ->where('jenis_asesmen', 'dokumen')
            ->whereIn('status_penawaran', ['pending', 'accepted'])
            ->latest('created_at')
            ->first();
    }

    /**
     * Check if has active validator
     */
    public function hasActiveValidator(): bool
    {
        return $this->getCurrentBorangValidator() !== null;
    }

    /**
     * Check if user already assigned as validator
     */
    public function isUserAssignedAsValidator(int $userId): bool
    {
        return $this->borangValidators()
            ->where('id_user', $userId)
            ->exists();
    }

    /**
     * Get active validator (accepted)
     */
    public function activeBorangValidator()
    {
        return $this->borangValidators()
            ->where('status_penawaran', 'accepted')
            ->first();
    }

    /**
     * Count validators by status
     */
    public function countValidatorsByStatus(string $status): int
    {
        return $this->borangValidators()
            ->where('status_penawaran', $status)
            ->count();
    }

    public static function statusMap(): array
    {
        return [
            'draft' => [
                'label' => 'Draft',
                'bg' => 'bg-secondary',
                'icon' => 'bi-pencil',
            ],

            'pengingat_dikirim' => [
                'label' => 'Pengingat Masa Akreditasi Dikirim',
                'bg' => 'bg-secondary',
                'icon' => 'bi-bell',
            ],
            'surat_permohonan_diterima' => [
                'label' => 'Surat Permohonan PS Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-envelope-check',
            ],

            'borang_dikirim' => [
                'label' => 'Penyampaian Template LED',
                'bg' => 'bg-primary',
                'icon' => 'bi-send',
            ],
            'draft_borang_diterima' => [
                'label' => 'Dokumen Draft LED Diterima',
                'bg' => 'bg-warning',
                'icon' => 'bi-file-earmark-text',
            ],

            'review_kesiapan_belum_siap' => [
                'label' => 'Review Kesiapan: Belum Siap',
                'bg' => 'bg-danger',
                'icon' => 'bi-x-circle',
            ],
            'review_kesiapan_siap' => [
                'label' => 'Review Kesiapan: Siap',
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle',
            ],

            'menunggu_pembayaran' => [
                'label' => 'Menunggu Pembayaran',
                'bg' => 'bg-warning',
                'icon' => 'bi-hourglass-split',
            ],
            'pembayaran_diterima' => [
                'label' => 'Pembayaran Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-credit-card',
            ],

            'borang_final_diterima' => [
                'label' => 'LED PS Final Diterima',
                'bg' => 'bg-primary',
                'icon' => 'bi-file-earmark-check',
            ],
            'borang_online_selesai' => [
                'label' => 'LED PS Final Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-ui-checks',
            ],

            'pengajuan_completed' => [
                'label' => 'Pengajuan Akreditasi Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-check2-all',
            ],

            'ak_in_progress' => [
                'label' => 'Proses Penilaian Dokumen (AK)',
                'bg' => 'bg-primary',
                'icon' => 'bi-clipboard-data',
            ],
            'ak_completed' => [
                'label' => 'Penilaian Dokumen (AK) Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-check',
            ],

            'al_in_progress' => [
                'label' => 'Proses Asesmen Lapangan (AL)',
                'bg' => 'bg-primary',
                'icon' => 'bi-building',
            ],
            'al_completed' => [
                'label' => 'Asesmen Lapangan (AL) Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-building-check',
            ],

            'selesai' => [
                'label' => 'Asesmen Selesai',
                'bg' => 'bg-dark',
                'icon' => 'bi-flag-fill',
            ],
            'ditolak' => [
                'label' => 'Asesmen Ditolak',
                'bg' => 'bg-danger',
                'icon' => 'bi-x-octagon',
            ],
        ];
    }
}
