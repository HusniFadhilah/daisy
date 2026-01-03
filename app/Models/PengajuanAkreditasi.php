<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanAkreditasi extends Model
{
    protected $table = 'pengajuan_akreditasi';

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
        'tanggal_pengingat',
        'tanggal_surat_permohonan',
        'tanggal_borang_dikirim',
        'tanggal_draft_borang',
        'tanggal_review_kesiapan',
        'tanggal_pembayaran',
        'tanggal_borang_final',
        'tanggal_lanjut_ak',
    ];

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


    public static function statusMap(): array
    {
        return [
            'draft' => [
                'label' => 'Draft',
                'bg' => 'bg-secondary',
                'icon' => 'bi-pencil',
            ],

            'pengingat_dikirim' => [
                'label' => 'Pengingat Dikirim',
                'bg' => 'bg-secondary',
                'icon' => 'bi-bell',
            ],
            'surat_permohonan_diterima' => [
                'label' => 'Surat Permohonan Diterima',
                'bg' => 'bg-info',
                'icon' => 'bi-envelope-check',
            ],

            'borang_dikirim' => [
                'label' => 'Borang Dikirim',
                'bg' => 'bg-primary',
                'icon' => 'bi-send',
            ],
            'draft_borang_diterima' => [
                'label' => 'Draft Borang Diterima',
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
                'label' => 'Borang Final Diterima',
                'bg' => 'bg-primary',
                'icon' => 'bi-file-earmark-check',
            ],
            'borang_online_selesai' => [
                'label' => 'Borang Online Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-ui-checks',
            ],

            'pengajuan_completed' => [
                'label' => 'Pengajuan Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-check2-all',
            ],

            'ak_in_progress' => [
                'label' => 'Asesmen Kecukupan (AK) Berlangsung',
                'bg' => 'bg-primary',
                'icon' => 'bi-clipboard-data',
            ],
            'ak_completed' => [
                'label' => 'Asesmen Kecukupan (AK) Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-check',
            ],

            'al_in_progress' => [
                'label' => 'Asesmen Lapangan (AL) Berlangsung',
                'bg' => 'bg-primary',
                'icon' => 'bi-building',
            ],
            'al_completed' => [
                'label' => 'Asesmen Lapangan (AL) Selesai',
                'bg' => 'bg-success',
                'icon' => 'bi-building-check',
            ],

            'selesai' => [
                'label' => 'Selesai',
                'bg' => 'bg-dark',
                'icon' => 'bi-flag-fill',
            ],
            'ditolak' => [
                'label' => 'Ditolak',
                'bg' => 'bg-danger',
                'icon' => 'bi-x-octagon',
            ],
        ];
    }
}
