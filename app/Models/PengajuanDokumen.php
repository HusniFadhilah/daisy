<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengajuanDokumen extends Model
{
    protected $table = 'pengajuan_dokumen';

    protected $fillable = [
        'id_pengajuan',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'original_filename',
        'file_size',
        'mime_type',
        'uploaded_by',
        'keterangan',
        'template_link',
        'versi',
        'is_latest',
    ];

    protected $appends = ['jenis_dokumen_alias'];

    protected $casts = [
        'is_latest' => 'boolean',
    ];

    public const JENIS_DOKUMEN_ALIAS = [
        'surat_permohonan'             => 'File Permohonan Akreditasi',
        'surat_permohonan_banding'     => 'File Permohonan Banding',
        'surat_penerimaan_de'          => 'File Penerimaan Permohonan Akreditasi',
        'surat_tugas'                  => 'Surat Tugas',
        'surat_tugas_validator'        => 'Surat Tugas Validator',
        'surat_tugas_validator_dokumen' => 'Surat Tugas Validator Dokumen',
        'surat_tugas_asesor_ak'        => 'Surat Tugas Asesor AK',
        'surat_tugas_validator_ak'     => 'Surat Tugas Validator AK',
        'surat_tugas_asesor_al'        => 'Surat Tugas Asesor AL',
        'surat_tugas_validator_al'     => 'Surat Tugas Validator AL',
        'surat_tugas_validator_rekap'  => 'Surat Tugas Validator Rekap',
        'surat_tugas_asesor_ak_banding'     => 'Surat Tugas Asesor Banding',
        'surat_tugas_validator_ak_banding'     => 'Surat Tugas Validator AK Banding',
        'surat_tugas_asesor_al_banding'     => 'Surat Tugas Asesor Banding',
        'surat_tugas_validator_al_banding'     => 'Surat Tugas Validator AL Banding',
        'surat_tugas_validator_rekap_banding'  => 'Surat Tugas Validator Rekap Banding',
        'surat_tugas_asesor_banding'     => 'Surat Tugas Asesor Banding',
        'borang_template'              => 'Templat Dokumen',
        'template_formulir_pembayaran' => 'Templat Formulir Pembayaran',
        'formulir_pembayaran'          => 'Formulir Pembayaran Terisi',
        'formulir_pembayaran_banding'  => 'Formulir Pembayaran Banding Terisi',
        'draft_borang'                 => 'Draft Dokumen',
        'borang_final'                 => 'Dokumen Final',
        'bukti_pembayaran'             => 'Bukti Pembayaran',
        'lembar_pengesahan'            => 'Lembar Pengesahan Dokumen',
        'dokumen_pendukung'            => 'Dokumen Pendukung',
        'laporan_ak'                   => 'Laporan Penilaian Kecukupan LED Program Studi (LHK)',
        'laporan_al'                   => 'Laporan AL',
        'laporan_hasil'                => 'Laporan Hasil Akreditasi',
        'sertifikat'                   => 'Sertifikat Akreditasi',
        'sertifikat_banding'           => 'Sertifikat Akreditasi (Terbaru)',
        'data_kualitatif'              => 'Laporan Evaluasi Diri (LED)',
        'data_kuantitatif'             => 'Laporan Kinerja Program Studi (LKPS)',
        'data_suplemen'                => 'Suplemen Laporan Evaluasi Diri (LED)',
        'lainnya'                      => 'Dokumen Lainnya',
    ];

    public function getJenisDokumenAliasAttribute(): string
    {
        return self::JENIS_DOKUMEN_ALIAS[$this->jenis_dokumen]
            ?? ucwords(str_replace('_', ' ', $this->jenis_dokumen));
    }

    // Relations
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors
    public function getDownloadUrlAttribute()
    {
        // ✅ Handle both file and link
        if ($this->template_link) {
            return $this->template_link;
        }
        if ($this->jenis_dokumen == 'draft_borang') {
            $uploadKualitatif = PengajuanDokumen::where('id_pengajuan', $this->id_pengajuan)->where('jenis_dokumen', 'data_kualitatif')->first();
            if ($uploadKualitatif)
                return route('pengajuan.dokumen.download', $uploadKualitatif->id);
            return route('pengajuan.dokumen.download', $this->id);
        }

        if ($this->path_file) {
            return route('pengajuan.dokumen.download', $this->id);
        }

        return null;
    }

    public function getIsLinkBasedAttribute()
    {
        return !empty($this->template_link);
    }

    public function getIsFileBasedAttribute()
    {
        return !empty($this->path_file);
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes === 0 || !isset($bytes)) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function getFileExtensionAttribute()
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    public function getFileIconClassAttribute()
    {
        $ext = $this->file_extension;

        $icons = [
            'pdf' => 'bi-file-pdf text-danger',
            'docx' => 'bi-file-word text-primary',
            'doc' => 'bi-file-word text-primary',
            'xlsx' => 'bi-file-excel text-success',
            'xls' => 'bi-file-excel text-success',
            'jpg' => 'bi-file-image text-info',
            'jpeg' => 'bi-file-image text-info',
            'png' => 'bi-file-image text-info',
        ];

        return $icons[$ext] ?? 'bi-file-earmark text-secondary';
    }

    // Scopes
    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('jenis_dokumen', $type);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('jenis_dokumen', $type);
    }

    public function scopeByPengajuan($query, $pengajuanId)
    {
        return $query->where('id_pengajuan', $pengajuanId);
    }

    public function scopeJenisSuratPenerimaan($query)
    {
        return $query->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true);
    }

    /**
     * Role yang boleh melihat seluruh dokumen lintas program studi.
     */
    public const STAFF_ROLES_FULL_ACCESS = ['super_admin', 'sekretariat', 'keuangan_lamdepilar'];

    public function downloadDokumen($additionalFunction = null)
    {
        $dokumen = $this;
        $authUser = Auth::user();
        $pengajuan = $dokumen->pengajuan;

        abort_if(!$authUser || !$pengajuan, 403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        abort_unless($this->userCanAccess($authUser, $pengajuan), 403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            // Jalankan additionalFunction jika ada
            if ($additionalFunction && is_callable($additionalFunction)) {
                return $additionalFunction($dokumen, $pengajuan);
            }
            abort(404, 'File tidak ditemukan.');
        }

        $absolutePath = storage_path('app/public/' . $dokumen->path_file);
        $filename = $dokumen->original_filename ?: basename($absolutePath);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mime = match ($ext) {
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            default => mime_content_type($absolutePath) ?: 'application/octet-stream',
        };

        $disposition = ($ext === 'pdf') ? 'inline' : 'attachment';

        return response()->stream(function () use ($absolutePath) {
            $stream = fopen($absolutePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => $disposition . '; filename="' . addslashes($filename) . '"',
            'Content-Length'      => filesize($absolutePath),
            'Accept-Ranges'       => 'bytes',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
            'Pragma'              => 'public',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Cek apakah user boleh akses dokumen milik $pengajuan.
     *
     * - super_admin / sekretariat / keuangan_lamdepilar: semua dokumen.
     * - admin_univ / admin_prodi (PT, UPPS, PS): hanya dokumen prodi miliknya.
     * - DE / validator: dokumen pengajuan yang ditugaskan langsung ke dia.
     * - asesor / validator asesmen: dokumen pengajuan yang asesmen-nya dia tangani.
     */
    private function userCanAccess($authUser, $pengajuan): bool
    {
        if (in_array($authUser->role_selected, self::STAFF_ROLES_FULL_ACCESS, true)) {
            return true;
        }

        $userStudyProgramIds = $authUser->studyPrograms()->pluck('study_programs.id')->toArray();
        if (in_array($pengajuan->id_program_studi, $userStudyProgramIds, true)) {
            return true;
        }

        if ($pengajuan->id_de_assigned === $authUser->id || $pengajuan->id_validator_assigned === $authUser->id) {
            return true;
        }

        return AsesmenUserRole::where('id_user', $authUser->id)
            ->whereHas('asesmen', fn($q) => $q->where('id_pengajuan', $pengajuan->id))
            ->exists();
    }
}
