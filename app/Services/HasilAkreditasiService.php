<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\BobotPenilaian;
use App\Models\HasilAkreditasi;
use App\Models\JenjangPenilaian;
use App\Models\PengajuanAkreditasi;
use App\Models\PenilaianElemenAk;
use App\Models\PenilaianElemenAl;
use App\Models\StatusAkreditasi;
use App\Repositories\SyaratAkreditasiRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HasilAkreditasiService
{
    public function __construct(
        private readonly LkpsDataReaderService      $lkpsReader,
        private readonly SyaratAkreditasiRepository $syaratRepo,
    ) {}

    // =========================================================
    // CALCULATE AK
    // =========================================================

    public function calculateAK(Asesmen $asesmen): array
    {
        if (!$asesmen->studyProgram->id_category) {
            throw new \Exception('Program studi belum memiliki kategori.');
        }

        $penilaians = PenilaianElemenAk::where('id_asesmen', $asesmen->id)
            ->whereIn('status', ['approved', 'submitted'])
            ->with(['elemenStandar.kriteria'])
            ->get();

        if ($penilaians->isEmpty()) {
            throw new \Exception('Belum ada penilaian AK yang di-approve.');
        }

        $elemenScores = $penilaians->groupBy('id_elemen')->map(function ($group) {
            $scores = $group->pluck('skor_final')->filter()->values();
            if ($scores->isEmpty()) {
                $scores = $group->pluck('skor')->filter()->values();
            }

            return [
                'elemen'        => $group->first()->elemenStandar,
                'avg_skor'      => $scores->isNotEmpty() ? round($scores->avg(), 2) : 0,
                'count_asesor'  => $scores->count(),
                'scores_detail' => $scores->toArray(),
            ];
        });

        return $this->buildScoreResult($elemenScores, $asesmen->studyProgram->id_degree_level);
    }

    // =========================================================
    // CALCULATE AL
    // =========================================================

    public function calculateAL(Asesmen $asesmen): array
    {
        if (!$asesmen->studyProgram->id_category) {
            throw new \Exception('Program studi belum memiliki kategori.');
        }

        $penilaians = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
            ->whereIn('status', ['approved', 'submitted'])
            ->with(['elemenStandar.kriteria'])
            ->get();

        if ($penilaians->isEmpty()) {
            throw new \Exception('Belum ada penilaian AL yang di-approve.');
        }

        $elemenScores = $penilaians->groupBy('id_elemen')->map(function ($group) {
            $scores = $group->pluck('skor')->filter()->values();

            return [
                'elemen'        => $group->first()->elemenStandar,
                'avg_skor'      => $scores->isNotEmpty() ? round($scores->avg(), 2) : 0,
                'count_asesor'  => $scores->count(),
                'scores_detail' => $scores->toArray(),
            ];
        });

        return $this->buildScoreResult($elemenScores, $asesmen->studyProgram->id_degree_level);
    }

    // =========================================================
    // CEK SYARAT UNGGUL
    // =========================================================

    /**
     * Evaluasi semua syarat Unggul dengan konfigurasi dinamis dari DB:
     *
     *   1. Skor >= skor_minimum_unggul
     *   2. Semua kriteria_required ada elemen dengan skor rata >= 4
     *   3. Rasio DTPS:Mahasiswa sesuai batas rumpun
     *   4. Kompetensi dosen (jabatan atau sertifikat, per degree level)
     *   5. Capaian lulusan (tipe dan persentase, per degree level)
     *
     * PENTING: $skorAl dioper eksplisit — JANGAN baca dari $hasil->skor_final
     * karena saat finalizeHasilAL() dipanggil, skor_final belum tersimpan.
     */
    public function cekSyaratUnggul(HasilAkreditasi $hasil, float $skorAl): array
    {
        $degreeLevelId = $hasil->studyProgram->id_degree_level;

        // ── 1 query ke DB, di-cache per degree level ──
        $config = $this->syaratRepo->getAllConfig($degreeLevelId);

        $pengajuanId = $hasil->id_pengajuan;
        $rumpun      = $hasil->studyProgram->rumpun ?? 'arsitektur';

        // 1. Skor
        $skorMin      = $config['skor_minimum'];
        $skorMemenuhi = $skorAl >= $skorMin;

        // 2. Pelampauan standar per kriteria
        $kriteriaRequired   = $config['kriteria_required'];
        $missingKriteria    = $hasil->getMissingKriteriaForUnggul($kriteriaRequired);
        $pelampauanMemenuhi = empty($missingKriteria);

        // 3, 4, 5. Semua syarat dari LKPS (rasio + kompetensi dosen + capaian lulusan)
        $syaratLkps = $this->lkpsReader->cekSemuaSyarat($pengajuanId, $rumpun, $degreeLevelId);
        $lkpsMemenuhi = $syaratLkps['semua_memenuhi'];

        // ── Keterangan ──
        $keterangan = [];

        $keterangan[] = $skorMemenuhi
            ? "☑ Skor {$skorAl} memenuhi syarat minimum Unggul (≥ {$skorMin})."
            : "☒ Skor {$skorAl} belum memenuhi syarat minimum Unggul (< {$skorMin}).";

        $keterangan[] = $pelampauanMemenuhi
            ? "☑ Semua kriteria (" . implode(', ', $kriteriaRequired) . ") memiliki elemen Melampaui Standar."
            : "☒ Kriteria belum ada elemen Melampaui Standar: " . implode(', ', $missingKriteria) . ".";

        $keterangan[] = $syaratLkps['rasio']['keterangan'];
        $keterangan[] = $syaratLkps['jabatan']['keterangan'];
        $keterangan[] = $syaratLkps['lulusan']['keterangan'];

        return [
            'memenuhi'            => $skorMemenuhi && $pelampauanMemenuhi && $lkpsMemenuhi,
            'skor_memenuhi'       => $skorMemenuhi,
            'skor_minimum'        => $skorMin,
            'pelampauan_memenuhi' => $pelampauanMemenuhi,
            'missing_kriteria'    => $missingKriteria,
            'p1_memenuhi'         => $lkpsMemenuhi,
            'syarat_p1'           => $syaratLkps,
            'keterangan'          => $keterangan,
        ];
    }

    // =========================================================
    // SAVE & FINALIZE AK
    // =========================================================

    public function saveHasilAK(Asesmen $asesmen, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            $calc = $this->calculateAK($asesmen);

            $hasil = HasilAkreditasi::updateOrCreate(
                [
                    'id_pengajuan' => $asesmen->id_pengajuan,
                    'id_asesmen'   => $asesmen->id,
                ],
                [
                    'id_status_ak'          => StatusAkreditasi::bySkor((int)$calc['skor_total'])->value('id'),
                    'id_study_program'      => $asesmen->id_study_program,
                    'id_category'           => $asesmen->studyProgram->id_category,
                    'status'                => HasilAkreditasi::getStatusHasilAkreditasi($asesmen),
                    'skor_ak'               => $calc['skor_total'],
                    'skor_ak_tertimbang'    => $calc['skor_tertimbang'],
                    'total_bobot_ak'        => $calc['total_bobot'],
                    'pelampauan_standar_ak' => $calc['pelampauan_standar'],
                    'detail_skor_ak'        => [
                        'kriteria' => $calc['detail_kriteria'],
                        'elemen'   => $calc['detail_elemen'],
                        'metadata' => [
                            'jumlah_elemen' => $calc['jumlah_elemen'],
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
            Log::error('saveHasilAK failed', [
                'asesmen_id' => $asesmen->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function finalizeHasilAK(HasilAkreditasi $hasil, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if ($hasil->isAkFinalized()) {
                throw new \Exception('Hasil AK sudah difinalisasi.');
            }

            $hasil->update([
                'status'                => 'final_ak',
                'tanggal_finalisasi_ak' => now(),
                'finalized_ak_by'       => $userId ?? auth()->id(),
            ]);

            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================
    // SAVE AL
    // =========================================================

    public function saveHasilAL(Asesmen $asesmen, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            if (!$hasil->isAkFinalized()) {
                throw new \Exception('AK harus difinalisasi terlebih dahulu.');
            }

            $calc = $this->calculateAL($asesmen);

            $detailSkorAL = [
                'kriteria' => $calc['detail_kriteria'],
                'elemen'   => $calc['detail_elemen'],
                'metadata' => [
                    'jumlah_elemen' => $calc['jumlah_elemen'],
                    'calculated_at' => now()->toISOString(),
                    'calculated_by' => $userId ?? auth()->id(),
                ],
            ];

            $idStatusAL = StatusAkreditasi::bySkor((int)$calc['skor_total'])->value('id');

            $hasil->update([
                'id_status_al'          => $idStatusAL,
                'skor_al'               => $calc['skor_total'],
                'skor_al_tertimbang'    => $calc['skor_tertimbang'],
                'total_bobot_al'        => $calc['total_bobot'],
                'pelampauan_standar_al' => $calc['pelampauan_standar'],
                'detail_skor_al'        => $detailSkorAL,

                'id_status_hasil'          => $idStatusAL,
                'skor_hasil'               => $calc['skor_total'],
                'skor_hasil_tertimbang'    => $calc['skor_tertimbang'],
                'total_bobot_hasil'        => $calc['total_bobot'],
                'pelampauan_standar_hasil' => $calc['pelampauan_standar'],
                'detail_skor_hasil'        => $detailSkorAL,

                'status' => 'draft_al',
            ]);

            DB::commit();
            return $hasil->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveHasilAL failed', [
                'asesmen_id' => $asesmen->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // =========================================================
    // FINALIZE AL
    // =========================================================

    /**
     * Finalisasi AL: kunci skor AL dan simpan hasil cek syarat Unggul.
     * skor_final BELUM diset di sini — itu dilakukan di saveHasilPenetapan().
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
            if (is_null($hasil->skor_al)) {
                throw new \Exception('Skor AL belum dihitung. Panggil saveHasilAL() terlebih dahulu.');
            }

            $skorALFinal = (float)$hasil->skor_al;
            $authId      = $userId ?? auth()->id();

            $syarat             = $this->cekSyaratUnggul($hasil, $skorALFinal);
            $memenuhiPelampauan = $syarat['pelampauan_memenuhi'];
            $memenuhiLkps       = $syarat['p1_memenuhi'];
            $semuaMemenuhi      = $syarat['memenuhi'];

            $peringkat = $hasil->getPeringkatFromSkorAL(
                $skorALFinal,
                $memenuhiPelampauan,
                $memenuhiLkps
            );

            $catatan = $syarat['keterangan'];

            if ($skorALFinal >= $syarat['skor_minimum'] && !$semuaMemenuhi) {
                $catatan[] = "⚠️ Skor ≥ {$syarat['skor_minimum']} namun tidak semua syarat Unggul terpenuhi.";
                $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$peringkat}.";

                if (!$memenuhiPelampauan && !empty($syarat['missing_kriteria'])) {
                    $catatan[] = "⚠️ Kriteria tanpa elemen Melampaui Standar: "
                        . implode(', ', $syarat['missing_kriteria']) . ".";
                }

                if (!$memenuhiLkps) {
                    $syaratP1 = $syarat['syarat_p1'];

                    if (!$syaratP1['rasio']['memenuhi']) {
                        $catatan[] = "⚠️ Rasio DTPS:Mahasiswa melebihi batas yang diizinkan.";
                    }
                    if (!$syaratP1['jabatan']['memenuhi']) {
                        $catatan[] = "⚠️ Kompetensi dosen (jabatan/sertifikat) belum mencapai "
                            . ($syaratP1['jabatan']['persen_minimum'] ?? 50) . "%.";
                    }
                    if (!$syaratP1['lulusan']['memenuhi']) {
                        $catatan[] = "⚠️ Capaian lulusan belum mencapai "
                            . ($syaratP1['lulusan']['persen_minimum'] ?? 10) . "%.";
                    }
                }
            } elseif ($semuaMemenuhi) {
                $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
            }

            $hasil->update([
                'status'                    => 'final_hasil',
                'tanggal_finalisasi_al'     => now(),
                'finalized_al_by'           => $authId,
                'tanggal_finalisasi_hasil'  => now(),
                'finalized_hasil_by'        => $authId,
                'skor_hasil'                => round($skorALFinal, 2),
                'peringkat_akreditasi_hasil' => $peringkat,
                'memenuhi_syarat_unggul'    => $semuaMemenuhi,
                'catatan_validasi'          => implode("\n", $catatan),
                'metadata'                  => array_merge(
                    (array)($hasil->metadata ?? []),
                    [
                        'syarat_unggul_check_al' => [
                            'checked_at'          => now()->toISOString(),
                            'checked_by'          => $authId,
                            'skor_al'             => $skorALFinal,
                            'skor_minimum'        => $syarat['skor_minimum'],
                            'skor_memenuhi'       => $syarat['skor_memenuhi'],
                            'pelampauan_memenuhi' => $memenuhiPelampauan,
                            'missing_kriteria'    => $syarat['missing_kriteria'],
                            'p1_memenuhi'         => $memenuhiLkps,
                            'rasio_dtps'          => $syarat['syarat_p1']['rasio'],
                            'jabatan_dtps'        => $syarat['syarat_p1']['jabatan'],
                            'lulusan'             => $syarat['syarat_p1']['lulusan'],
                        ],
                    ]
                ),
            ]);

            DB::commit();
            return $hasil->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================
    // SAVE & FINALIZE PENETAPAN
    // =========================================================

    public function saveHasilPenetapan(HasilAkreditasi $hasil, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if (!$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi sebelum penetapan.');
            }
            if ($hasil->isPenetapanFinalized()) {
                throw new \Exception('Penetapan sudah dikunci, tidak bisa dihitung ulang.');
            }

            $skorFinal = (float)$hasil->skor_al;

            $syarat             = $this->cekSyaratUnggul($hasil, $skorFinal);
            $memenuhiPelampauan = $syarat['pelampauan_memenuhi'];
            $memenuhiLkps       = $syarat['p1_memenuhi'];

            $peringkat = $hasil->getPeringkatFromSkorAL(
                $skorFinal,
                $memenuhiPelampauan,
                $memenuhiLkps
            );

            $statusFinalId = StatusAkreditasi::bySkor((int)$skorFinal)->value('id');

            $hasil->update([
                'status'                     => 'draft_penetapan',
                'skor_final'                 => round($skorFinal, 2),
                'skor_final_tertimbang'      => round($skorFinal, 2),
                'total_bobot_final'          => $hasil->total_bobot_al,
                'detail_skor_final'          => $hasil->detail_skor_al,
                'pelampauan_standar_final'   => $hasil->pelampauan_standar_al,
                'id_status_final'            => $statusFinalId,
                'peringkat_akreditasi_final' => $peringkat,
                'memenuhi_syarat_unggul'     => $syarat['memenuhi'],
            ]);

            DB::commit();
            return $hasil->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function finalizeHasilPenetapan(HasilAkreditasi $hasil, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if (!$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi sebelum penetapan.');
            }
            if ($hasil->isPenetapanFinalized()) {
                throw new \Exception('Penetapan sudah dikunci sebelumnya.');
            }
            if (is_null($hasil->skor_final)) {
                throw new \Exception('Skor final belum disiapkan. Panggil saveHasilPenetapan() terlebih dahulu.');
            }

            $authId = $userId ?? auth()->id();

            $syarat  = $this->cekSyaratUnggul($hasil, (float)$hasil->skor_final);
            $catatan = $syarat['keterangan'];

            if (!$syarat['memenuhi'] && (float)$hasil->skor_final >= $syarat['skor_minimum']) {
                $catatan[] = "⚠️ Peringkat diturunkan dari potensi Unggul menjadi: {$hasil->peringkat_akreditasi_final}.";

                if (!$syarat['syarat_p1']['lulusan']['memenuhi']) {
                    $catatan[] = "⚠️ Capaian lulusan belum mencapai "
                        . ($syarat['syarat_p1']['lulusan']['persen_minimum'] ?? 10) . "%.";
                }
            } elseif ($syarat['memenuhi']) {
                $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
            }

            $hasil->update([
                'status'                       => 'final_penetapan',
                'tanggal_finalisasi_penetapan' => now(),
                'finalized_penetapan_by'       => $authId,
                'catatan_penetapan'            => implode("\n", $catatan),
                'metadata'                     => array_merge(
                    (array)($hasil->metadata ?? []),
                    [
                        'syarat_unggul_check_penetapan' => [
                            'checked_at'          => now()->toISOString(),
                            'checked_by'          => $authId,
                            'skor_final'          => $hasil->skor_final,
                            'peringkat'           => $hasil->peringkat_akreditasi_final,
                            'skor_memenuhi'       => $syarat['skor_memenuhi'],
                            'pelampauan_memenuhi' => $syarat['pelampauan_memenuhi'],
                            'p1_memenuhi'         => $syarat['p1_memenuhi'],
                            'rasio_dtps'          => $syarat['syarat_p1']['rasio'],
                            'jabatan_dtps'        => $syarat['syarat_p1']['jabatan'],
                            'lulusan'             => $syarat['syarat_p1']['lulusan'],
                        ],
                    ]
                ),
            ]);

            DB::commit();
            return $hasil->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================
    // PUBLISH
    // =========================================================

    public function publishHasil(HasilAkreditasi $hasil): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if (!$hasil->isPenetapanFinalized()) {
                throw new \Exception('Penetapan harus difinalisasi sebelum dipublikasikan.');
            }

            $hasil->update(['status' => 'published']);

            $hasil->pengajuan->update([
                'status'                           => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                'tanggal_hasil_akreditasi_dikirim' => now(),
            ]);

            $hasil->studyProgram->update([
                'peringkat_akreditasi' => $hasil->peringkat_akreditasi_final,
                'tanggal_kedaluwarsa'  => now()->addYears(5),
                'status_kedaluwarsa'   => 'Aktif',
            ]);

            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================
    // VALIDATION SUMMARY (untuk UI)
    // =========================================================

    public function getValidationSummary(HasilAkreditasi $hasil): array
    {
        $skor = (float)($hasil->skor_final ?? $hasil->skor_al ?? 0);

        $syarat           = $this->cekSyaratUnggul($hasil, $skor);
        $pelampauan       = $hasil->pelampauan_standar_al ?? [];
        $kriteriaRequired = $this->syaratRepo->getKriteriaRequired();
        $kriteriaStatus   = [];

        foreach ($kriteriaRequired as $kode) {
            $hasPelampauan         = !empty($pelampauan[$kode]);
            $kriteriaStatus[$kode] = [
                'has_pelampauan'       => $hasPelampauan,
                'jumlah_elemen_skor_4' => $hasPelampauan ? count($pelampauan[$kode]) : 0,
                'elemen_list'          => $hasPelampauan ? $pelampauan[$kode] : [],
            ];
        }

        return [
            'skor'                => $skor,
            'skor_minimum'        => $syarat['skor_minimum'],
            'skor_memenuhi'       => $syarat['skor_memenuhi'],
            'pelampauan_memenuhi' => $syarat['pelampauan_memenuhi'],
            'p1_memenuhi'         => $syarat['p1_memenuhi'],
            'dapat_unggul'        => $syarat['memenuhi'],
            'kriteria_status'     => $kriteriaStatus,
            'missing_kriteria'    => $syarat['missing_kriteria'],
            'syarat_p1'           => $syarat['syarat_p1'],
            'keterangan'          => $syarat['keterangan'],
        ];
    }

    // =========================================================
    // PRIVATE: SCORE BUILDER
    // =========================================================

    private function buildScoreResult(Collection $elemenScores, ?int $degreeLevelId): array
    {
        $totalSkor         = 0.0;
        $totalBobot        = 0.0;
        $detailPerKriteria = [];
        $detailPerElemen   = [];
        $pelampauanStandar = [];

        if (empty($degreeLevelId)) {
            Log::warning("Degree level tidak tersedia saat buildScoreResult.");
            return [
                'skor_total'         => 0,
                'skor_tertimbang'    => 0,
                'total_bobot'        => 0,
                'detail_kriteria'    => [],
                'detail_elemen'      => [],
                'pelampauan_standar' => [],
                'jumlah_elemen'      => 0,
            ];
        }

        $elemenIds = $elemenScores
            ->pluck('elemen.id')
            ->filter()
            ->unique()
            ->values();

        $bobotMap = BobotPenilaian::query()
            ->whereIn('id_elemen', $elemenIds)
            ->where('id_degree_level', $degreeLevelId)
            ->where('is_active', true)
            ->get()
            ->keyBy('id_elemen');

        foreach ($elemenScores as $data) {
            $elemen   = $data['elemen'];
            $kriteria = $elemen->kriteria;
            $avgSkor  = (float)$data['avg_skor'];

            $bobotRow = $bobotMap->get($elemen->id);

            if (!$bobotRow) {
                Log::warning("Bobot tidak ditemukan untuk elemen {$elemen->kode_elemen}", [
                    'id_elemen'       => $elemen->id,
                    'id_degree_level' => $degreeLevelId,
                ]);
                continue;
            }

            $bobot          = (float)$bobotRow->bobot;
            $kode           = $kriteria->kode_kriteria;
            $skorTertimbang = $avgSkor * $bobot;

            if ($avgSkor >= 4) {
                $pelampauanStandar[$kode][] = [
                    'kode_elemen'   => $elemen->kode_elemen,
                    'nama_elemen'   => $elemen->pernyataan_elemen,
                    'skor_rata'     => $avgSkor,
                    'bobot'         => $bobot,
                    'jumlah_asesor' => $data['count_asesor'],
                ];
            }

            $totalSkor  += $skorTertimbang;
            $totalBobot += $bobot;

            if (!isset($detailPerKriteria[$kode])) {
                $detailPerKriteria[$kode] = [
                    'nama'           => $kriteria->nama_kriteria,
                    'total_skor'     => 0.0,
                    'total_bobot'    => 0.0,
                    'elemen_count'   => 0,
                    'has_pelampauan' => false,
                ];
            }

            $detailPerKriteria[$kode]['total_skor']  += $skorTertimbang;
            $detailPerKriteria[$kode]['total_bobot'] += $bobot;
            $detailPerKriteria[$kode]['elemen_count']++;

            if ($avgSkor >= 4) {
                $detailPerKriteria[$kode]['has_pelampauan'] = true;
            }

            $detailPerElemen[] = [
                'kode_elemen'     => $elemen->kode_elemen,
                'nama_elemen'     => $elemen->pernyataan_elemen,
                'kode_kriteria'   => $kode,
                'nama_kriteria'   => $kriteria->nama_kriteria,
                'skor'            => $avgSkor,
                'skor_kategori'   => JenjangPenilaian::getSkorInfo(round($avgSkor)),
                'bobot'           => $bobot,
                'skor_tertimbang' => $skorTertimbang,
                'jumlah_asesor'   => $data['count_asesor'],
            ];
        }

        return [
            'skor_total'         => round($totalSkor, 2),
            'skor_tertimbang'    => round($totalSkor, 2),
            'total_bobot'        => round($totalBobot, 2),
            'detail_kriteria'    => $detailPerKriteria,
            'detail_elemen'      => $detailPerElemen,
            'pelampauan_standar' => $pelampauanStandar,
            'jumlah_elemen'      => count($detailPerElemen),
        ];
    }
}
