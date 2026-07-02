<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\BobotPenilaian;
use App\Models\HasilAkreditasi;
use App\Models\JenjangPenilaian;
use App\Models\Kriteria;
use App\Models\PengajuanAkreditasi;
use App\Models\PenilaianElemenAk;
use App\Models\PenilaianElemenAkBanding;
use App\Models\PenilaianElemenAl;
use App\Models\PenilaianElemenAlBanding;
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

    public function calculateAK(Asesmen $asesmen, $isBanding = false): array
    {
        if (!$asesmen->studyProgram->id_category) {
            throw new \Exception('Program studi belum memiliki kategori.');
        }
        if ($isBanding)
            $penilaians = PenilaianElemenAkBanding::where('id_asesmen', $asesmen->id)
                ->whereIn('status', ['approved', 'submitted'])
                ->with(['elemenStandar.kriteria'])
                ->get();
        else
            $penilaians = PenilaianElemenAk::where('id_asesmen', $asesmen->id)
                ->whereIn('status', ['approved', 'submitted'])
                ->with(['elemenStandar.kriteria'])
                ->get();

        if ($penilaians->isEmpty()) {
            throw new \Exception('Belum ada penilaian AK ' . ($isBanding ? 'banding' : '') . ' yang di-approve.');
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

    public function calculateAL(Asesmen $asesmen, $isBanding = false): array
    {
        if (!$asesmen->studyProgram->id_category) {
            throw new \Exception('Program studi belum memiliki kategori.');
        }

        if ($isBanding)
            $penilaians = PenilaianElemenAlBanding::where('id_asesmen', $asesmen->id)
                ->whereIn('status', ['approved', 'submitted'])
                ->with(['elemenStandar.kriteria'])
                ->get();
        else
            $penilaians = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
                ->whereIn('status', ['approved', 'submitted'])
                ->with(['elemenStandar.kriteria'])
                ->get();

        if ($penilaians->isEmpty()) {
            throw new \Exception('Belum ada penilaian AL ' . ($isBanding ? 'banding' : '') . ' yang di-approve.');
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
     * PENTING: $skor dioper eksplisit — jangan baca dari $hasil->skor_final
     * karena saat finalizeHasilAL() dipanggil, skor_final belum tersimpan.
     */
    public function cekSyaratUnggul(HasilAkreditasi $hasil, float $skor, $type = 'al'): array
    {
        $degreeLevelId = $hasil->studyProgram->id_degree_level;
        $config        = $this->syaratRepo->getAllConfig($degreeLevelId);
        $pengajuanId   = $hasil->id_pengajuan;
        $rumpun        = $hasil->studyProgram->rumpun ?? 'arsitektur';

        // ── Syarat Kunci 1: Skor ──
        $skorMin      = $config['skor_minimum'];
        $skorMemenuhi = $skor >= $skorMin;

        // ── Syarat Perlu: Pelampauan standar per kriteria ──
        $kriteriaRequired   = $config['kriteria_required'];
        $missingKriteria    = $hasil->getMissingKriteriaForUnggul($kriteriaRequired, $type);
        $pelampauanMemenuhi = empty($missingKriteria);

        // ── Syarat Kunci 2,3,4: LKPS (rasio + jabatan + lulusan) ──
        $syaratLkps   = $this->lkpsReader->cekSemuaSyarat($pengajuanId, $rumpun, $degreeLevelId);
        $lkpsMemenuhi = $syaratLkps['semua_memenuhi'];

        $keterangan   = [];
        $keterangan[] = $skorMemenuhi
            ? "☑ Skor {$skor} memenuhi syarat minimum Unggul (≥ {$skorMin})."
            : "☒ Skor {$skor} belum memenuhi syarat minimum Unggul (< {$skorMin}).";
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
            $syarat = $this->cekSyaratUnggul($hasil, $calc['skor_total'], 'al');
            $skorTotal = (int)$calc['skor_total'];
            $hasil->al_memenuhi_syarat_unggul = $syarat['memenuhi'];
            $resolved = $this->resolvePeringkatDanStatus($hasil, $skorTotal, 'al');

            $peringkat  = $resolved['peringkat'];
            $idStatusDraft = $resolved['status_id'];
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
                'al_memenuhi_syarat_unggul'        => $syarat['memenuhi'],

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

            $syarat = $this->cekSyaratUnggul($hasil, $skorALFinal, 'al');

            // ── Set atribut SEBELUM getPeringkatFromSkor() dipanggil ──
            // Model membaca $this->al_memenuhi_syarat_unggul, bukan parameter eksternal
            $hasil->al_memenuhi_syarat_unggul = $syarat['memenuhi'];
            $resolved = $this->resolvePeringkatDanStatus($hasil, $skorALFinal, 'al');

            $peringkat  = $resolved['peringkat'];
            $idStatusAL = $resolved['status_id'];

            $catatan = $syarat['keterangan'];

            if ($skorALFinal >= $syarat['skor_minimum'] && !$syarat['memenuhi']) {
                $catatan[] = "⚠️ Skor ≥ {$syarat['skor_minimum']} namun tidak semua syarat Unggul terpenuhi.";
                $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$peringkat}.";

                if (!$syarat['pelampauan_memenuhi'] && !empty($syarat['missing_kriteria'])) {
                    $catatan[] = "⚠️ Kriteria tanpa elemen "
                        . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ": "
                        . implode(', ', $syarat['missing_kriteria']) . ".";
                }
                if (!$syarat['p1_memenuhi']) {
                    $p1 = $syarat['syarat_p1'];
                    if (!$p1['rasio']['memenuhi'])   $catatan[] = "⚠️ Rasio DTPS:Mahasiswa melebihi batas.";
                    if (!$p1['jabatan']['memenuhi']) $catatan[] = "⚠️ Kompetensi dosen belum mencapai " . ($p1['jabatan']['persen_minimum'] ?? 50) . "%.";
                    if (!$p1['lulusan']['memenuhi']) $catatan[] = "⚠️ Capaian lulusan belum mencapai " . ($p1['lulusan']['persen_minimum'] ?? 10) . "%.";
                }
            } elseif ($syarat['memenuhi']) {
                $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
            }

            $hasil->update([
                'status'                     => 'final_hasil',
                'tanggal_finalisasi_al'      => now(),
                'finalized_al_by'            => $authId,
                'tanggal_finalisasi_hasil'   => now(),
                'finalized_hasil_by'         => $authId,
                'id_status_al'               => $idStatusAL,
                'id_status_hasil'            => $idStatusAL,
                'skor_hasil'                 => round($skorALFinal, 2),
                'peringkat_akreditasi_hasil' => $peringkat,
                'al_memenuhi_syarat_unggul'     => $syarat['memenuhi'], // ← persist ke DB
                'catatan_validasi'           => implode("\n", $catatan),
                'metadata'                   => array_merge(
                    (array)($hasil->metadata ?? []),
                    [
                        'syarat_unggul_check_al' => [
                            'checked_at'          => now()->toISOString(),
                            'checked_by'          => $authId,
                            'skor_al'             => $skorALFinal,
                            'skor_minimum'        => $syarat['skor_minimum'],
                            'skor_memenuhi'       => $syarat['skor_memenuhi'],
                            'pelampauan_memenuhi' => $syarat['pelampauan_memenuhi'],
                            'missing_kriteria'    => $syarat['missing_kriteria'],
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

    public function saveHasilALBanding(Asesmen $asesmen, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            if (!$hasil->isAkBandingFinalized() && !$hasil->isAkFinalized()) {
                throw new \Exception('AK banding harus difinalisasi terlebih dahulu.');
            }

            $calc = $this->calculateAL($asesmen, true);

            $detailSkorALBanding = [
                'kriteria' => $calc['detail_kriteria'],
                'elemen'   => $calc['detail_elemen'],
                'metadata' => [
                    'jumlah_elemen' => $calc['jumlah_elemen'],
                    'calculated_at' => now()->toISOString(),
                    'calculated_by' => $userId ?? auth()->id(),
                    'source'        => 'al_banding',
                ],
            ];
            $syarat = $this->cekSyaratUnggul($hasil, $calc['skor_total'], 'al_banding');
            $hasil->al_banding_memenuhi_syarat_unggul = $syarat['memenuhi'];
            $resolved = $this->resolvePeringkatDanStatus($hasil, (int)$calc['skor_total'], 'al_banding');

            $peringkat  = $resolved['peringkat'];
            $idStatusDraft = $resolved['status_id'];
            $hasil->update([
                'id_status_al_banding'             => $idStatusDraft,
                'skor_al_banding'                  => $calc['skor_total'],
                'skor_al_banding_tertimbang'       => $calc['skor_tertimbang'],
                'total_bobot_al_banding'           => $calc['total_bobot'],
                'pelampauan_standar_al_banding'    => $calc['pelampauan_standar'],
                'detail_skor_al_banding'           => $detailSkorALBanding,
                'peringkat_akreditasi_banding'     => null,
                'status'                           => 'draft_al_banding',
                'al_banding_memenuhi_syarat_unggul'        => $syarat['memenuhi'],
                'catatan_validasi_banding'         => implode("\n", $this->buildCatatanSyaratUnggul($syarat, $peringkat, (float)$calc['skor_total'])),
            ]);

            DB::commit();
            return $hasil->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveHasilALBanding failed', [
                'asesmen_id' => $asesmen->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function finalizeHasilALBanding(HasilAkreditasi $hasil, ?int $userId = null): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            if (is_null($hasil->skor_al_banding)) {
                throw new \Exception('Skor AL banding belum dihitung.');
            }

            $skorALBanding = (float)$hasil->skor_al_banding;
            $syarat        = $this->cekSyaratUnggul($hasil, $skorALBanding, 'al_banding');

            // ── Set atribut sebelum getPeringkatFromSkor ──
            $hasil->al_banding_memenuhi_syarat_unggul = $syarat['memenuhi'];
            $resolved = $this->resolvePeringkatDanStatus($hasil, $skorALBanding, 'al_banding');

            $peringkat  = $resolved['peringkat'];
            $idStatus = $resolved['status_id'];

            $hasil->update([
                'id_status_al_banding'          => $idStatus,
                'peringkat_akreditasi_banding'  => $peringkat,
                'al_banding_memenuhi_syarat_unggul'        => $syarat['memenuhi'],
                'catatan_validasi_banding'       => implode("\n", $this->buildCatatanSyaratUnggul($syarat, $peringkat, $skorALBanding)),
                'tanggal_finalisasi_al_banding' => now(),
                'finalized_al_banding_by'       => $userId ?? auth()->id(),
                'status'                        => 'final_al_banding',
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

    public function saveHasilPenetapan(HasilAkreditasi $hasil, ?int $userId = null, $isForBanding = false): HasilAkreditasi
    {
        DB::beginTransaction();
        try {
            $pakaiBanding = !is_null($hasil->skor_al_banding) && $hasil->isAlBandingFinalized();

            if (!$pakaiBanding && !$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi sebelum penetapan.');
            }
            if ($pakaiBanding && !$hasil->isAlBandingFinalized()) {
                throw new \Exception('AL banding harus difinalisasi sebelum penetapan.');
            }
            if ($hasil->isPenetapanFinalized()) {
                throw new \Exception('Penetapan sudah dikunci, tidak bisa dihitung ulang.');
            }

            $skorFinal       = $pakaiBanding ? (float)$hasil->skor_al_banding : (float)$hasil->skor_al;
            $totalBobotFinal = $pakaiBanding ? $hasil->total_bobot_al_banding  : $hasil->total_bobot_al;
            $detailSkorFinal = $pakaiBanding ? $hasil->detail_skor_al_banding  : $hasil->detail_skor_al;
            $pelampauanFinal = $pakaiBanding ? $hasil->pelampauan_standar_al_banding : $hasil->pelampauan_standar_al;

            $syarat = $this->cekSyaratUnggul($hasil, $skorFinal, 'final');

            // ── Set atribut sebelum getPeringkatFromSkor ──
            $hasil->final_memenuhi_syarat_unggul = $syarat['memenuhi'];
            $resolved = $this->resolvePeringkatDanStatus($hasil, $skorFinal, 'final');

            $peringkat  = $resolved['peringkat'];
            $idStatusFinal = $resolved['status_id'];

            $hasil->update([
                'status'                     => 'draft_penetapan',
                'skor_final'                 => round($skorFinal, 2),
                'skor_final_tertimbang'      => round($skorFinal, 2),
                'total_bobot_final'          => $totalBobotFinal,
                'detail_skor_final'          => $detailSkorFinal,
                'pelampauan_standar_final'   => $pelampauanFinal,
                'id_status_final'            => $idStatusFinal,
                'peringkat_akreditasi_final' => $peringkat,
                'final_memenuhi_syarat_unggul'     => $syarat['memenuhi'],
                'catatan_penetapan'          => implode("\n", $this->buildCatatanSyaratUnggul($syarat, $peringkat, $skorFinal)),
                'metadata'                   => array_merge(
                    (array)($hasil->metadata ?? []),
                    ['sumber_penetapan' => $pakaiBanding ? 'al_banding' : 'al']
                ),
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
            $pakaiBanding = (($hasil->metadata['sumber_penetapan'] ?? null) === 'al_banding');

            if ($pakaiBanding && !$hasil->isAlBandingFinalized()) {
                throw new \Exception('AL banding harus difinalisasi sebelum penetapan.');
            }
            if (!$pakaiBanding && !$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi sebelum penetapan.');
            }
            if ($hasil->isPenetapanFinalized()) {
                throw new \Exception('Penetapan sudah dikunci sebelumnya.');
            }
            if (is_null($hasil->skor_final)) {
                throw new \Exception('Skor final belum disiapkan. Panggil saveHasilPenetapan() terlebih dahulu.');
            }

            $authId = $userId ?? auth()->id();
            $syarat = $this->cekSyaratUnggul($hasil, (float)$hasil->skor_final, 'final');

            // ── Re-set atribut agar konsisten dengan hasil cek terbaru ──
            $hasil->final_memenuhi_syarat_unggul = $syarat['memenuhi'];

            // getPeringkatFromSkor membaca $hasil->final_memenuhi_syarat_unggul
            // Tidak perlu re-compute peringkat — sudah tersimpan di draft_penetapan.
            // Tapi lakukan re-check untuk validasi konsistensi:
            $skorFinal = (float)$hasil->skor_final;
            $resolved = $this->resolvePeringkatDanStatus($hasil, $skorFinal, 'final');

            $peringkatRecheck  = $resolved['peringkat'];
            $idStatusFinal = $resolved['status_id'];
            if ($peringkatRecheck !== $hasil->peringkat_akreditasi_final) {
                // Update jika ada perubahan (misal: data LKPS diupdate antara draft dan finalize)
                $hasil->peringkat_akreditasi_final = $peringkatRecheck;
                $hasil->id_status_final            = $idStatusFinal;
            }

            $catatan = $syarat['keterangan'];

            if (!$syarat['memenuhi'] && (float)$hasil->skor_final >= $syarat['skor_minimum']) {
                $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$hasil->peringkat_akreditasi_final}.";
                $p1 = $syarat['syarat_p1'];
                if (!$p1['rasio']['memenuhi'])   $catatan[] = "⚠️ " . $p1['rasio']['keterangan'];
                if (!$p1['jabatan']['memenuhi']) $catatan[] = "⚠️ " . $p1['jabatan']['keterangan'];
                if (!$p1['lulusan']['memenuhi']) $catatan[] = "⚠️ " . $p1['lulusan']['keterangan'];
                if (!$syarat['pelampauan_memenuhi'] && !empty($syarat['missing_kriteria'])) {
                    $catatan[] = "⚠️ Kriteria tanpa elemen "
                        . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ": "
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
                'final_memenuhi_syarat_unggul'       => $syarat['memenuhi'],
                'peringkat_akreditasi_final'   => $hasil->peringkat_akreditasi_final,
                'id_status_final'              => $hasil->id_status_final,
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

            $studyProgramUpdate = [
                'peringkat_akreditasi' => $hasil->peringkat_akreditasi_final,
                'tanggal_kedaluwarsa'  => now()->addYears(5),
                'status_kedaluwarsa'   => 'Aktif',
            ];

            if ($hasil->pengajuan->nomor_sertifikat) {
                $studyProgramUpdate['no_sk'] = $hasil->pengajuan->nomor_sertifikat;
            }

            $hasil->studyProgram->update($studyProgramUpdate);

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

    public function getValidationSummary(HasilAkreditasi $hasil, $type = 'al'): array
    {
        $skor             = (float)($hasil->skor_final ?? $hasil->skor_al ?? 0);
        $syarat           = $this->cekSyaratUnggul($hasil, $skor, $type);

        $pelampauanStandarCol = HasilAkreditasi::getPelampauanStandarCol($hasil, $type) ?? [];
        $kriteriaRequired = $this->syaratRepo->getKriteriaRequired();

        // Ambil nama kriteria sekaligus untuk ditampilkan di tabel
        $kriteriaNames = Kriteria::whereIn('kode_kriteria', $kriteriaRequired)
            ->pluck('nama_kriteria', 'kode_kriteria')
            ->toArray();

        $kriteriaStatus = [];
        foreach ($kriteriaRequired as $kode) {
            $hasPelampauan         = !empty($pelampauanStandarCol[$kode]);
            $kriteriaStatus[$kode] = [
                'has_pelampauan'       => $hasPelampauan,
                'jumlah_elemen_skor_4' => $hasPelampauan ? count($pelampauanStandarCol[$kode]) : 0,
                'elemen_list'          => $hasPelampauan ? $pelampauanStandarCol[$kode] : [],
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
     * Setelah peringkat efektif diketahui (post-validasi syarat perlu),
     * cari id status yang string-nya match persis dengan peringkat tersebut.
     * Ini menjamin id_status_* dan peringkat_akreditasi_* selalu konsisten.
     */
    private function resolvePeringkatDanStatus(HasilAkreditasi $hasil, float $skor, ?string $type = null): array
    {
        $peringkat = $hasil->getPeringkatFromSkor($skor, $type);

        $status = $this->getAllStatusAkreditasi()
            ->filter(function ($item) use ($skor, $peringkat) {
                return $item->skor_min <= $skor
                    && $item->skor_max >= $skor
                    && strcasecmp(trim($item->status), trim($peringkat)) === 0;
            })
            ->sortByDesc('skor_min')
            ->first();

        // Jika skor masuk rentang Unggul tapi syarat tidak terpenuhi, peringkat bisa
        // turun ke status non-Unggul yang rentang skornya tidak mencakup skor aktual.
        if (!$status) {
            $status = $this->getAllStatusAkreditasi()
                ->filter(fn($item) => strcasecmp(trim($item->status), trim($peringkat)) === 0)
                ->sortByDesc('skor_max')
                ->first();
        }

        // fallback terakhir kalau konfigurasi nama status tidak ditemukan
        if (!$status) {
            $status = $this->getAllStatusAkreditasi()
                ->filter(fn($item) => $item->skor_min <= $skor && $item->skor_max >= $skor)
                ->sortByDesc('skor_min')
                ->first();
        }

        return [
            'peringkat' => $peringkat,
            'status_id' => $status?->id,
            'status'    => $status,
        ];
    }

    private function buildCatatanSyaratUnggul(array $syarat, string $peringkat, float $skor): array
    {
        $catatan = $syarat['keterangan'];

        if (!$syarat['memenuhi'] && $skor >= $syarat['skor_minimum']) {
            $catatan[] = "⚠️ Peringkat diturunkan menjadi: {$peringkat}.";
            $p1 = $syarat['syarat_p1'];

            if (!$p1['rasio']['memenuhi']) {
                $catatan[] = "⚠️ " . $p1['rasio']['keterangan'];
            }
            if (!$p1['jabatan']['memenuhi']) {
                $catatan[] = "⚠️ " . $p1['jabatan']['keterangan'];
            }
            if (!$p1['lulusan']['memenuhi']) {
                $catatan[] = "⚠️ " . $p1['lulusan']['keterangan'];
            }
            if (!$syarat['pelampauan_memenuhi'] && !empty($syarat['missing_kriteria'])) {
                $catatan[] = "⚠️ Kriteria tanpa elemen "
                    . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ": "
                    . implode(', ', $syarat['missing_kriteria']) . ".";
            }
        } elseif ($syarat['memenuhi']) {
            $catatan[] = "☑ Semua syarat Terakreditasi Unggul terpenuhi.";
        }

        return $catatan;
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
     * query berulang di resolveStatusIdForDraft, resolveStatusIdBySkor, dll.
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
