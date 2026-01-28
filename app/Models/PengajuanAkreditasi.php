<?php
// app/Models/PengajuanAkreditasi.php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PengajuanAkreditasi extends Model
{
    protected $table = 'pengajuan_akreditasi';

    public const KELOMPOK_INDIVIDUAL = 'individual';
    public const KELOMPOK_KELOMPOK = 'kelompok';
    public const AKREDITASI_BARU = 'baru';
    public const AKREDITASI_PERPANJANGAN = 'perpanjangan';
    public const AKREDITASI_MENUJU_UNGGUL = 'menuju_unggul';

    public const DAFTAR_TEMPLATE = 'Template Dokumen';
    // ============================================
    // STATUS CONSTANTS (20 Steps)
    // ============================================
    public const STATUS_DRAFT = 'draft';

    // Step 1
    public const STATUS_PENGINGAT_DIKIRIM = 'pengingat_dikirim';

    // Step 2
    public const STATUS_SURAT_PERMOHONAN_DIKIRIM = 'surat_permohonan_dikirim';
    public const STATUS_SURAT_PERMOHONAN_DITERIMA = 'surat_permohonan_diterima';
    public const STATUS_SURAT_PERMOHONAN_DITOLAK = 'surat_permohonan_ditolak';

    // Step 3
    public const STATUS_TEMPLATE_LED_DIKIRIM = 'template_borang_dikirim';

    // Step 4
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_PEMBAYARAN_DITERIMA = 'pembayaran_diterima'; // tdk terpakai
    public const STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN = 'menunggu_verifikasi_pembayaran';
    public const STATUS_PEMBAYARAN_DIVERIFIKASI = 'pembayaran_diverifikasi';

    // Step 5
    public const STATUS_DRAFT_BORANG_DIKIRIM = 'draft_borang_dikirim';
    public const STATUS_DRAFT_BORANG_DITERIMA = 'draft_borang_diterima';
    public const STATUS_BORANG_ONLINE_SELESAI = 'borang_online_selesai';

    // Step 6-7
    public const STATUS_BORANG_VALIDATION_PENDING = 'borang_validation_pending';
    public const STATUS_BORANG_IN_VALIDATION = 'borang_in_validation';
    public const STATUS_BORANG_REVISION_REQUIRED = 'borang_revision_required';
    public const STATUS_BORANG_VALIDATED = 'borang_validated';
    public const STATUS_BORANG_FINAL_DITERIMA = 'borang_final_diterima';
    public const STATUS_VALIDASI_BORANG_DILAPORKAN = 'validasi_borang_dilaporkan';
    public const STATUS_PENGAJUAN_COMPLETED = 'pengajuan_completed';

    // Step 8-10
    public const STATUS_ASESOR_AK_ASSIGNED = 'asesor_ak_assigned';
    public const STATUS_AK_IN_PROGRESS = 'ak_in_progress';
    public const STATUS_AK_ON_VALIDATION = 'ak_on_validation';
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
    public const STATUS_BANDING_DIAJUKAN = 'banding_diajukan';
    public const STATUS_BANDING_DILAKSANAKAN = 'banding_dilaksanakan';
    public const STATUS_BANDING_DILAPORKAN = 'banding_dilaporkan';
    public const STATUS_HASIL_DITETAPKAN = 'hasil_ditetapkan';
    public const STATUS_HASIL_DIUMUMKAN = 'hasil_diumumkan';
    public const STATUS_HASIL_DILAPORKAN = 'hasil_dilaporkan';
    public const STATUS_ARSIP_DISIMPAN = 'arsip_disimpan';
    public const STATUS_SELESAI = 'selesai';

    // Special
    public const STATUS_DITOLAK = 'ditolak';

    // Other
    public const STATUS_REMINDER_PENGIRIMAN_BORANG = 'reminder_pengiriman_borang';

    // ============================================
    // FILLABLE
    // ============================================
    protected $fillable = [
        'nomor_pengajuan',
        'id_program_studi',
        'id_user_pengaju',
        'id_de_assigned',
        'id_validator_assigned',
        'tahun_akreditasi',
        'jenis_akreditasi',
        'kelompok_akreditasi',
        'tanggal_pengajuan',
        'catatan_pengaju',
        'status',

        // Timeline fields
        'tanggal_pengingat',
        'tanggal_surat_permohonan_dikirim',
        'tanggal_surat_permohonan_diterima',
        'tanggal_surat_permohonan_ditolak',
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
        'is_active',
        'is_example'
    ];

    // ============================================
    // TYPE CASTING
    // ============================================
    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_pengingat' => 'datetime',
        'tanggal_surat_permohonan_dikirim' => 'datetime',
        'tanggal_surat_permohonan_diterima' => 'datetime',
        'tanggal_surat_permohonan_ditolak' => 'datetime',
        'tanggal_template_led_dikirim' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'tanggal_draft_borang' => 'datetime',
        'tanggal_borang_final' => 'datetime',
        'tanggal_lanjut_ak' => 'datetime',
        'tanggal_validasi_borang_assigned' => 'datetime',
        'tanggal_validasi_borang_selesai' => 'datetime',
        'tanggal_pelaporan_validasi_borang' => 'datetime',
        'tanggal_penugasan_asesor_ak' => 'datetime',
        'tanggal_ak_mulai' => 'datetime',
        'tanggal_validasi_ak' => 'datetime',
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

    /**
     * Get kelompok akreditasi options
     */
    public static function kelompokAkreditasiOptions()
    {
        return [
            self::KELOMPOK_INDIVIDUAL => 'Individual (Per Program Studi)',
            self::KELOMPOK_KELOMPOK => 'Kelompok (Beberapa Program Studi)',
        ];
    }

    public static function jenisAkreditasiOptions()
    {
        return [
            self::AKREDITASI_BARU => 'Akreditasi untuk pembukaan prodi baru',
            self::AKREDITASI_PERPANJANGAN => 'Akreditasi untuk pemenuhan status Terakreditasi',
            self::AKREDITASI_MENUJU_UNGGUL => 'Akreditasi untuk menuju status Unggul',
        ];
    }

    /**
     * Get kelompok akreditasi label
     */
    public function getKelompokAkreditasiLabelAttribute()
    {
        $options = self::kelompokAkreditasiOptions();
        return $options[$this->kelompok_akreditasi] ?? 'Individual';
    }

    public function getJenisAkreditasiTitleAttribute()
    {
        $options = self::jenisAkreditasiOptions();
        return $options[$this->jenis_akreditasi] ?? 'Akreditasi untuk pemenuhan status Terakreditasi';
    }

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

    public function validator()
    {
        return $this->belongsTo(User::class, 'id_validator_assigned');
    }

    public function dokumen()
    {
        return $this->hasMany(PengajuanDokumen::class, 'id_pengajuan');
    }

    // public function pembayaran()
    // {
    //     return $this->hasOne(PembayaranAkreditasi::class, 'id_pengajuan');
    // }

    public function borangData()
    {
        return $this->hasMany(BorangData::class, 'id_pengajuan');
    }

    public function pengingatAkreditasi()
    {
        return $this->hasOne(PengingatAkreditasi::class, 'id_pengajuan');
    }

    public function statusLog()
    {
        return $this->hasMany(PengajuanStatusLog::class, 'id_pengajuan');
    }

    public function latestStatusLog()
    {
        return $this->hasOne(PengajuanStatusLog::class, 'id_pengajuan')
            ->latestOfMany('created_at'); // atau 'id' kalau id selalu urut waktu
    }

    public function latestChangedAtStatusLog()
    {
        return $this->hasOne(PengajuanStatusLog::class, 'id_pengajuan')
            ->latestOfMany('changed_at'); // atau 'id' kalau id selalu urut waktu
    }

    public function lastBorangValidationLog()
    {
        $statuses = [
            self::STATUS_BORANG_VALIDATION_PENDING,
            self::STATUS_BORANG_IN_VALIDATION,
            self::STATUS_BORANG_REVISION_REQUIRED,
            self::STATUS_BORANG_VALIDATED,
            self::STATUS_VALIDASI_BORANG_DILAPORKAN,
        ];

        return $this->hasOne(PengajuanStatusLog::class, 'id_pengajuan')
            ->whereIn('status_to', $statuses)
            ->latestOfMany('changed_at'); // ambil 1 paling baru
    }

    public function borangValidation()
    {
        return $this->hasOne(BorangValidation::class, 'id_pengajuan');
    }

    public function borangImports()
    {
        return $this->hasMany(BorangImport::class, 'id_pengajuan');
    }

    public function pembayaran()
    {
        return $this->hasOne(PengajuanPembayaran::class, 'id_pengajuan', 'id');
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

    public function assignments()
    {
        return $this->hasManyThrough(
            AsesmenUserRole::class,
            Asesmen::class,
            'id_pengajuan',
            'id_asesmen',
            'id',
            'id'
        );
    }

    // ============================================
    // HELPER METHODS
    // ============================================
    public static function generateNomorPengajuan(?string $jenisAkreditasi = null): string
    {
        $year = date('Y');

        $jenis = strtolower($jenisAkreditasi ?? request('jenis_akreditasi') ?? '');

        // Kode jenis (baru = default, TANPA kode)
        $kodeJenis = match ($jenis) {
            'perpanjangan' => 'PRP',
            'menuju_unggul', 'menuju-unggul', 'unggul' => 'MENUJU-UNGGUL',
            default => null, // BARU
        };

        // Prefix pencarian
        $prefix = $kodeJenis
            ? "LAMDEPILAR/{$year}/{$kodeJenis}/"
            : "LAMDEPILAR/{$year}/";

        $last = self::where('nomor_pengajuan', 'like', $prefix . '%')
            ->orderBy('nomor_pengajuan', 'desc')
            ->first();

        $newNum = 1;
        if ($last) {
            $lastNum = (int) substr($last->nomor_pengajuan, -3);
            $newNum = $lastNum + 1;
        }

        // Format akhir
        return $kodeJenis
            ? sprintf('LAMDEPILAR/%s/%s/%03d', $year, $kodeJenis, $newNum)
            : sprintf('LAMDEPILAR/%s/%03d', $year, $newNum);
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

    public function setValidatorAssigned($idUser)
    {
        $this->update([
            'id_validator_assigned' => $idUser
        ]);
        $this->borangValidation->update(['id_validator_assigned' => $idUser]);
    }

    public function checkUpdateStatusAKAL($jenisAsesmen, $statusToUpdate, $idUser = null)
    {
        if ($statusToUpdate == 'status_asesor_assigned') {
            if ($jenisAsesmen == 'ak') {
                if ($this->status == PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED)
                    $this->update([
                        'status' => PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                        'tanggal_penugasan_asesor_ak' => now(),
                    ]);
            }
            if ($jenisAsesmen == 'al') {
                if ($this->status == PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
                    $this->update([
                        'status' => PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                        'tanggal_penugasan_asesor_al' => now()
                    ]);
            }
        }
        if ($statusToUpdate == 'status_asesor_in_progress') {
            if ($jenisAsesmen == 'ak') {
                if ($this->status == PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS, 'tanggal_ak_mulai' => now()]);
            }
            if ($jenisAsesmen == 'al') {
                if ($this->status == PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS, 'tanggal_al_mulai' => now()]);
            }
        }
        if ($statusToUpdate == 'status_asesor_on_validation') {
            if ($jenisAsesmen == 'ak') {
                if ($this->status == PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AK_ON_VALIDATION, 'tanggal_validasi_ak' => now()]);
            }
        }
        if ($statusToUpdate == 'status_asesor_selesai') {
            if ($jenisAsesmen == 'ak') {
                if ($this->status == PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AK_SELESAI, 'tanggal_ak_selesai' => now()]);
            }
            if ($jenisAsesmen == 'al') {
                if ($this->status == PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AL_SELESAI, 'tanggal_al_selesai' => now()]);
            }
        }
        if ($statusToUpdate == 'status_asesor_dilaporkan') {
            if ($jenisAsesmen == 'ak') {
                if ($this->status == PengajuanAkreditasi::STATUS_AK_SELESAI)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AK_DILAPORKAN, 'tanggal_pelaporan_ak' => now()]);
            }
            if ($jenisAsesmen == 'al') {
                if ($this->status == PengajuanAkreditasi::STATUS_AL_SELESAI)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_AL_DILAPORKAN, 'tanggal_pelaporan_al' => now()]);
            }
        }
        if ($statusToUpdate == 'status_hasil_akreditasi_disampaikan') {
            if ($jenisAsesmen == 'al') {
                if ($this->status == PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
                    $this->update(['status' => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM, 'tanggal_hasil_akreditasi' => now()]);
            }
        }
    }

    public function canBeReported(string $jenisAsesmen): bool
    {
        return match ($jenisAsesmen) {
            'ak' => $this->status === self::STATUS_AK_SELESAI
                && is_null($this->tanggal_pelaporan_ak),

            'al' => $this->status === self::STATUS_AL_SELESAI
                && is_null($this->tanggal_pelaporan_al),

            'dokumen' => in_array($this->status, [
                self::STATUS_BORANG_VALIDATED,
                self::STATUS_BORANG_FINAL_DITERIMA,
            ], true)
                && is_null($this->tanggal_pelaporan_validasi_borang),

            default => false,
        };
    }

    public function getPelaporanBadge(string $jenis): ?string
    {
        if ($jenis === 'dokumen' && $this->tanggal_pelaporan_validasi_borang) return 'Pelaporan Dokumen telah Dibuat';
        if ($jenis === 'ak' && $this->tanggal_pelaporan_ak) return 'Pelaporan AK telah Dibuat';
        if ($jenis === 'al' && $this->tanggal_pelaporan_al) return 'Pelaporan AL telah Dibuat';
        return null;
    }

    // ============================================
    // ATTRIBUTES
    // ============================================
    public function getStatusLabelAttribute(): string
    {
        return self::statusMap()[$this->status]['label'] ?? ucwords(str_replace('_', ' ', $this->status));
    }

    public function getJenisAkreditasiLabelAttribute(): string
    {
        return $this->jenis_akreditasi == 'perpanjangan' ? 'Pemenuhan Status Terakreditasi' : Str::title(str_replace('_', ' ', $this->jenis_akreditasi));
    }

    public function getJudulAttribute(): string
    {
        $pengajuan = $this;
        $prodi = $pengajuan->studyProgram->name ?? '-';

        $jenis = strtolower($pengajuan->jenis_akreditasi ?? '');

        $prefix = match ($jenis) {
            'perpanjangan' => 'Permohonan Akreditasi untuk Pemenuhan Status Terakreditasi Prodi',
            'menuju_unggul', 'menuju-unggul', 'unggul' => 'Permohonan Akreditasi Menuju Unggul Prodi',
            'baru' => 'Permohonan Akreditasi Baru Prodi',
            default => 'Akreditasi Prodi',
        };

        $tahun = $pengajuan->tahun_akreditasi ? ' ' . $pengajuan->tahun_akreditasi : '';
        $judul = "{$prefix} {$prodi}{$tahun}";
        return $judul;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusMap()[$this->status]['bg'] ?? 'bg-secondary';
    }

    public function getUploadedDocuments()
    {
        $dokumens = $this->dokumen()
            ->where('is_latest', true)
            ->latest()
            ->get();
        $uploadedFiles = [
            'led' => $dokumens->whereIn('jenis_dokumen', ['data_kualitatif', 'draft_borang', 'borang_final',])->first(),
            'suplemen' => $dokumens->whereIn('jenis_dokumen', ['data_suplemen', 'suplemen', 'file_suplemen', 'dokumen_pendukung'])->first(),
            'lkps' => $dokumens->whereIn('jenis_dokumen', ['data_kuantitatif', 'kuantitatif',])->first(),
            'pengesahan' => $dokumens->where('jenis_dokumen', 'lembar_pengesahan')->first(),
            'formulir_pembayaran' => $dokumens->where('jenis_dokumen', 'formulir_pembayaran')->first(),
            'surat_permohonan' => $dokumens->where('jenis_dokumen', 'surat_permohonan')->first(),
        ];
        return $uploadedFiles;
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
            self::STATUS_SURAT_PERMOHONAN_DIKIRIM => [
                'label' => 'Surat Permohonan Akreditasi',
                'bg' => 'bg-primary',
                'icon' => 'bi-envelope',
            ],
            self::STATUS_SURAT_PERMOHONAN_DITOLAK => [
                'label' => 'Surat Permohonan Akreditasi Ditolak',
                'bg' => 'bg-danger',
                'icon' => 'bi-envelope',
            ],
            self::STATUS_SURAT_PERMOHONAN_DITERIMA => [
                'label' => 'Surat Permohonan Akreditasi Diterima',
                'bg' => 'bg-success',
                'icon' => 'bi-envelope',
            ],
            self::STATUS_TEMPLATE_LED_DIKIRIM => [
                'label' => 'Pengiriman Formulir dan Template Dokumen',
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
            self::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN => [
                'label' => 'Menunggu Verifikasi Pembayaran',
                'bg' => 'bg-warning',
                'icon' => 'bi-shield-exclamation',
            ],
            self::STATUS_PEMBAYARAN_DIVERIFIKASI => [
                'label' => 'Validasi Pembayaran Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle',
            ],
            self::STATUS_DRAFT_BORANG_DIKIRIM => [
                'label' => 'File Dokumen Dikirim',
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-check',
            ],
            self::STATUS_DRAFT_BORANG_DITERIMA => [
                'label' => 'File Dokumen Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-check',
            ],
            self::STATUS_BORANG_ONLINE_SELESAI => [
                'label' => 'Dokumen Diterima',
                'bg' => 'bg-success',
                'icon' => 'bi-ui-checks',
            ],
            self::STATUS_BORANG_VALIDATION_PENDING => [
                'label' => 'Menunggu Validasi Dokumen',
                'bg' => 'bg-warning',
                'icon' => 'bi-clock-history',
            ],
            self::STATUS_BORANG_IN_VALIDATION => [
                'label' => 'Validasi Dokumen Berlangsung',
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-check',
            ],
            self::STATUS_BORANG_REVISION_REQUIRED => [
                'label' => 'Dokumen Perlu Revisi',
                'bg' => 'bg-danger',
                'icon' => 'bi-exclamation-triangle',
            ],
            self::STATUS_BORANG_VALIDATED => [
                'label' => 'Dokumen Divalidasi',
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
            ],
            self::STATUS_BORANG_FINAL_DITERIMA => [
                'label' => 'Draft Final Dokumen Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-arrow-up',
            ],
            self::STATUS_VALIDASI_BORANG_DILAPORKAN => [
                'label' => 'Pelaporan Validasi Dokumen',
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-text',
            ],
            self::STATUS_PENGAJUAN_COMPLETED => [
                'label' => 'Proses Penugasan Asesor AK',
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-text',
            ],
            self::STATUS_ASESOR_AK_ASSIGNED => [
                'label' => 'Penugasan Asesor AK',
                'bg' => 'bg-primary',
                'icon' => 'bi-person-check',
            ],
            self::STATUS_AK_IN_PROGRESS => [
                'label' => 'Penugasan Asesor AK Berlangsung',
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-data',
            ],
            self::STATUS_AK_ON_VALIDATION => [
                'label' => 'Validasi AK Berlangsung',
                'bg' => 'bg-warning',
                'icon' => 'bi-clipboard2-check',
            ],
            self::STATUS_AK_SELESAI => [
                'label' => 'Validasi AK Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-check',
            ],
            self::STATUS_AK_DILAPORKAN => [
                'label' => 'Pelaporan AK Selesai',
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
                'label' => 'Pelaksanaan AL dan Penyampaian Berita Acara AL Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-building-check',
            ],
            self::STATUS_AL_DILAPORKAN => [
                'label' => 'Pelaporan AL Selesai',
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
            self::STATUS_BANDING_DIAJUKAN => [
                'label' => 'Banding Diajukan',
                'bg' => 'bg-danger',
                'icon' => 'bi-file-earmark-break',
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
            self::STATUS_HASIL_DIUMUMKAN => [
                'label' => 'Hasil Akreditasi Diumumkan',
                'bg' => 'bg-primary',
                'icon' => 'bi-megaphone-fill',
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
            self::STATUS_SELESAI => [
                'label' => 'Proses Akreditasi Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
            ],
            self::STATUS_DITOLAK => [
                'label' => 'Ditolak',
                'bg' => 'bg-danger',
                'icon' => 'bi-x-octagon',
            ],
            // Other
            self::STATUS_REMINDER_PENGIRIMAN_BORANG => [
                'label' => 'Reminder Pengiriman Dokumen',
                'bg' => 'bg-warning',
                'icon' => 'bi-x-envelope-paper',
            ],
        ];
    }

    public function timelineItems(): array
    {
        $items = [
            1 => ['date' => $this->tanggal_pengingat, 'label' => 'Pengingat Masa Akreditasi', 'icon' => 'bi-bell'],
            2 => ['date' => ($this->tanggal_surat_permohonan_dikirim ?? $this->tanggal_surat_permohonan_diterima), 'label' => 'Surat Permohonan Akreditasi', 'icon' => 'bi-envelope'],
            3 => ['date' => $this->tanggal_template_led_dikirim, 'label' => 'Pengiriman Formulir dan Template Dokumen, formulir pembayaran', 'icon' => 'bi-file-earmark-arrow-down'],
            4 => ['date' => $this->tanggal_pembayaran, 'label' => 'Validasi pembayaran', 'icon' => 'bi-credit-card-2-front'],
            5 => ['date' => $this->tanggal_draft_borang, 'label' => 'Penerimaan Draft Dokumen dari Prodi', 'icon' => 'bi-file-earmark-check'],
            6 => ['date' => $this->tanggal_validasi_borang_assigned, 'label' => 'Validasi Dokumen', 'icon' => 'bi-clipboard-check'],
            7 => ['date' => $this->tanggal_pelaporan_validasi_borang, 'label' => 'Pelaporan Validasi Dokumen', 'icon' => 'bi-file-earmark-text'],
            8 => ['date' => $this->tanggal_penugasan_asesor_ak, 'label' => 'Penugasan asesor untuk AK', 'icon' => 'bi-person-check'],
            9 => ['date' => $this->tanggal_validasi_ak, 'label' => 'Validasi AK', 'icon' => 'bi-clipboard2-check'],
            10 => ['date' => $this->tanggal_pelaporan_ak, 'label' => 'Pelaporan AK', 'icon' => 'bi-file-earmark-medical'],
            11 => ['date' => $this->tanggal_penugasan_asesor_al, 'label' => 'Penugasan asesor untuk AL', 'icon' => 'bi-person-badge'],
            12 => ['date' => ($this->tanggal_pelaksanaan_al ?? $this->tanggal_al_selesai), 'label' => 'Pelaksanaan AL dan penyampaian berita acara AL', 'icon' => 'bi-building'],
            13 => ['date' => $this->tanggal_pelaporan_al, 'label' => 'Pelaporan AL', 'icon' => 'bi-clipboard-data'],
            14 => ['date' => $this->tanggal_hasil_akreditasi, 'label' => 'Penyampaian hasil akreditasi', 'icon' => 'bi-envelope-paper'],
            15 => ['date' => $this->tanggal_masa_sanggah_mulai, 'label' => 'Masa sanggah', 'icon' => 'bi-clock-history'],
            16 => ['date' => $this->tanggal_pelaksanaan_banding, 'label' => 'Pelaksanaan banding', 'icon' => 'bi-arrow-repeat'],
            17 => ['date' => $this->tanggal_pelaporan_banding, 'label' => 'Pelaporan banding', 'icon' => 'bi-file-earmark-ruled'],
            18 => ['date' => $this->tanggal_penetapan, 'label' => 'Penetapan hasil akreditasi', 'icon' => 'bi-award'],
            19 => ['date' => $this->tanggal_pelaporan_hasil, 'label' => 'Pelaporan hasil akreditasi', 'icon' => 'bi-megaphone'],
            20 => ['date' => $this->tanggal_penyimpanan, 'label' => 'Penyimpanan arsip pelaksanaan akreditasi', 'icon' => 'bi-archive'],
        ];

        $meta = $this->currentTimelineMeta();
        $currentStep = $meta['step'];
        $currentColor = $meta['color']; // warning|success

        // Special case: ditolak
        if ($this->status === self::STATUS_DITOLAK) {
            foreach ($items as $step => &$item) {
                $item['color'] = 'secondary';
                $item['state'] = 'future';
            }
            unset($item);

            // kalau mau: tampilkan badge khusus "Ditolak" di UI, atau set step terakhir jadi danger
            return $items;
        }

        // Jika status di step ini "success", artinya step tsb sudah tuntas,
        // maka "current" pindah ke step berikutnya (kecuali sudah step 20)
        if ($currentColor === 'success' && $currentStep < 20 && $this->status !== self::STATUS_SELESAI) {
            $currentStep++;
            $currentColor = 'warning'; // step berikutnya dianggap progress (kuning)
        }

        foreach ($items as $step => &$item) {
            if ($step < $currentStep) {
                $item['color'] = 'success';
                $item['state'] = 'done';
            } elseif ($step === $currentStep) {
                $item['color'] = $currentColor; // sesuai tabel kamu
                $item['state'] = 'current';
            } else {
                $item['color'] = 'secondary';
                $item['state'] = 'future';
            }
        }
        unset($item);

        return $items;
    }

    public static function statusTimelineRuleMap(): array
    {
        return [
            // step => [ 'warning' => [...], 'success' => [...] ]

            1 => [
                'warning' => [self::STATUS_DRAFT],
                'success' => [self::STATUS_PENGINGAT_DIKIRIM],
            ],

            2 => [
                'warning' => [self::STATUS_SURAT_PERMOHONAN_DIKIRIM],
                'success' => [self::STATUS_SURAT_PERMOHONAN_DITERIMA],
                'danger' => [self::STATUS_SURAT_PERMOHONAN_DITOLAK],
            ],

            3 => [
                'success' => [self::STATUS_TEMPLATE_LED_DIKIRIM],
            ],

            4 => [
                'warning' => [
                    self::STATUS_MENUNGGU_PEMBAYARAN,
                    self::STATUS_PEMBAYARAN_DITERIMA,
                    self::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
                ],
                'success' => [self::STATUS_PEMBAYARAN_DIVERIFIKASI],
            ],

            5 => [
                'warning' => [self::STATUS_DRAFT_BORANG_DIKIRIM],
                'success' => [self::STATUS_DRAFT_BORANG_DITERIMA, self::STATUS_BORANG_ONLINE_SELESAI],
            ],

            6 => [
                'warning' => [
                    self::STATUS_BORANG_VALIDATION_PENDING,
                    self::STATUS_BORANG_IN_VALIDATION,
                    self::STATUS_BORANG_REVISION_REQUIRED,
                ],
                'success' => [self::STATUS_BORANG_VALIDATED, self::STATUS_BORANG_FINAL_DITERIMA],
            ],

            7 => [
                'success' => [self::STATUS_VALIDASI_BORANG_DILAPORKAN, self::STATUS_PENGAJUAN_COMPLETED],
            ],

            8 => [
                'warning' => [self::STATUS_ASESOR_AK_ASSIGNED, self::STATUS_AK_IN_PROGRESS],
                'success' => [self::STATUS_AK_ON_VALIDATION],
            ],

            9 => [
                'warning' => [self::STATUS_AK_ON_VALIDATION],
                'success' => [self::STATUS_AK_SELESAI],
            ],

            10 => [
                'success' => [self::STATUS_AK_DILAPORKAN],
            ],

            11 => [
                'warning' => [self::STATUS_ASESOR_AL_ASSIGNED],
                'success' => [self::STATUS_AL_IN_PROGRESS],
            ],

            12 => [
                'warning' => [self::STATUS_AL_IN_PROGRESS],
                'success' => [self::STATUS_AL_SELESAI],
            ],

            13 => [
                'success' => [self::STATUS_AL_DILAPORKAN],
            ],

            14 => [
                'success' => [self::STATUS_HASIL_AKREDITASI_DIKIRIM],
            ],

            15 => [
                'success' => [self::STATUS_MASA_SANGGAH],
            ],

            16 => [
                'warning' => [self::STATUS_BANDING_DIAJUKAN],
                'success' => [self::STATUS_BANDING_DILAKSANAKAN],
            ],

            17 => [
                'success' => [self::STATUS_BANDING_DILAPORKAN],
            ],

            18 => [
                'warning' => [self::STATUS_HASIL_DITETAPKAN],
                'success' => [self::STATUS_HASIL_DIUMUMKAN],
            ],

            19 => [
                'success' => [self::STATUS_HASIL_DILAPORKAN],
            ],

            20 => [
                'success' => [self::STATUS_ARSIP_DISIMPAN, self::STATUS_SELESAI],
            ],
        ];
    }

    /**
     * Return: ['step' => int, 'color' => 'warning|success']
     * color = warna step saat ini sesuai status (bukan berdasarkan date)
     */
    public function currentTimelineMeta(): array
    {
        $status = $this->status;
        $rules = self::statusTimelineRuleMap();

        foreach ($rules as $step => $cfg) {
            foreach (['warning', 'success'] as $color) {
                $list = $cfg[$color] ?? [];
                if (in_array($status, $list, true)) {
                    return ['step' => $step, 'color' => $color];
                }
            }
        }

        // fallback aman: kalau status nggak terdaftar di rules
        // anggap masih di step 1 dan sedang progress
        return ['step' => 1, 'color' => 'warning'];
    }

    /**
     * ✅ Status transition rules (which status can go to which)
     */
    public static function allowedStatusTransitions(): array
    {
        return [
            // Steps 1-13 (existing)
            self::STATUS_DRAFT => [self::STATUS_PENGINGAT_DIKIRIM],
            self::STATUS_PENGINGAT_DIKIRIM => [self::STATUS_SURAT_PERMOHONAN_DIKIRIM],
            self::STATUS_SURAT_PERMOHONAN_DIKIRIM => [self::STATUS_SURAT_PERMOHONAN_DITERIMA, self::STATUS_SURAT_PERMOHONAN_DITOLAK],
            self::STATUS_SURAT_PERMOHONAN_DITERIMA => [self::STATUS_TEMPLATE_LED_DIKIRIM, self::STATUS_MENUNGGU_PEMBAYARAN],
            self::STATUS_TEMPLATE_LED_DIKIRIM => [self::STATUS_MENUNGGU_PEMBAYARAN],
            self::STATUS_MENUNGGU_PEMBAYARAN => [self::STATUS_PEMBAYARAN_DITERIMA, self::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN],
            self::STATUS_PEMBAYARAN_DITERIMA => [self::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN],
            self::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN => [self::STATUS_PEMBAYARAN_DIVERIFIKASI, self::STATUS_MENUNGGU_PEMBAYARAN],
            self::STATUS_PEMBAYARAN_DIVERIFIKASI => [self::STATUS_DRAFT_BORANG_DITERIMA],
            self::STATUS_DRAFT_BORANG_DITERIMA => [self::STATUS_BORANG_ONLINE_SELESAI],
            self::STATUS_BORANG_ONLINE_SELESAI => [self::STATUS_BORANG_VALIDATION_PENDING],
            self::STATUS_BORANG_VALIDATION_PENDING => [self::STATUS_BORANG_IN_VALIDATION],
            self::STATUS_BORANG_IN_VALIDATION => [self::STATUS_BORANG_REVISION_REQUIRED, self::STATUS_BORANG_VALIDATED],
            self::STATUS_BORANG_REVISION_REQUIRED => [self::STATUS_BORANG_ONLINE_SELESAI],
            self::STATUS_BORANG_VALIDATED => [self::STATUS_BORANG_FINAL_DITERIMA, self::STATUS_VALIDASI_BORANG_DILAPORKAN],
            self::STATUS_BORANG_FINAL_DITERIMA => [self::STATUS_VALIDASI_BORANG_DILAPORKAN],
            self::STATUS_VALIDASI_BORANG_DILAPORKAN => [self::STATUS_PENGAJUAN_COMPLETED],
            self::STATUS_PENGAJUAN_COMPLETED => [self::STATUS_ASESOR_AK_ASSIGNED],
            self::STATUS_ASESOR_AK_ASSIGNED => [self::STATUS_AK_IN_PROGRESS],
            self::STATUS_AK_IN_PROGRESS => [self::STATUS_AK_ON_VALIDATION],
            self::STATUS_AK_ON_VALIDATION => [self::STATUS_AK_SELESAI],
            self::STATUS_AK_SELESAI => [self::STATUS_AK_DILAPORKAN],
            self::STATUS_AK_DILAPORKAN => [self::STATUS_ASESOR_AL_ASSIGNED],
            self::STATUS_ASESOR_AL_ASSIGNED => [self::STATUS_AL_IN_PROGRESS],
            self::STATUS_AL_IN_PROGRESS => [self::STATUS_AL_SELESAI],
            self::STATUS_AL_SELESAI => [self::STATUS_AL_DILAPORKAN],

            // ✅ NEW: Steps 14-20 (SEQUENTIAL ENFORCEMENT)
            self::STATUS_AL_DILAPORKAN => [self::STATUS_HASIL_AKREDITASI_DIKIRIM], // 13 → 14 ONLY

            self::STATUS_HASIL_AKREDITASI_DIKIRIM => [self::STATUS_MASA_SANGGAH], // 14 → 15 ONLY

            self::STATUS_MASA_SANGGAH => [
                self::STATUS_BANDING_DIAJUKAN,  // 15 → 16 (if banding)
                self::STATUS_HASIL_DITETAPKAN    // 15 → 18 (skip banding)
            ],

            self::STATUS_BANDING_DIAJUKAN => [self::STATUS_BANDING_DILAKSANAKAN], // 16 → 17
            self::STATUS_BANDING_DILAKSANAKAN => [self::STATUS_BANDING_DILAPORKAN], // 17 → 18
            self::STATUS_BANDING_DILAPORKAN => [self::STATUS_HASIL_DITETAPKAN], // 18 → 19

            self::STATUS_HASIL_DITETAPKAN => [self::STATUS_HASIL_DIUMUMKAN], // 18 → 19
            self::STATUS_HASIL_DIUMUMKAN => [self::STATUS_HASIL_DILAPORKAN], // 19 → 20
            self::STATUS_HASIL_DILAPORKAN => [self::STATUS_ARSIP_DISIMPAN], // 20 → 21
            self::STATUS_ARSIP_DISIMPAN => [self::STATUS_SELESAI], // 21 → DONE

            self::STATUS_SELESAI => [], // Terminal state
            self::STATUS_DITOLAK => [], // Terminal state
        ];
    }

    /**
     * ✅ Get current step number (1–20) from statusTimelineRuleMap
     */
    public function getCurrentStepNumber(): int
    {
        $currentStatus = $this->status;

        foreach (self::statusTimelineRuleMap() as $step => $rules) {

            foreach ($rules as $statuses) {
                if (in_array($currentStatus, $statuses, true)) {
                    return $step;
                }
            }
        }

        return 0; // fallback jika status tidak ditemukan
    }

    /**
     * ✅ Check if transition is allowed
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = self::allowedStatusTransitions();

        // Current status not in rules (shouldn't happen)
        if (!isset($allowedTransitions[$this->status])) {
            return false;
        }

        return in_array($newStatus, $allowedTransitions[$this->status], true);
    }

    /**
     * ✅ Get next allowed statuses
     */
    public function getNextAllowedStatuses(): array
    {
        $allowedTransitions = self::allowedStatusTransitions();
        return $allowedTransitions[$this->status] ?? [];
    }

    /**
     * ✅ Safe status update with validation
     */
    public function updateStatusSafely(string $newStatus, ?string $note = null): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status} to {$newStatus}. " .
                    "Allowed: " . implode(', ', $this->getNextAllowedStatuses())
            );
        }

        DB::beginTransaction();
        try {
            $oldStatus = $this->status;

            $this->update(['status' => $newStatus]);

            // Log transition
            $this->statusLog()->create([
                'status_from' => $oldStatus,
                'status_to' => $newStatus,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $note,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ✅ Check if step 14 can be started
     */
    public function canStartHasilAkreditasi(): bool
    {
        return $this->status === self::STATUS_AL_DILAPORKAN
            && $this->tanggal_pelaporan_al !== null;
    }

    /**
     * ✅ Check if step 15 (masa sanggah) can be started
     */
    public function canStartMasaSanggah(): bool
    {
        return $this->status === self::STATUS_HASIL_AKREDITASI_DIKIRIM
            && $this->tanggal_hasil_akreditasi !== null;
    }

    /**
     * ✅ Check if banding is submitted during masa sanggah
     */
    public function hasBanding(): bool
    {
        return $this->tanggal_banding !== null;
    }

    public function getCustomLastStatus($attribute)
    {
        $statuses = [];
        if ($attribute == 'surat_permohonan_ps')
            $statuses = [self::STATUS_PENGINGAT_DIKIRIM, self::STATUS_SURAT_PERMOHONAN_DIKIRIM, self::STATUS_SURAT_PERMOHONAN_DITERIMA, self::STATUS_SURAT_PERMOHONAN_DITOLAK];
        if ($attribute == 'borang_template')
            $statuses = [self::STATUS_TEMPLATE_LED_DIKIRIM, self::STATUS_SURAT_PERMOHONAN_DITERIMA];
        if ($attribute == 'draft_borang')
            $statuses = [self::STATUS_DRAFT_BORANG_DIKIRIM, self::STATUS_DRAFT_BORANG_DITERIMA, self::STATUS_BORANG_ONLINE_SELESAI, self::STATUS_BORANG_VALIDATION_PENDING, self::STATUS_BORANG_IN_VALIDATION, self::STATUS_BORANG_REVISION_REQUIRED, self::STATUS_BORANG_VALIDATED, self::STATUS_BORANG_FINAL_DITERIMA];
        if ($attribute == 'borang_final')
            $statuses = [self::STATUS_DRAFT_BORANG_DIKIRIM, self::STATUS_DRAFT_BORANG_DITERIMA, self::STATUS_BORANG_ONLINE_SELESAI, self::STATUS_BORANG_VALIDATION_PENDING, self::STATUS_BORANG_IN_VALIDATION, self::STATUS_BORANG_REVISION_REQUIRED, self::STATUS_BORANG_VALIDATED, self::STATUS_BORANG_FINAL_DITERIMA];

        // status fase terakhir (berdasarkan log)
        $lastStatus = optional($this->statusLog()->whereIn('status_to', $statuses)->latest()->first())->status_to;
        // fallback kalau belum ada log (harusnya jarang) -> pakai current status
        $lastStatus = $lastStatus ?? $this->status;
        return $lastStatus;
    }

    public function getCustomBadgeLastStatus(string $attribute): string
    {
        return match ($attribute) {
            'surat_permohonan_ps' => match ($this->getCustomLastStatus($attribute)) {
                self::STATUS_PENGINGAT_DIKIRIM => '<span class="badge bg-warning">Menunggu Surat</span>',
                self::STATUS_SURAT_PERMOHONAN_DIKIRIM => '<span class="badge bg-info">Surat Dikirim dari PS</span>',
                self::STATUS_SURAT_PERMOHONAN_DITERIMA => '<span class="badge bg-success">Surat Permohonan Akreditasi Diterima</span>',
                self::STATUS_SURAT_PERMOHONAN_DITOLAK => '<span class="badge bg-danger">Surat Permohonan Akreditasi Ditolak</span>',
                default => '<span class="badge bg-secondary">-</span>',
            },
            'borang_template' => match ($this->getCustomLastStatus($attribute)) {
                self::STATUS_SURAT_PERMOHONAN_DITERIMA => '<span class="badge bg-warning">Belum Dikirim DE</span>',
                self::STATUS_TEMPLATE_LED_DIKIRIM => '<span class="badge bg-success">Sudah Dikirim, Sudah Diterima PS</span>',
                default => '<span class="badge bg-secondary">-</span>',
            },
            'borang_final' => match ($this->getCustomLastStatus($attribute)) {
                self::STATUS_DRAFT_BORANG_DIKIRIM => '<span class="badge bg-warning">Draft Dokumen Dikirim</span>',
                self::STATUS_DRAFT_BORANG_DITERIMA => '<span class="badge bg-info">Draft Dokumen Diterima</span>',
                self::STATUS_BORANG_ONLINE_SELESAI => '<span class="badge bg-primary">Pengisian Dokumen Selesai</span>',
                self::STATUS_BORANG_VALIDATION_PENDING => '<span class="badge bg-warning">Menunggu Validasi Dokumen</span>',
                self::STATUS_BORANG_IN_VALIDATION => '<span class="badge bg-info">Dalam Proses Validasi</span>',
                self::STATUS_BORANG_REVISION_REQUIRED => '<span class="badge bg-danger">Perlu Revisi Dokumen</span>',
                self::STATUS_BORANG_VALIDATED => '<span class="badge bg-success">Dokumen Tervalidasi</span>',
                self::STATUS_BORANG_FINAL_DITERIMA => '<span class="badge bg-success">Dokumen Final Diterima</span>',
                default => '<span class="badge bg-secondary">-</span>',
            },
            default => '<span class="badge bg-secondary">-</span>',
        };
    }
}
