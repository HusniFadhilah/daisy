<?php
// app/Models/PengajuanAkreditasi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanAkreditasi extends Model
{
    protected $table = 'pengajuan_akreditasi';

    public const DAFTAR_TEMPLATE = 'Template LED+Suplemen dan LKPS';
    // ============================================
    // STATUS CONSTANTS (20 Steps)
    // ============================================
    public const STATUS_DRAFT = 'draft';

    // Step 1
    public const STATUS_PENGINGAT_DIKIRIM = 'pengingat_dikirim';

    // Step 2
    public const STATUS_SURAT_PERMOHONAN_DITERIMA = 'surat_permohonan_diterima';

    // Step 3
    public const STATUS_TEMPLATE_LED_DIKIRIM = 'template_led_dikirim';

    // Step 4
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_PEMBAYARAN_DITERIMA = 'pembayaran_diterima';
    public const STATUS_PEMBAYARAN_DIVERIFIKASI = 'pembayaran_diverifikasi';

    // Step 5
    public const STATUS_DRAFT_BORANG_DITERIMA = 'draft_borang_diterima';
    public const STATUS_BORANG_ONLINE_SELESAI = 'borang_online_selesai';

    // Step 6-7
    public const STATUS_BORANG_VALIDATION_PENDING = 'borang_validation_pending';
    public const STATUS_BORANG_IN_VALIDATION = 'borang_in_validation';
    public const STATUS_BORANG_REVISION_REQUIRED = 'borang_revision_required';
    public const STATUS_BORANG_VALIDATED = 'borang_validated';
    public const STATUS_VALIDASI_BORANG_DILAPORKAN = 'validasi_borang_dilaporkan';

    // Step 8-10
    public const STATUS_ASESOR_AK_ASSIGNED = 'asesor_ak_assigned';
    public const STATUS_AK_IN_PROGRESS = 'ak_in_progress';
    public const STATUS_AK_SELESAI = 'ak_selesai';
    public const STATUS_AK_DILAPORKAN = 'ak_dilaporkan';

    // Step 11-13
    public const STATUS_ASESOR_AL_ASSIGNED = 'asesor_al_assigned';
    public const STATUS_AL_IN_PROGRESS = 'al_in_progress';
    public const STATUS_AL_SELESAI = 'al_selesai';
    public const STATUS_AL_DILAPORKAN = 'al_dilaporkan';

    // Step 14-20
    public const STATUS_HASIL_AKREDITASI_DIKIRIM = 'hasil_akreditasi_dikirim';
    public const STATUS_MASA_SANGGAH = 'masa_sanggah';
    public const STATUS_BANDING_DILAKSANAKAN = 'banding_dilaksanakan';
    public const STATUS_BANDING_DILAPORKAN = 'banding_dilaporkan';
    public const STATUS_HASIL_DITETAPKAN = 'hasil_ditetapkan';
    public const STATUS_HASIL_DILAPORKAN = 'hasil_dilaporkan';
    public const STATUS_ARSIP_DISIMPAN = 'arsip_disimpan';

    // Special
    public const STATUS_DITOLAK = 'ditolak';

    // ============================================
    // FILLABLE
    // ============================================
    protected $fillable = [
        'nomor_pengajuan',
        'id_program_studi',
        'id_user_pengaju',
        'id_de_assigned',
        'tahun_akreditasi',
        'jenis_akreditasi',
        'tanggal_pengajuan',
        'catatan_pengaju',
        'status',

        // Timeline fields
        'tanggal_pengingat',
        'tanggal_surat_permohonan',
        'tanggal_template_led_dikirim',
        'tanggal_pembayaran',
        'tanggal_draft_borang',
        'tanggal_borang_final',
        'tanggal_lanjut_ak',
        'tanggal_validasi_borang_assigned',
        'tanggal_validasi_borang_selesai',
        'tanggal_pelaporan_validasi_borang',
        'tanggal_penugasan_asesor_ak',
        'tanggal_validasi_ak',
        'tanggal_ak_mulai',
        'tanggal_ak_selesai',
        'tanggal_pelaporan_ak',
        'tanggal_penugasan_asesor_al',
        'tanggal_pelaksanaan_al',
        'tanggal_al_mulai',
        'tanggal_al_selesai',
        'tanggal_pelaporan_al',
        'tanggal_hasil_akreditasi',
        'tanggal_masa_sanggah_mulai',
        'tanggal_masa_sanggah_selesai',
        'tanggal_banding',
        'tanggal_pelaksanaan_banding',
        'tanggal_pelaporan_banding',
        'tanggal_penetapan',
        'tanggal_pengumuman',
        'tanggal_pelaporan_hasil',
        'tanggal_penyimpanan',
    ];

    // ============================================
    // TYPE CASTING
    // ============================================
    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_pengingat' => 'datetime',
        'tanggal_surat_permohonan' => 'datetime',
        'tanggal_template_led_dikirim' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'tanggal_draft_borang' => 'datetime',
        'tanggal_borang_final' => 'datetime',
        'tanggal_lanjut_ak' => 'datetime',
        'tanggal_validasi_borang_assigned' => 'datetime',
        'tanggal_validasi_borang_selesai' => 'datetime',
        'tanggal_pelaporan_validasi_borang' => 'datetime',
        'tanggal_penugasan_asesor_ak' => 'datetime',
        'tanggal_validasi_ak' => 'datetime',
        'tanggal_ak_mulai' => 'datetime',
        'tanggal_ak_selesai' => 'datetime',
        'tanggal_pelaporan_ak' => 'datetime',
        'tanggal_penugasan_asesor_al' => 'datetime',
        'tanggal_pelaksanaan_al' => 'datetime',
        'tanggal_al_mulai' => 'datetime',
        'tanggal_al_selesai' => 'datetime',
        'tanggal_pelaporan_al' => 'datetime',
        'tanggal_hasil_akreditasi' => 'datetime',
        'tanggal_masa_sanggah_mulai' => 'datetime',
        'tanggal_masa_sanggah_selesai' => 'datetime',
        'tanggal_banding' => 'datetime',
        'tanggal_pelaksanaan_banding' => 'datetime',
        'tanggal_pelaporan_banding' => 'datetime',
        'tanggal_penetapan' => 'datetime',
        'tanggal_pengumuman' => 'datetime',
        'tanggal_pelaporan_hasil' => 'datetime',
        'tanggal_penyimpanan' => 'datetime',
    ];

    // ============================================
    // RELATIONSHIPS (unchanged)
    // ============================================
    public function asesmen()
    {
        return $this->hasOne(Asesmen::class, 'id_pengajuan');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_program_studi');
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

    public function pembayaran()
    {
        return $this->hasOne(PembayaranAkreditasi::class, 'id_pengajuan');
    }

    public function statusLog()
    {
        return $this->hasMany(PengajuanStatusLog::class, 'id_pengajuan');
    }

    public function borangImports()
    {
        return $this->hasMany(BorangImport::class, 'id_pengajuan');
    }

    public function latestBorangImport()
    {
        return $this->hasOne(BorangImport::class, 'id_pengajuan')->latest();
    }

    public function borangValidators()
    {
        return $this->hasManyThrough(
            AsesmenUserRole::class,
            Asesmen::class,
            'id_pengajuan',
            'id_asesmen',
            'id',
            'id'
        )->where('jenis_asesmen', 'dokumen');
    }

    // ============================================
    // HELPER METHODS
    // ============================================
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

    public function canAssignValidator(): bool
    {
        if (!in_array($this->status, [
            self::STATUS_BORANG_ONLINE_SELESAI,
            self::STATUS_BORANG_VALIDATION_PENDING,
            self::STATUS_BORANG_REVISION_REQUIRED
        ])) {
            return false;
        }

        if (!$this->latestBorangImport) {
            return false;
        }

        return true;
    }

    public function getCurrentBorangValidator()
    {
        if (!$this->asesmen) {
            return null;
        }

        return $this->asesmen->userRoles()
            ->with(['user', 'role_selected', 'borangValidation'])
            ->where('jenis_asesmen', 'dokumen')
            ->whereIn('status_penawaran', ['pending', 'accepted'])
            ->latest('created_at')
            ->first();
    }

    // ============================================
    // ATTRIBUTES
    // ============================================
    public function getStatusLabelAttribute(): string
    {
        return self::statusMap()[$this->status]['label'] ?? ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusMap()[$this->status]['bg'] ?? 'bg-secondary';
    }

    // ============================================
    // STATUS MAP (Updated for 20 steps)
    // ============================================
    public static function statusMap(): array
    {
        return [
            self::STATUS_DRAFT => [
                'label' => 'Draft',
                'bg' => 'bg-secondary',
                'icon' => 'bi-pencil',
            ],
            self::STATUS_PENGINGAT_DIKIRIM => [
                'label' => 'Pengingat Masa Akreditasi',
                'bg' => 'bg-info',
                'icon' => 'bi-bell',
            ],
            self::STATUS_SURAT_PERMOHONAN_DITERIMA => [
                'label' => 'Surat Permohonan dari PS',
                'bg' => 'bg-primary',
                'icon' => 'bi-envelope',
            ],
            self::STATUS_TEMPLATE_LED_DIKIRIM => [
                'label' => 'Penyampaian Template LED+Suplemen dan LKPS',
                'bg' => 'bg-primary',
                'icon' => 'bi-file-earmark-arrow-down',
            ],
            self::STATUS_MENUNGGU_PEMBAYARAN => [
                'label' => 'Menunggu Pembayaran',
                'bg' => 'bg-warning',
                'icon' => 'bi-hourglass-split',
            ],
            self::STATUS_PEMBAYARAN_DITERIMA => [
                'label' => 'Pembayaran Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-credit-card',
            ],
            self::STATUS_PEMBAYARAN_DIVERIFIKASI => [
                'label' => 'Validasi Pembayaran Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle',
            ],
            self::STATUS_DRAFT_BORANG_DITERIMA => [
                'label' => 'Draft LED Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-check',
            ],
            self::STATUS_BORANG_ONLINE_SELESAI => [
                'label' => 'LED Online Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-ui-checks',
            ],
            self::STATUS_BORANG_VALIDATION_PENDING => [
                'label' => 'Menunggu Validasi LED',
                'bg' => 'bg-warning',
                'icon' => 'bi-clock-history',
            ],
            self::STATUS_BORANG_IN_VALIDATION => [
                'label' => 'Validasi LED Berlangsung',
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-check',
            ],
            self::STATUS_BORANG_REVISION_REQUIRED => [
                'label' => 'LED Perlu Revisi',
                'bg' => 'bg-danger',
                'icon' => 'bi-exclamation-triangle',
            ],
            self::STATUS_BORANG_VALIDATED => [
                'label' => 'LED Divalidasi',
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
            ],
            self::STATUS_VALIDASI_BORANG_DILAPORKAN => [
                'label' => 'Pelaporan Validasi LED',
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-text',
            ],
            self::STATUS_ASESOR_AK_ASSIGNED => [
                'label' => 'Penugasan Asesor AK',
                'bg' => 'bg-primary',
                'icon' => 'bi-person-check',
            ],
            self::STATUS_AK_IN_PROGRESS => [
                'label' => 'Validasi AK Berlangsung',
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-data',
            ],
            self::STATUS_AK_SELESAI => [
                'label' => 'Validasi AK Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-check',
            ],
            self::STATUS_AK_DILAPORKAN => [
                'label' => 'Pelaporan AK',
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-medical',
            ],
            self::STATUS_ASESOR_AL_ASSIGNED => [
                'label' => 'Penugasan Asesor AL',
                'bg' => 'bg-primary',
                'icon' => 'bi-person-badge',
            ],
            self::STATUS_AL_IN_PROGRESS => [
                'label' => 'Pelaksanaan AL Berlangsung',
                'bg' => 'bg-info',
                'icon' => 'bi-building',
            ],
            self::STATUS_AL_SELESAI => [
                'label' => 'Pelaksanaan AL Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-building-check',
            ],
            self::STATUS_AL_DILAPORKAN => [
                'label' => 'Pelaporan AL',
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-data',
            ],
            self::STATUS_HASIL_AKREDITASI_DIKIRIM => [
                'label' => 'Penyampaian Hasil Akreditasi',
                'bg' => 'bg-warning',
                'icon' => 'bi-envelope-paper',
            ],
            self::STATUS_MASA_SANGGAH => [
                'label' => 'Masa Sanggah',
                'bg' => 'bg-warning',
                'icon' => 'bi-clock-history',
            ],
            self::STATUS_BANDING_DILAKSANAKAN => [
                'label' => 'Pelaksanaan Banding',
                'bg' => 'bg-danger',
                'icon' => 'bi-arrow-repeat',
            ],
            self::STATUS_BANDING_DILAPORKAN => [
                'label' => 'Pelaporan Banding',
                'bg' => 'bg-danger',
                'icon' => 'bi-file-earmark-ruled',
            ],
            self::STATUS_HASIL_DITETAPKAN => [
                'label' => 'Penetapan Hasil Akreditasi',
                'bg' => 'bg-success',
                'icon' => 'bi-award',
            ],
            self::STATUS_HASIL_DILAPORKAN => [
                'label' => 'Pelaporan Hasil Akreditasi',
                'bg' => 'bg-success',
                'icon' => 'bi-megaphone',
            ],
            self::STATUS_ARSIP_DISIMPAN => [
                'label' => 'Penyimpanan Arsip',
                'bg' => 'bg-dark',
                'icon' => 'bi-archive',
            ],
            self::STATUS_DITOLAK => [
                'label' => 'Ditolak',
                'bg' => 'bg-danger',
                'icon' => 'bi-x-octagon',
            ],
        ];
    }
}
