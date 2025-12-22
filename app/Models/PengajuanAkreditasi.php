<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanAkreditasi extends Model
{
    use SoftDeletes;

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

    // Scopes
    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('id_program_studi', $prodiId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
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
        $labels = [
            'pengingat_dikirim' => 'Pengingat Dikirim',
            'surat_permohonan_diterima' => 'Surat Permohonan Diterima',
            'borang_dikirim' => 'Borang Dikirim',
            'draft_borang_diterima' => 'Draft Borang Diterima',
            'review_kesiapan_siap' => 'Review: Siap Lanjut',
            'review_kesiapan_belum_siap' => 'Review: Belum Siap',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'pembayaran_diterima' => 'Pembayaran Diterima',
            'borang_final_diterima' => 'Borang Final Diterima',
            'lanjut_ke_ak' => 'Lanjut ke AK',
            'ditolak' => 'Ditolak',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pengingat_dikirim' => 'bg-secondary',
            'surat_permohonan_diterima' => 'bg-info',
            'borang_dikirim' => 'bg-primary',
            'draft_borang_diterima' => 'bg-warning',
            'review_kesiapan_siap' => 'bg-success',
            'review_kesiapan_belum_siap' => 'bg-danger',
            'menunggu_pembayaran' => 'bg-warning',
            'pembayaran_diterima' => 'bg-info',
            'borang_final_diterima' => 'bg-primary',
            'lanjut_ke_ak' => 'bg-success',
            'ditolak' => 'bg-danger',
        ];

        return $classes[$this->status] ?? 'bg-secondary';
    }
}
