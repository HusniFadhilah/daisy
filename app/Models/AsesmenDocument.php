<?php
// app/Models/AsesmenDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenDocument extends Model
{
    protected $fillable = [
        'id_asesmen',
        'type',
        'title',
        'sort_order',
        'path',
        'original_name',
        'size',
        'mime',
        'is_active',
        'version',
        'uploaded_by',
        'uploaded_at',
        'keterangan',
        'status_persetujuan_prodi',
        'approved_by_prodi',
        'approved_at_prodi',
        'catatan_prodi',
        'status_persetujuan_de',
        'approved_by_de',
        'approved_at_de',
        'catatan_de',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'approved_at_prodi' => 'datetime',
        'approved_at_de' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ============================================
    // TYPE CONSTANTS
    // ============================================
    public const TYPE_SURAT_TUGAS = 'surat_tugas';
    public const TYPE_LAPORAN_AK = 'laporan_ak';
    public const TYPE_LAPORAN_AL = 'laporan_al';
    public const TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL = 'berita_acara_penyampaian_hasil';
    public const TYPE_BERITA_ACARA_PENETAPAN_HASIL = 'berita_acara_penetapan_hasil'; // ✅ ADD
    public const TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP = 'berita_acara_penyimpanan_arsip'; // ✅ NEW
    public const TYPE_BERITA_ACARA_AL = 'berita_acara_al';
    public const TYPE_BERITA_ACARA_AK = 'berita_acara_ak';

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the asesmen
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    /**
     * Get the uploader
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Alias for uploader
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the prodi approver
     */
    public function prodiApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_prodi');
    }

    /**
     * Get the DE approver
     */
    public function deApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_de');
    }

    // ============================================
    // ATTRIBUTES & HELPERS
    // ============================================

    /**
     * Get status badge class for prodi approval
     */
    public function getStatusProdiBadgeClassAttribute(): string
    {
        return match ($this->status_persetujuan_prodi) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'revision_required' => 'bg-info',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label for prodi approval
     */
    public function getStatusProdiLabelAttribute(): string
    {
        return match ($this->status_persetujuan_prodi) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'revision_required' => 'Perlu Revisi',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }

    /**
     * Get status badge class for DE approval
     */
    public function getStatusDeBadgeClassAttribute(): string
    {
        return match ($this->status_persetujuan_de) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'revision_required' => 'bg-info',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label for DE approval
     */
    public function getStatusDeLabelAttribute(): string
    {
        return match ($this->status_persetujuan_de) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'revision_required' => 'Perlu Revisi',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }

    /**
     * Check if can be revised (pending or revision_required)
     */
    public function canBeRevised(): bool
    {
        return in_array($this->status_persetujuan_prodi, ['pending', 'revision_required']);
    }

    /**
     * Check if is final status (approved or rejected)
     */
    public function isFinalStatus(): bool
    {
        return in_array($this->status_persetujuan_prodi, ['approved', 'rejected']);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SURAT_TUGAS => 'Surat Tugas',
            self::TYPE_LAPORAN_AK => 'Laporan AK',
            self::TYPE_LAPORAN_AL => 'Laporan AL',
            self::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL => 'Berita Acara Penyampaian Hasil',
            self::TYPE_BERITA_ACARA_PENETAPAN_HASIL => 'Berita Acara Penetapan Hasil',
            self::TYPE_BERITA_ACARA_AL => 'Berita Acara AL',
            self::TYPE_BERITA_ACARA_AK => 'Berita Acara AK',
            default => ucwords(str_replace('_', ' ', $this->type)),
        };
    }

    // ============================================
    // STATIC HELPERS
    // ============================================

    /**
     * ✅ Allowed document types
     */
    public static function allowedTypes(): array
    {
        return [
            self::TYPE_SURAT_TUGAS,
            self::TYPE_LAPORAN_AK,
            self::TYPE_LAPORAN_AL,
            self::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL,
            self::TYPE_BERITA_ACARA_PENETAPAN_HASIL,
            self::TYPE_BERITA_ACARA_AL,
            self::TYPE_BERITA_ACARA_AK,
        ];
    }

    /**
     * ✅ Check if berita acara penyampaian hasil exists
     */
    public static function hasBeritaAcaraPenyampaianHasil(int $asesmenId): bool
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * ✅ Check if berita acara penetapan hasil exists
     */
    public static function hasBeritaAcaraPenetapanHasil(int $asesmenId): bool
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * ✅ Get active berita acara penyampaian hasil
     */
    public static function getBeritaAcaraPenyampaianHasil(int $asesmenId): ?self
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * ✅ Get active berita acara penetapan hasil
     */
    public static function getBeritaAcaraPenetapanHasil(int $asesmenId): ?self
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * ✅ Get active berita acara AL
     */
    public static function getBeritaAcaraAL(int $asesmenId): ?self
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_AL)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * ✅ Get active berita acara AK
     */
    public static function getBeritaAcaraAK(int $asesmenId): ?self
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_AK)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * ✅ Check if document type exists for asesmen
     */
    public static function hasDocumentType(int $asesmenId, string $type): bool
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', $type)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * ✅ Get latest document by type
     */
    public static function getLatestByType(int $asesmenId, string $type): ?self
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', $type)
            ->where('is_active', true)
            ->latest('uploaded_at')
            ->first();
    }

    /**
     * ✅ Get all active documents by type
     */
    public static function getAllByType(int $asesmenId, string $type)
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    /**
     * ✅ Check if berita acara penyimpanan arsip exists
     */
    public static function hasBeritaAcaraPenyimpananArsip(int $asesmenId): bool
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * ✅ Get latest berita acara penyimpanan arsip
     */
    public static function getLatestBeritaAcaraPenyimpananArsip(int $asesmenId): ?self
    {
        return self::where('id_asesmen', $asesmenId)
            ->where('type', self::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope for active documents
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific asesmen
     */
    public function scopeForAsesmen($query, int $asesmenId)
    {
        return $query->where('id_asesmen', $asesmenId);
    }

    /**
     * Scope for pending approval
     */
    public function scopePendingApproval($query, string $approver = 'prodi')
    {
        $column = $approver === 'prodi'
            ? 'status_persetujuan_prodi'
            : 'status_persetujuan_de';

        return $query->where($column, 'pending');
    }

    /**
     * Scope for approved documents
     */
    public function scopeApproved($query, string $approver = 'prodi')
    {
        $column = $approver === 'prodi'
            ? 'status_persetujuan_prodi'
            : 'status_persetujuan_de';

        return $query->where($column, 'approved');
    }
}
