<?php
// app/Services/HasilAkreditasiSyncService.php

namespace App\Services;

use App\Models\HasilAkreditasi;
use App\Models\JenjangPenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HasilAkreditasiSyncService
{
    /**
     * ✅ Update skor_kategori di semua HasilAkreditasi
     */
    public function syncAllSkorKategori(): array
    {
        $updated = 0;
        $failed = 0;
        $errors = [];

        $hasilList = HasilAkreditasi::whereNotNull('detail_skor_ak')
            ->orWhereNotNull('detail_skor_al')
            ->get();

        foreach ($hasilList as $hasil) {
            try {
                $this->syncSkorKategoriForHasil($hasil);
                $updated++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'id' => $hasil->id,
                    'error' => $e->getMessage()
                ];
                Log::error('Sync skor kategori failed', [
                    'hasil_id' => $hasil->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'total' => $hasilList->count(),
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * ✅ Update skor_kategori untuk 1 HasilAkreditasi
     */
    public function syncSkorKategoriForHasil(HasilAkreditasi $hasil): bool
    {
        DB::beginTransaction();
        try {
            $updated = false;

            // Update detail_skor_ak
            if ($hasil->detail_skor_ak) {
                $detailAk = $hasil->detail_skor_ak;

                if (isset($detailAk['elemen'])) {
                    foreach ($detailAk['elemen'] as &$elemen) {
                        if (isset($elemen['skor'])) {
                            $skor = round($elemen['skor']);
                            $elemen['skor_kategori'] = JenjangPenilaian::getSkorInfo($skor);
                        }
                    }
                }

                $hasil->detail_skor_ak = $detailAk;
                $updated = true;
            }

            // Update detail_skor_al
            if ($hasil->detail_skor_al) {
                $detailAl = $hasil->detail_skor_al;

                if (isset($detailAl['elemen'])) {
                    foreach ($detailAl['elemen'] as &$elemen) {
                        if (isset($elemen['skor'])) {
                            $skor = round($elemen['skor']);
                            $elemen['skor_kategori'] = JenjangPenilaian::getSkorInfo($skor);
                        }
                    }
                }

                $hasil->detail_skor_al = $detailAl;
                $updated = true;
            }

            if ($updated) {
                $hasil->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ✅ Sync specific hasil by ID
     */
    public function syncByHasilId(int $hasilId): bool
    {
        $hasil = HasilAkreditasi::findOrFail($hasilId);
        return $this->syncSkorKategoriForHasil($hasil);
    }

    /**
     * ✅ Sync by pengajuan ID
     */
    public function syncByPengajuanId(int $pengajuanId): bool
    {
        $hasil = HasilAkreditasi::where('id_pengajuan', $pengajuanId)->first();

        if (!$hasil) {
            throw new \Exception('Hasil akreditasi tidak ditemukan untuk pengajuan ini.');
        }

        return $this->syncSkorKategoriForHasil($hasil);
    }

    /**
     * ✅ Sync by asesmen ID
     */
    public function syncByAsesmenId(int $asesmenId): bool
    {
        $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->first();

        if (!$hasil) {
            throw new \Exception('Hasil akreditasi tidak ditemukan untuk asesmen ini.');
        }

        return $this->syncSkorKategoriForHasil($hasil);
    }

    /**
     * ✅ Get preview of changes (dry run)
     */
    public function previewChanges(HasilAkreditasi $hasil): array
    {
        $changes = [
            'ak' => [],
            'al' => []
        ];

        // Preview AK changes
        if ($hasil->detail_skor_ak && isset($hasil->detail_skor_ak['elemen'])) {
            foreach ($hasil->detail_skor_ak['elemen'] as $elemen) {
                if (isset($elemen['skor'])) {
                    $skor = round($elemen['skor']);
                    $old = $elemen['skor_kategori'];
                    $new = JenjangPenilaian::getSkorInfo($skor);

                    if ($old != $new) {
                        $changes['ak'][] = [
                            'kode_elemen' => $elemen['kode_elemen'],
                            'nama_elemen' => $elemen['nama_elemen'],
                            'skor' => $skor,
                            'old' => $old,
                            'new' => $new
                        ];
                    }
                }
            }
        }

        // Preview AL changes
        if ($hasil->detail_skor_al && isset($hasil->detail_skor_al['elemen'])) {
            foreach ($hasil->detail_skor_al['elemen'] as $elemen) {
                if (isset($elemen['skor'])) {
                    $skor = round($elemen['skor']);
                    $old = $elemen['skor_kategori'];
                    $new = JenjangPenilaian::getSkorInfo($skor);

                    if ($old != $new) {
                        $changes['al'][] = [
                            'kode_elemen' => $elemen['kode_elemen'],
                            'nama_elemen' => $elemen['nama_elemen'],
                            'skor' => $skor,
                            'old' => $old,
                            'new' => $new
                        ];
                    }
                }
            }
        }

        return $changes;
    }
}
