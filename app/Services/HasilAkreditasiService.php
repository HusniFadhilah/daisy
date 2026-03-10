<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\BobotPenilaian;
use App\Models\HasilAkreditasi;
use App\Models\JenjangPenilaian;
use App\Models\Kriteria;
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
     * Evaluasi semua syarat Unggul:
     *   Syarat Kunci : skor >= minimum + rasio DTPS + jabatan/sertifikasi + capaian lulusan
     *   Syarat Perlu : semua kriteria ada elemen ".JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI." (skor >= 4)
     *
     * PENTING: $skorAl dioper eksplisit — jangan baca dari $hasil->skor_final
     * karena saat finalizeHasilAL() dipanggil, skor_final belum tersimpan.
     */
    public function cekSyaratUnggul(HasilAkreditasi $hasil, float $skorAl): array
    {
        $degreeLevelId = $hasil->studyProgram->id_degree_level;
        $config        = $this->syaratRepo->getAllConfig($degreeLevelId);
        $pengajuanId   = $hasil->id_pengajuan;
        $rumpun        = $hasil->studyProgram->rumpun ?? 'arsitektur';

        // ── Syarat Kunci 1: Skor ──
        $skorMin      = $config['skor_minimum'];
        $skorMemenuhi = $skorAl >= $skorMin;

        // ── Syarat Perlu: Pelampauan standar per kriteria ──
        $kriteriaRequired   = $config['kriteria_required'];
        $missingKriteria    = $hasil->getMissingKriteriaForUnggul($kriteriaRequired);
        $pelampauanMemenuhi = empty($missingKriteria);

        // ── Syarat Kunci 2,3,4: LKPS (rasio + jabatan + lulusan) ──
        $syaratLkps   = $this->lkpsReader->cekSemuaSyarat($pengajuanId, $rumpun, $degreeLevelId);
        $lkpsMemenuhi = $syaratLkps['semua_memenuhi'];

        $keterangan   = [];
        $keterangan[] = $skorMemenuhi
            ? "☑ Skor {$skorAl} memenuhi syarat minimum Unggul (≥ {$skorMin})."
            : "☒ Skor {$skorAl} belum memenuhi syarat minimum Unggul (< {$skorMin}).";
        $keterangan[] = $pelampauanMemenuhi
            ? "☑ Semua kriteria (" . implode(', ', $kriteriaRequired) . ") memiliki elemen " . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . "."
            : "☒ Kriteria belum ada elemen " . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ": " . implode(', ', $missingKriteria) . ".";
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

            // AK: tidak ada syarat Unggul → bySkor aman dipakai
            $idStatusAk = StatusAkreditasi::bySkor((int)$calc['skor_total'])->value('id');

            $hasil = HasilAkreditasi::updateOrCreate(
                [
                    'id_pengajuan' => $asesmen->id_pengajuan,
                    'id_asesmen'   => $asesmen->id,
                ],
                [
                    'id_status_ak'          => $idStatusAk,
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
            Log::error('saveHasilAK failed', ['asesmen_id' => $asesmen->id, 'error' => $e->getMessage()]);
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

    /**
     * Simpan hasil kalkulasi AL sebagai DRAFT.
     *
     * id_status_al/hasil TIDAK boleh menunjuk ke Unggul di tahap ini
     * karena syarat perlu (pelampauan + LKPS) belum divalidasi.
     * Peringkat efektif baru ditetapkan saat finalizeHasilAL().
     */
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

            // ── Gunakan status non-Unggul sebagai placeholder draft ──
            // Syarat perlu baru bisa dicek saat finalize, bukan di sini.
            $idStatusDraft = $this->resolveStatusIdForDraft((int)$calc['skor_total']);

            $hasil->update([
                'id_status_al'             => $idStatusDraft,
                'skor_al'                  => $calc['skor_total'],
                'skor_al_tertimbang'       => $calc['skor_tertimbang'],
                'total_bobot_al'           => $calc['total_bobot'],
                'pelampauan_standar_al'    => $calc['pelampauan_standar'],
                'detail_skor_al'           => $detailSkorAL,

                'id_status_hasil'          => $idStatusDraft,
                'skor_hasil'               => $calc['skor_total'],
                'skor_hasil_tertimbang'    => $calc['skor_tertimbang'],
                'total_bobot_hasil'        => $calc['total_bobot'],
                'pelampauan_standar_hasil' => $calc['pelampauan_standar'],
                'detail_skor_hasil'        => $detailSkorAL,

                // Dikosongkan — diisi saat finalizeHasilAL() dengan validasi penuh
                'peringkat_akreditasi_hasil' => null,

                'status' => 'draft_al',
            ]);

            DB::commit();
            return $hasil->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveHasilAL failed', ['asesmen_id' => $asesmen->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    // =========================================================
    // FINALIZE AL
    // =========================================================

    /**
     * Finalisasi AL: validasi semua syarat Unggul, tetapkan peringkat efektif,
     * dan kunci skor AL. skor_final belum diset — itu di saveHasilPenetapan().
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

            // Peringkat efektif — sudah mempertimbangkan syarat kunci + syarat perlu
            $peringkat   = $hasil->getPeringkatFromSkor($skorALFinal, $memenuhiPelampauan, $memenuhiLkps);

            // id_status_al diupdate ke status yang sesuai peringkat efektif
            $idStatusAL  = $this->resolveStatusIdByPeringkat($peringkat);

            $catatan = $syarat['keterangan'];

            if ($skorALFinal >= $syarat['skor_minimum'] && !$semuaMemenuhi) {
                $catatan[] = "⚠️ Skor ≥ {$syarat['skor_minimum']} namun tidak semua syarat Unggul terpenuhi.";
                $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$peringkat}.";

                if (!$memenuhiPelampauan && !empty($syarat['missing_kriteria'])) {
                    $catatan[] = "⚠️ Kriteria tanpa elemen " . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ": "
                        . implode(', ', $syarat['missing_kriteria']) . ".";
                }
                if (!$memenuhiLkps) {
                    $p1 = $syarat['syarat_p1'];
                    if (!$p1['rasio']['memenuhi']) {
                        $catatan[] = "⚠️ Rasio DTPS:Mahasiswa melebihi batas yang diizinkan.";
                    }
                    if (!$p1['jabatan']['memenuhi']) {
                        $catatan[] = "⚠️ Kompetensi dosen belum mencapai "
                            . ($p1['jabatan']['persen_minimum'] ?? 50) . "%.";
                    }
                    if (!$p1['lulusan']['memenuhi']) {
                        $catatan[] = "⚠️ Capaian lulusan belum mencapai "
                            . ($p1['lulusan']['persen_minimum'] ?? 10) . "%.";
                    }
                }
            } elseif ($semuaMemenuhi) {
                $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
            }

            $hasil->update([
                'status'                     => 'final_hasil',
                'tanggal_finalisasi_al'      => now(),
                'finalized_al_by'            => $authId,
                'tanggal_finalisasi_hasil'   => now(),
                'finalized_hasil_by'         => $authId,

                // Konsisten: id_status dan string peringkat menunjuk ke entitas yang sama
                'id_status_al'               => $idStatusAL,
                'id_status_hasil'            => $idStatusAL,
                'skor_hasil'                 => round($skorALFinal, 2),
                'peringkat_akreditasi_hasil' => $peringkat,
                'memenuhi_syarat_unggul'     => $semuaMemenuhi,
                'catatan_validasi'           => implode("\n", $catatan),

                'metadata' => array_merge(
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

            $skorFinal          = (float)$hasil->skor_al;
            $syarat             = $this->cekSyaratUnggul($hasil, $skorFinal);
            $memenuhiPelampauan = $syarat['pelampauan_memenuhi'];
            $memenuhiLkps       = $syarat['p1_memenuhi'];

            // Peringkat efektif — konsisten dengan finalizeHasilAL
            $peringkat     = $hasil->getPeringkatFromSkor($skorFinal, $memenuhiPelampauan, $memenuhiLkps);

            // id_status_final konsisten dengan peringkat efektif, bukan raw bySkor
            $idStatusFinal = $this->resolveStatusIdByPeringkat($peringkat);

            $hasil->update([
                'status'                     => 'draft_penetapan',
                'skor_final'                 => round($skorFinal, 2),
                'skor_final_tertimbang'      => round($skorFinal, 2),
                'total_bobot_final'          => $hasil->total_bobot_al,
                'detail_skor_final'          => $hasil->detail_skor_al,
                'pelampauan_standar_final'   => $hasil->pelampauan_standar_al,
                'id_status_final'            => $idStatusFinal,
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

            $authId  = $userId ?? auth()->id();
            $syarat  = $this->cekSyaratUnggul($hasil, (float)$hasil->skor_final);
            $catatan = $syarat['keterangan'];

            if (!$syarat['memenuhi'] && (float)$hasil->skor_final >= $syarat['skor_minimum']) {
                $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$hasil->peringkat_akreditasi_final}.";
                $p1 = $syarat['syarat_p1'];
                if (!$p1['rasio']['memenuhi'])   $catatan[] = "⚠️ " . $p1['rasio']['keterangan'];
                if (!$p1['jabatan']['memenuhi'])  $catatan[] = "⚠️ " . $p1['jabatan']['keterangan'];
                if (!$p1['lulusan']['memenuhi'])  $catatan[] = "⚠️ " . $p1['lulusan']['keterangan'];
                if (!$syarat['pelampauan_memenuhi'] && !empty($syarat['missing_kriteria'])) {
                    $catatan[] = "⚠️ Kriteria tanpa elemen " . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ": "
                        . implode(', ', $syarat['missing_kriteria']) . ".";
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
        $skor             = (float)($hasil->skor_final ?? $hasil->skor_al ?? 0);
        $syarat           = $this->cekSyaratUnggul($hasil, $skor);
        $pelampauan       = $hasil->pelampauan_standar_al ?? [];
        $kriteriaRequired = $this->syaratRepo->getKriteriaRequired();

        // Ambil nama kriteria sekaligus untuk ditampilkan di tabel
        $kriteriaNames = Kriteria::whereIn('kode_kriteria', $kriteriaRequired)
            ->pluck('nama_kriteria', 'kode_kriteria')
            ->toArray();

        $kriteriaStatus = [];
        foreach ($kriteriaRequired as $kode) {
            $hasPelampauan         = !empty($pelampauan[$kode]);
            $kriteriaStatus[$kode] = [
                'has_pelampauan'       => $hasPelampauan,
                'jumlah_elemen_skor_4' => $hasPelampauan ? count($pelampauan[$kode]) : 0,
                'elemen_list'          => $hasPelampauan ? $pelampauan[$kode] : [],
                'nama_kriteria'        => $kriteriaNames[$kode] ?? $kode,
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
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Untuk DRAFT: kembalikan id status non-Unggul tertinggi jika skor
     * masuk range Unggul. Unggul belum bisa ditetapkan sebelum syarat perlu divalidasi.
     */
    private function resolveStatusIdForDraft(int $skor): ?int
    {
        $all = $this->getAllStatusAkreditasi();

        $match = $all
            ->filter(fn($s) => $s->skor_min <= $skor && $s->skor_max >= $skor)
            ->sortByDesc('skor_min')
            ->first();

        if (!$match) {
            return null;
        }

        // Bukan Unggul → aman dipakai langsung
        if (!$this->isStatusUnggul($match)) {
            return $match->id;
        }

        // Unggul → ambil non-Unggul tertinggi sebagai placeholder
        return $this->getStatusNonUnggulTertinggi()?->id ?? $match->id;
    }

    /**
     * Setelah peringkat efektif diketahui (post-validasi syarat perlu),
     * cari id status yang string-nya match persis dengan peringkat tersebut.
     * Ini menjamin id_status_* dan peringkat_akreditasi_* selalu konsisten.
     */
    private function resolveStatusIdByPeringkat(string $peringkat): ?int
    {
        return $this->getAllStatusAkreditasi()
            ->firstWhere('status', $peringkat)
            ?->id;
    }

    /**
     * Status non-Unggul dengan skor_max tertinggi (tepat di bawah ambang Unggul).
     */
    private function getStatusNonUnggulTertinggi(): ?StatusAkreditasi
    {
        $all         = $this->getAllStatusAkreditasi();
        $batasUnggul = $all->filter(fn($s) => $this->isStatusUnggul($s))->min('skor_min');

        return $all
            ->filter(fn($s) => !$this->isStatusUnggul($s) && $s->skor_max < $batasUnggul)
            ->sortByDesc('skor_max')
            ->first();
    }

    private function isStatusUnggul(StatusAkreditasi $status): bool
    {
        return str_contains(strtolower($status->status), 'unggul');
    }

    /**
     * Cache semua StatusAkreditasi dalam satu request untuk menghindari
     * query berulang di resolveStatusIdForDraft, resolveStatusIdByPeringkat, dll.
     */
    private function getAllStatusAkreditasi(): Collection
    {
        static $cache = null;
        return $cache ??= StatusAkreditasi::all();
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

        $elemenIds = $elemenScores->pluck('elemen.id')->filter()->unique()->values();

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
