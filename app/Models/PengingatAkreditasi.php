<?php
// app/Models/PengingatAkreditasi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengingatAkreditasi extends Model
{
    protected $table = 'pengingat_akreditasi';

    protected $fillable = [
        'id_program_studi',
        'id_de_pengirim',
        'tahun_akreditasi',
        'pesan_pengingat',
        'tanggal_dikirim',
        'status',
        'tanggal_direspon',
        'id_pengajuan',
        'email_terkirim_ke',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_dikirim' => 'datetime',
        'tanggal_direspon' => 'datetime',
    ];

    // Status Constants
    public const STATUS_BELUM_DIRESPON = 'belum_direspon';
    public const STATUS_DIRESPON = 'direspon';
    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BELUM_DIRESPON => 'Belum Direspon',
            self::STATUS_DIRESPON => 'Sudah Direspon',
            self::STATUS_KEDALUWARSA => 'Kedaluwarsa',
        ];
    }

    // Relations
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'id_program_studi');
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_de_pengirim');
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    // Scopes
    public function scopeBelumDirespon($query)
    {
        return $query->where('status', self::STATUS_BELUM_DIRESPON);
    }

    public function scopeDirespon($query)
    {
        return $query->where('status', self::STATUS_DIRESPON);
    }

    public function scopeKedaluwarsa($query)
    {
        return $query->where('status', self::STATUS_KEDALUWARSA);
    }

    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('id_program_studi', $prodiId);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun_akreditasi', $tahun);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_BELUM_DIRESPON => 'bg-warning',
            self::STATUS_DIRESPON => 'bg-success',
            self::STATUS_KEDALUWARSA => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getDurasiResponAttribute(): ?int
    {
        if (!$this->tanggal_direspon) {
            return null;
        }

        return $this->tanggal_dikirim->diffInDays($this->tanggal_direspon);
    }

    // Methods
    public function markAsResponded(PengajuanAkreditasi $pengajuan): void
    {
        $this->update([
            'status' => self::STATUS_DIRESPON,
            'tanggal_direspon' => now(),
            'id_pengajuan' => $pengajuan->id,
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_KEDALUWARSA,
        ]);
    }
}
