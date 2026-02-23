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
        private readonly LkpsDataReaderService       $lkpsReader,
        private readonly SyaratAkreditasiRepository  $syaratRepo,
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
            // Utamakan skor_final (sudah divalidasi); fallback ke skor awal asesor
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
     *   1. Skor >= skor_minimum_unggul    (dari tabel syarat_akreditasi)
     *   2. Semua kriteria_required ada    (dari tabel syarat_akreditasi)
     *      elemen dengan skor rata >= 4
     *   3. Rasio DTPS:Mahasiswa sesuai    (batas dari syarat_akreditasi,
     *      batas rumpun                   data dari borang_data_excel E.2 + P.1)
     *   4. DTPS jabatan Lektor ke atas    (jabatan valid + persen minimum
     *      >= persen_minimum_lektor       dari syarat_akreditasi,
     *                                     data dari borang_data_excel P.1)
     *
     * PENTING: $skorAl dioper eksplisit dari caller — JANGAN baca dari
     * $hasil->skor_final karena saat finalizeHasilAL() dipanggil,
     * skor_final belum tersimpan ke DB.
     *
     * @param  HasilAkreditasi  $hasil
     * @param  float            $skorAl  Skor AL aktual (dari $hasil->skor_al)
     * @return array{
     *   memenuhi: bool,
     *   skor_memenuhi: bool,
     *   skor_minimum: int,
     *   pelampauan_memenuhi: bool,
     *   missing_kriteria: string[],
     *   p1_memenuhi: bool,
     *   syarat_p1: array,
     *   keterangan: string[]
     * }
     */
    public function cekSyaratUnggul(HasilAkreditasi $hasil, float $skorAl): array
    {
        // ── Ambil semua config sekaligus (1 query, di-cache) ──
        $config = $this->syaratRepo->getAllConfig();

        $pengajuanId = $hasil->id_pengajuan;
        $rumpun      = $hasil->studyProgram->rumpun ?? 'arsitektur';

        // 1. Skor
        $skorMin      = $config['skor_minimum'];
        $skorMemenuhi = $skorAl >= $skorMin;

        // 2. Pelampauan standar per kriteria (menggunakan kriteria dinamis dari DB)
        $kriteriaRequired   = $config['kriteria_required'];
        $missingKriteria    = $hasil->getMissingKriteriaForUnggul($kriteriaRequired);
        $pelampauanMemenuhi = empty($missingKriteria);

        // 3 & 4. Syarat P.1 dari data LKPS (E.2 + P.1)
        // LkpsDataReaderService juga sudah inject SyaratAkreditasiRepository
        // sehingga batas rasio & jabatan valid juga dinamis
        $syaratP1   = $this->lkpsReader->cekSemuaSyaratP1($pengajuanId, $rumpun);
        $p1Memenuhi = $syaratP1['semua_memenuhi'];

        // ── Keterangan ──
        $keterangan = [];

        $keterangan[] = $skorMemenuhi
            ? "☑ Skor {$skorAl} memenuhi syarat minimum Unggul (≥ {$skorMin})."
            : "☒ Skor {$skorAl} belum memenuhi syarat minimum Unggul (< {$skorMin}).";

        $keterangan[] = $pelampauanMemenuhi
            ? "☑ Semua kriteria (" . implode(', ', $kriteriaRequired) . ") memiliki elemen Melampaui Standar."
            : "☒ Kriteria berikut belum ada elemen Melampaui Standar: " . implode(', ', $missingKriteria) . ".";

        $keterangan[] = $syaratP1['rasio']['keterangan'];
        $keterangan[] = $syaratP1['jabatan']['keterangan'];

        return [
            'memenuhi'            => $skorMemenuhi && $pelampauanMemenuhi && $p1Memenuhi,
            'skor_memenuhi'       => $skorMemenuhi,
            'skor_minimum'        => $skorMin,
            'pelampauan_memenuhi' => $pelampauanMemenuhi,
            'missing_kriteria'    => $missingKriteria,
            'p1_memenuhi'         => $p1Memenuhi,
            'syarat_p1'           => $syaratP1,
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
                    'id_status_ak' => StatusAkreditasi::bySkor((int)$calc['skor_total'])->value('id'),
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
            return $hasil->fresh(); // refresh agar pelampauan_standar_al terbaca cast array
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
     * Finalisasi AL:
     *   1. Pastikan pelampauan_standar_al sudah tersimpan (saveHasilAL dipanggil duluan)
     *   2. Ambil skor_al dari DB (sudah fresh setelah saveHasilAL)
     *   3. Oper skor_al ke cekSyaratUnggul() — BUKAN skor_final (belum ada)
     *   4. Tentukan peringkat dengan downgrade bila syarat tidak terpenuhi
     *   5. Simpan skor_final, peringkat, catatan, dan audit trail ke metadata
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

            // ☑ Gunakan skor_al bukan skor_final — skor_final belum diset saat ini
            $skorALFinal = (float)$hasil->skor_al;
            $authId = auth()->id();
            // ☑ Oper skorAl eksplisit agar cekSyaratUnggul tidak perlu membaca skor_final
            $syarat             = $this->cekSyaratUnggul($hasil, $skorALFinal);
            $memenuhiPelampauan = $syarat['pelampauan_memenuhi'];
            $memenuhiP1         = $syarat['p1_memenuhi'];
            $semuaMemenuhi      = $syarat['memenuhi'];

            // Tentukan peringkat (downgrade otomatis jika syarat tidak terpenuhi)
            $peringkat = $hasil->getPeringkatFromSkorAL(
                $skorALFinal,
                $memenuhiPelampauan,
                $memenuhiP1
            );

            // Susun catatan validasi
            $catatan = $syarat['keterangan'];

            if ($skorALFinal >= $syarat['skor_minimum'] && !$semuaMemenuhi) {
                $catatan[] = "⚠️ Skor ≥ {$syarat['skor_minimum']} namun tidak semua syarat Unggul terpenuhi.";
                $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$peringkat}.";

                if (!$memenuhiPelampauan && !empty($syarat['missing_kriteria'])) {
                    $catatan[] = "⚠️ Kriteria tanpa elemen Melampaui Standar: "
                        . implode(', ', $syarat['missing_kriteria']) . ".";
                }
                if (!$memenuhiP1) {
                    if (!$syarat['syarat_p1']['rasio']['memenuhi']) {
                        $catatan[] = "⚠️ Rasio DTPS:Mahasiswa melebihi batas yang diizinkan.";
                    }
                    if (!$syarat['syarat_p1']['jabatan']['memenuhi']) {
                        $catatan[] = "⚠️ DTPS jabatan Lektor ke atas belum mencapai "
                            . $syarat['syarat_p1']['jabatan']['persen_minimum'] . "%.";
                    }
                }
            } elseif ($semuaMemenuhi) {
                $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
            }

            $hasil->update([
                'status'                 => 'final_hasil',
                'tanggal_finalisasi_al'  => now(),
                'finalized_al_by'        => $userId ?? $authId,
                'tanggal_finalisasi_hasil'  => now(),
                'finalized_hasil_by'        => $userId ?? $authId,
                'skor_hasil'             => round($skorALFinal, 2),
                'peringkat_akreditasi_hasil'   => $peringkat,
                'memenuhi_syarat_unggul' => $semuaMemenuhi,
                'catatan_validasi'       => implode("\n", $catatan),
                // Audit trail lengkap di metadata
                'metadata' => array_merge(
                    (array)($hasil->metadata ?? []),
                    [
                        'syarat_unggul_check' => [
                            'checked_at'           => now()->toISOString(),
                            'checked_by'           => $userId ?? $authId,
                            'skor_al'              => $skorALFinal,
                            'skor_minimum'         => $syarat['skor_minimum'],
                            'skor_memenuhi'        => $syarat['skor_memenuhi'],
                            'pelampauan_memenuhi'  => $memenuhiPelampauan,
                            'missing_kriteria'     => $syarat['missing_kriteria'],
                            'p1_memenuhi'          => $memenuhiP1,
                            'rasio_dtps'           => $syarat['syarat_p1']['rasio'],
                            'jabatan_dtps'         => $syarat['syarat_p1']['jabatan'],
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

            // Skor final = skor AL (atau bisa kombinasi AK+AL jika ada formula)
            $skorFinal = (float) $hasil->skor_al;

            $syarat             = $this->cekSyaratUnggul($hasil, $skorFinal);
            $memenuhiPelampauan = $syarat['pelampauan_memenuhi'];
            $memenuhiP1         = $syarat['p1_memenuhi'];

            $peringkat = $hasil->getPeringkatFromSkorAL(
                $skorFinal,
                $memenuhiPelampauan,
                $memenuhiP1
            );

            $statusFinalId = StatusAkreditasi::bySkor((int)$skorFinal)->value('id');

            $hasil->update([
                'status'               => 'draft_penetapan',
                'skor_final'           => round($skorFinal, 2),
                'skor_final_tertimbang' => round($skorFinal, 2),
                'total_bobot_final' => $hasil->total_bobot_al,
                'detail_skor_final' => $hasil->detail_skor_al,
                'pelampauan_standar_final' => $hasil->pelampauan_standar_al,
                'id_status_final'      => $statusFinalId,
                'peringkat_akreditasi_final' => $peringkat,
                'memenuhi_syarat_unggul' => $syarat['memenuhi'],
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

            // Re-check syarat dengan skor_final yang sudah tersimpan
            $syarat  = $this->cekSyaratUnggul($hasil, (float) $hasil->skor_final);
            $catatan = $syarat['keterangan'];

            if (!$syarat['memenuhi'] && (float)$hasil->skor_final >= $syarat['skor_minimum']) {
                $catatan[] = "⚠️ Peringkat diturunkan dari potensi Unggul menjadi: {$hasil->peringkat_akreditasi_final}.";
            } elseif ($syarat['memenuhi']) {
                $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
            }

            $hasil->update([
                'status'                         => 'final_penetapan',
                'tanggal_finalisasi_penetapan'   => now(),
                'finalized_penetapan_by'         => $userId ?? auth()->id(),
                'catatan_penetapan'              => implode("\n", $catatan),

                // Update metadata dengan audit penetapan
                'metadata' => array_merge(
                    (array)($hasil->metadata ?? []),
                    [
                        'syarat_unggul_check_penetapan' => [
                            'checked_at'          => now()->toISOString(),
                            'checked_by'          => $userId ?? auth()->id(),
                            'skor_final'          => $hasil->skor_final,
                            'peringkat'           => $hasil->peringkat_akreditasi_final,
                            'skor_memenuhi'       => $syarat['skor_memenuhi'],
                            'pelampauan_memenuhi' => $syarat['pelampauan_memenuhi'],
                            'p1_memenuhi'         => $syarat['p1_memenuhi'],
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
            if (!$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi terlebih dahulu.');
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

    /**
     * Ringkasan lengkap semua syarat untuk ditampilkan di halaman hasil.
     * Bisa dipanggil sebelum maupun setelah finalize.
     *
     * @param  HasilAkreditasi  $hasil
     * @return array
     */
    public function getValidationSummary(HasilAkreditasi $hasil): array
    {
        // Gunakan skor_final jika sudah ada, fallback ke skor_al
        $skor = (float)($hasil->skor_final ?? $hasil->skor_al ?? 0);

        $syarat     = $this->cekSyaratUnggul($hasil, $skor);
        $pelampauan = $hasil->pelampauan_standar_al ?? [];

        $kriteriaRequired = $this->syaratRepo->getKriteriaRequired();
        $kriteriaStatus   = [];

        foreach ($kriteriaRequired as $kode) {
            $hasPelampauan = !empty($pelampauan[$kode]);
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
    // PRIVATE: SCORE BUILDER (dipakai AK & AL)
    // =========================================================

    private function buildScoreResult(Collection $elemenScores, ?int $degreeLevelId): array
    {
        $totalSkor         = 0.0;
        $totalBobot        = 0.0;
        $detailPerKriteria = [];
        $detailPerElemen   = [];
        $pelampauanStandar = [];

        // Jika degree level tidak ada, tidak bisa ambil bobot
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

        // =========================================================
        // ☑ PREFETCH BOBOT SEKALI (hindari N+1)
        // =========================================================

        // Ambil semua id elemen yang akan diproses
        $elemenIds = $elemenScores
            ->pluck('elemen.id')
            ->filter()
            ->unique()
            ->values();

        // 1 query: ambil semua bobot aktif untuk degree level tsb
        $bobotMap = BobotPenilaian::query()
            ->whereIn('id_elemen', $elemenIds)
            ->where('id_degree_level', $degreeLevelId)
            ->where('is_active', true)
            ->get()
            ->keyBy('id_elemen'); // map: id_elemen => BobotPenilaian

        foreach ($elemenScores as $data) {
            /** @var \App\Models\ElemenStandar $elemen */
            $elemen   = $data['elemen'];
            $kriteria = $elemen->kriteria; // sudah eager loaded dari calculateAK/AL
            $avgSkor  = (float) $data['avg_skor'];

            // Ambil bobot dari map (O(1), tanpa query)
            $bobotRow = $bobotMap->get($elemen->id);

            if (!$bobotRow) {
                Log::warning("Bobot tidak ditemukan untuk elemen {$elemen->kode_elemen}", [
                    'id_elemen'       => $elemen->id,
                    'id_degree_level' => $degreeLevelId,
                ]);
                continue;
            }

            $bobot          = (float) $bobotRow->bobot;
            $kode           = $kriteria->kode_kriteria;
            $skorTertimbang = $avgSkor * $bobot;

            // Track pelampauan (skor rata ≥ 4)
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

            // Akumulasi per kriteria
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
