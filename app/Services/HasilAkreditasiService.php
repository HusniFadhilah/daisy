<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\BobotPenilaian;
use App\Models\HasilAkreditasi;
use App\Models\JenjangPenilaian;
use App\Models\PenilaianElemenAk;
use App\Models\PenilaianElemenAl;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;

class HasilAkreditasiService
{
    /**
     * ✅ Calculate AK score
     */
    public function calculateAK(Asesmen $asesmen): array
    {
        $studyProgram = $asesmen->studyProgram;
        $categoryId = $studyProgram->id_category;
        $degreeLevelId = $studyProgram->id_degree_level;

        if (!$categoryId) {
            throw new \Exception('Program studi belum memiliki kategori.');
        }

        // Get all AK penilaian (approved only)
        $penilaians = PenilaianElemenAk::where('id_asesmen', $asesmen->id)
            ->whereIn('status', ['approved', 'submitted'])
            ->with(['elemenStandar.kriteria'])
            ->get();

        if ($penilaians->isEmpty()) {
            throw new \Exception('Belum ada penilaian AK yang di-approve.');
        }

        // ✅ Group by elemen and calculate AVERAGE
        $elemenScores = $penilaians->groupBy('id_elemen')->map(function ($group) {
            $scores = $group->pluck('skor_final')->filter()->values();

            // If no skor_final, use skor
            if ($scores->isEmpty()) {
                $scores = $group->pluck('skor')->filter()->values();
            }

            return [
                'elemen' => $group->first()->elemenStandar,
                'avg_skor' => $scores->isNotEmpty() ? round($scores->avg(), 2) : 0,
                'count_asesor' => $scores->count(),
                'scores_detail' => $scores->toArray(),
            ];
        });

        $totalSkor = 0;
        $totalBobot = 0;
        $detailPerKriteria = [];
        $detailPerElemen = [];
        $pelampauanStandar = [];

        foreach ($elemenScores as $idElemen => $data) {
            $elemen = $data['elemen'];
            $kriteria = $elemen->kriteria;
            $avgSkor = $data['avg_skor'];

            // Get bobot
            $bobot = BobotPenilaian::where('id_elemen', $elemen->id)
                ->where('id_degree_level', $degreeLevelId)
                ->where('is_active', true)
                ->first();

            if (!$bobot) {
                Log::warning("Bobot tidak ditemukan untuk elemen {$elemen->kode_elemen}");
                continue;
            }

            // ✅ Track pelampauan standar (rata-rata skor >= 4)
            if ($avgSkor >= 4) {
                $kriteriaCode = $kriteria->kode_kriteria;

                if (!isset($pelampauanStandar[$kriteriaCode])) {
                    $pelampauanStandar[$kriteriaCode] = [];
                }

                $pelampauanStandar[$kriteriaCode][] = [
                    'kode_elemen' => $elemen->kode_elemen,
                    'nama_elemen' => $elemen->pernyataan_elemen,
                    'skor_rata' => $avgSkor,
                    'bobot' => $bobot->bobot,
                    'jumlah_asesor' => $data['count_asesor'],
                ];
            }

            // Calculate weighted score
            $skorTertimbang = $avgSkor * $bobot->bobot;

            $totalSkor += $skorTertimbang;
            $totalBobot += $bobot->bobot;

            // Group by kriteria
            $kriteriaCode = $kriteria->kode_kriteria;
            if (!isset($detailPerKriteria[$kriteriaCode])) {
                $detailPerKriteria[$kriteriaCode] = [
                    'nama' => $kriteria->nama_kriteria,
                    'total_skor' => 0,
                    'total_bobot' => 0,
                    'elemen_count' => 0,
                    'has_pelampauan' => false,
                ];
            }

            $detailPerKriteria[$kriteriaCode]['total_skor'] += $skorTertimbang;
            $detailPerKriteria[$kriteriaCode]['total_bobot'] += $bobot->bobot;
            $detailPerKriteria[$kriteriaCode]['elemen_count']++;

            if ($avgSkor >= 4) {
                $detailPerKriteria[$kriteriaCode]['has_pelampauan'] = true;
            }

            // Detail per elemen
            $detailPerElemen[] = [
                'kode_elemen' => $elemen->kode_elemen,
                'nama_elemen' => $elemen->pernyataan_elemen,
                'kode_kriteria' => $kriteriaCode,
                'nama_kriteria' => $kriteria->nama_kriteria,
                'skor' => $avgSkor,
                'skor_kategori' => JenjangPenilaian::getSkorInfo(round($avgSkor)),
                'bobot' => $bobot->bobot,
                'skor_tertimbang' => $skorTertimbang,
                'jumlah_asesor' => $data['count_asesor'],
            ];
        }

        return [
            'skor_total' => round($totalSkor, 2),
            'skor_tertimbang' => round($totalSkor, 2),
            'total_bobot' => $totalBobot,
            'detail_kriteria' => $detailPerKriteria,
            'detail_elemen' => $detailPerElemen,
            'pelampauan_standar' => $pelampauanStandar,
            'jumlah_elemen' => count($detailPerElemen),
        ];
    }

    /**
     * ✅ Calculate AL score with pelampauan tracking
     */
    public function calculateAL(Asesmen $asesmen): array
    {
        $studyProgram = $asesmen->studyProgram;
        $categoryId = $studyProgram->id_category;
        $degreeLevelId = $studyProgram->id_degree_level;

        if (!$categoryId) {
            throw new \Exception('Program studi belum memiliki kategori.');
        }

        // Get all AL penilaian (approved only)
        $penilaians = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
            ->whereIn('status', ['approved', 'submitted'])
            ->with(['elemenStandar.kriteria'])
            ->get();

        if ($penilaians->isEmpty()) {
            throw new \Exception('Belum ada penilaian AL yang di-approve.');
        }

        // ✅ Group by elemen and calculate AVERAGE
        $elemenScores = $penilaians->groupBy('id_elemen')->map(function ($group) {
            $scores = $group->pluck('skor')->filter()->values();

            return [
                'elemen' => $group->first()->elemenStandar,
                'avg_skor' => $scores->isNotEmpty() ? round($scores->avg(), 2) : 0,
                'count_asesor' => $scores->count(),
                'scores_detail' => $scores->toArray(),
            ];
        });

        $totalSkor = 0;
        $totalBobot = 0;
        $detailPerKriteria = [];
        $detailPerElemen = [];
        $pelampauanStandar = [];

        foreach ($elemenScores as $idElemen => $data) {
            $elemen = $data['elemen'];
            $kriteria = $elemen->kriteria;
            $avgSkor = $data['avg_skor'];

            // Get bobot
            $bobot = BobotPenilaian::where('id_elemen', $elemen->id)
                ->where('id_degree_level', $degreeLevelId)
                ->where('is_active', true)
                ->first();

            if (!$bobot) {
                Log::warning("Bobot tidak ditemukan untuk elemen {$elemen->kode_elemen}");
                continue;
            }

            // ✅ Track pelampauan standar (rata-rata skor >= 4)
            if ($avgSkor >= 4) {
                $kriteriaCode = $kriteria->kode_kriteria;

                if (!isset($pelampauanStandar[$kriteriaCode])) {
                    $pelampauanStandar[$kriteriaCode] = [];
                }

                $pelampauanStandar[$kriteriaCode][] = [
                    'kode_elemen' => $elemen->kode_elemen,
                    'nama_elemen' => $elemen->pernyataan_elemen,
                    'skor_rata' => $avgSkor,
                    'bobot' => $bobot->bobot,
                    'jumlah_asesor' => $data['count_asesor'],
                ];
            }

            $skorTertimbang = $avgSkor * $bobot->bobot;

            $totalSkor += $skorTertimbang;
            $totalBobot += $bobot->bobot;

            // Group by kriteria
            $kriteriaCode = $kriteria->kode_kriteria;
            if (!isset($detailPerKriteria[$kriteriaCode])) {
                $detailPerKriteria[$kriteriaCode] = [
                    'nama' => $kriteria->nama_kriteria,
                    'total_skor' => 0,
                    'total_bobot' => 0,
                    'elemen_count' => 0,
                    'has_pelampauan' => false,
                ];
            }

            $detailPerKriteria[$kriteriaCode]['total_skor'] += $skorTertimbang;
            $detailPerKriteria[$kriteriaCode]['total_bobot'] += $bobot->bobot;
            $detailPerKriteria[$kriteriaCode]['elemen_count']++;

            if ($avgSkor >= 4) {
                $detailPerKriteria[$kriteriaCode]['has_pelampauan'] = true;
            }

            // Detail per elemen
            $detailPerElemen[] = [
                'kode_elemen' => $elemen->kode_elemen,
                'nama_elemen' => $elemen->pernyataan_elemen,
                'kode_kriteria' => $kriteriaCode,
                'nama_kriteria' => $kriteria->nama_kriteria,
                'skor' => $avgSkor,
                'skor_kategori' => JenjangPenilaian::getSkorInfo(round($avgSkor)),
                'bobot' => $bobot->bobot,
                'skor_tertimbang' => $skorTertimbang,
                'jumlah_asesor' => $data['count_asesor'],
            ];
        }

        return [
            'skor_total' => round($totalSkor, 2),
            'skor_tertimbang' => round($totalSkor, 2),
            'total_bobot' => $totalBobot,
            'detail_kriteria' => $detailPerKriteria,
            'detail_elemen' => $detailPerElemen,
            'pelampauan_standar' => $pelampauanStandar,
            'jumlah_elemen' => count($detailPerElemen),
        ];
    }

    /**
     * ✅ Save/Update AK result
     */
    public function saveHasilAK(Asesmen $asesmen, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();

        try {
            $calculation = $this->calculateAK($asesmen);

            $hasil = HasilAkreditasi::updateOrCreate(
                [
                    'id_pengajuan' => $asesmen->id_pengajuan,
                    'id_asesmen'   => $asesmen->id,
                ],
                [
                    'id_study_program'       => $asesmen->id_study_program,
                    'id_category'            => $asesmen->studyProgram->id_category,
                    'status'                 => HasilAkreditasi::getStatusHasilAkreditasi($asesmen),

                    'skor_ak'                => $calculation['skor_total'],
                    'skor_ak_tertimbang'     => $calculation['skor_tertimbang'],
                    'total_bobot_ak'         => $calculation['total_bobot'],
                    'pelampauan_standar_ak'  => $calculation['pelampauan_standar'],

                    'detail_skor_ak' => [
                        'kriteria' => $calculation['detail_kriteria'],
                        'elemen'   => $calculation['detail_elemen'],
                        'metadata' => [
                            'jumlah_elemen' => $calculation['jumlah_elemen'],
                            'calculated_at' => now()->toISOString(),
                            'calculated_by' => $userId ?? auth()->id(),
                        ],
                    ],
                ]
            );

            DB::commit();
            return $hasil;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Save hasil AK failed', [
                'asesmen_id' => $asesmen->id,
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * ✅ Finalize AK result
     */
    public function finalizeHasilAK(HasilAkreditasi $hasil, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if ($hasil->isAkFinalized()) {
                throw new \Exception('Hasil AK sudah definalized.');
            }

            $hasil->update([
                'status' => 'final_ak',
                'tanggal_finalisasi_ak' => now(),
                'finalized_ak_by' => $userId ?? auth()->id(),
            ]);

            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ✅ Save/Update AL result
     */
    public function saveHasilAL(Asesmen $asesmen, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            $calculation = $this->calculateAL($asesmen);

            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            if (!$hasil->isAkFinalized()) {
                throw new \Exception('AK harus difinalisasi terlebih dahulu.');
            }

            $hasil->update([
                'skor_al' => $calculation['skor_total'],
                'skor_al_tertimbang' => $calculation['skor_tertimbang'],
                'total_bobot_al' => $calculation['total_bobot'],
                'detail_skor_al' => [
                    'kriteria' => $calculation['detail_kriteria'],
                    'elemen' => $calculation['detail_elemen'],
                    'metadata' => [
                        'jumlah_elemen' => $calculation['jumlah_elemen'],
                        'calculated_at' => now()->toISOString(),
                        'calculated_by' => $userId ?? auth()->id(),
                    ]
                ],
                'pelampauan_standar_al' => $calculation['pelampauan_standar'], // ✅ NEW
                'status' => 'draft_al',
            ]);

            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save hasil AL failed', [
                'asesmen_id' => $asesmen->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ Finalize AL and calculate final score (USING AL SCORE ONLY)
     */
    public function finalizeHasilAL(HasilAkreditasi $hasil, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if (!$hasil->isAkFinalized()) {
                throw new \Exception('AK harus difinalisasi terlebih dahulu.');
            }

            if ($hasil->isAlFinalized()) {
                throw new \Exception('Hasil AL sudah difinalisasi.');
            }

            // ✅ IMPORTANT: Use AL score as final score
            $skorFinal = $hasil->skor_al;

            // ✅ Check syarat pelampauan standar untuk Unggul
            $memenuhi_syarat = $hasil->memenuhi_syarat_unggul_check();
            $missingKriteria = $hasil->getMissingKriteriaForUnggul();

            // Build catatan validasi
            $catatanValidasi = [];

            if ($skorFinal >= 361) {
                if ($memenuhi_syarat) {
                    $catatanValidasi[] = "✅ Memenuhi syarat Status akreditasi Unggul (skor >= 361)";
                    $catatanValidasi[] = "✅ Semua kriteria (D, E, P, I, L, A, R) memiliki minimal 1 pelampauan standar";
                } else {
                    $catatanValidasi[] = "⚠️ Skor mencapai >= 361, namun TIDAK memenuhi syarat Unggul";
                    $catatanValidasi[] = "⚠️ Kriteria yang belum memiliki pelampauan standar: " . implode(', ', $missingKriteria);
                    $catatanValidasi[] = "⚠️ Status akreditasi diturunkan menjadi: Baik Sekali";
                }
            }

            // Get peringkat with validation
            $peringkat = $hasil->getPeringkatFromSkorAL($skorFinal);

            $hasil->update([
                'status' => 'final_al',
                'tanggal_finalisasi_al' => now(),
                'finalized_al_by' => $userId ?? auth()->id(),
                'skor_final' => round($skorFinal, 2),
                'peringkat_akreditasi' => $peringkat,
                'memenuhi_syarat_unggul' => $memenuhi_syarat,
                'catatan_validasi' => implode("\n", $catatanValidasi),
            ]);
            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ✅ Get validation summary for Unggul
     */
    public function getValidationSummary(HasilAkreditasi $hasil): array
    {
        $summary = [
            'skor_memenuhi' => false,
            'pelampauan_memenuhi' => false,
            'dapat_unggul' => false,
            'kriteria_status' => [],
            'missing_kriteria' => [],
        ];

        // Check skor
        if ($hasil->skor_final >= 361) {
            $summary['skor_memenuhi'] = true;
        }

        // Check pelampauan per kriteria
        $pelampauan = $hasil->pelampauan_standar_al ?? [];

        foreach (HasilAkreditasi::KRITERIA_REQUIRED as $kriteria) {
            $hasPelampauan = isset($pelampauan[$kriteria]) && !empty($pelampauan[$kriteria]);

            $summary['kriteria_status'][$kriteria] = [
                'has_pelampauan' => $hasPelampauan,
                'jumlah_elemen_skor_4' => $hasPelampauan ? count($pelampauan[$kriteria]) : 0,
                'elemen_list' => $hasPelampauan ? $pelampauan[$kriteria] : [],
            ];

            if (!$hasPelampauan) {
                $summary['missing_kriteria'][] = $kriteria;
            }
        }

        $summary['pelampauan_memenuhi'] = empty($summary['missing_kriteria']);
        $summary['dapat_unggul'] = $summary['skor_memenuhi'] && $summary['pelampauan_memenuhi'];

        return $summary;
    }

    /**
     * ✅ Get peringkat from skor
     */
    private function getPeringkatFromSkor(float $skor): string
    {
        // Standar LAMDEPILAR (sesuaikan dengan aturan resmi)
        if ($skor >= 361) return 'Unggul';
        if ($skor >= 301) return 'Baik Sekali';
        if ($skor >= 200) return 'Baik';
        return 'Tidak Terakreditasi';
    }

    /**
     * ✅ Publish result to prodi
     */
    public function publishHasil(HasilAkreditasi $hasil): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if (!$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi terlebih dahulu.');
            }

            $hasil->update([
                'status' => 'published',
            ]);

            // Update Permohonan akreditasi status
            $hasil->pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                'tanggal_hasil_akreditasi_dikirim' => now(),
            ]);

            // Update study program
            $hasil->studyProgram->update([
                'peringkat_akreditasi' => $hasil->peringkat_akreditasi,
                'tanggal_kedaluwarsa' => now()->addYears(5),
                'status_kedaluwarsa' => 'Aktif',
            ]);

            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
