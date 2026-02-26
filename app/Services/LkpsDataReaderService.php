<?php

namespace App\Services;

use App\Models\BorangDataExcel;
use App\Models\DegreeLevel;
use App\Repositories\SyaratAkreditasiRepository;

class LkpsDataReaderService
{
    // =========================================================
    // MAPPING KOLOM — 0-indexed sesuai posisi fisik Excel
    // =========================================================

    // P.1.1 — Dosen Tetap Perguruan Tinggi
    private const COL_NOMOR                    = 1;  // Kolom B — No.
    private const P1_COL_NAMA_DOSEN            = 2;  // Kolom C — Nama Dosen
    private const P1_COL_NIDN                  = 3;  // Kolom D — NIDN/NIDK
    private const P1_COL_JABATAN_AKADEMIK      = 8;  // Kolom I — Jabatan Akademik
    private const P1_COL_SERTIFIKAT_PENDIDIK   = 9;  // Kolom J — Sertifikat Pendidik Profesional (sertifikasi mengajar)
    private const P1_COL_SERTIFIKAT_KOMPETENSI = 10; // Kolom K — Sertifikat Kompetensi/Profesi/Industri ← syarat Profesi

    // P.1.3 EWMP — identifikasi DTPS
    private const P13_COL_NAMA_DOSEN = 2; // Kolom C — Nama Dosen
    private const P13_COL_DTPS       = 3; // Kolom D — centang/isi jika DTPS

    // E.2.1 — Mahasiswa
    private const E2_COL_TAHUN_AKADEMIK  = 1; // Kolom B — Tahun Akademik (Angkatan)
    private const E2_COL_MAHASISWA_AKTIF = 8; // Kolom I — Jumlah Mahasiswa Aktif

    // R.3.1.c / R.3.1.e — Luaran Mahasiswa
    private const R3_COL_PUBLIKASI_ILMIAH = 6; // Kolom G — Publikasi Ilmiah
    private const R3_COL_KARYA_INOVASI    = 7; // Kolom H — Karya/Inovasi Karya

    /**
     * Mapping tipe_capaian → konfigurasi pembacaan sheet R.3.
     *
     * skala: 1=Internasional, 2=Nasional, 3=Wilayah, 4=Lokal, 5=Rekapitulasi semua skala
     * kolom: indeks kolom luaran yang dihitung
     */
    private const TIPE_CAPAIAN_CONFIG = [
        'inovasi_industri' => [
            'skala'  => 5,
            'kolom'  => self::R3_COL_KARYA_INOVASI,
            'sheets' => ['R.3.1.c', 'R.3.1.d', 'R.3.1.e'],
        ],
        'inovasi_profesi' => [
            'skala'  => 5,
            'kolom'  => self::R3_COL_KARYA_INOVASI,
            'sheets' => ['R.3.1.c', 'R.3.1.d', 'R.3.1.e'],
        ],
        'publikasi_sinta' => [
            'skala'  => 2,
            'kolom'  => self::R3_COL_PUBLIKASI_ILMIAH,
            'sheets' => ['R.3.1.c', 'R.3.1.d', 'R.3.1.e'],
        ],
        'publikasi_internasional' => [
            'skala'  => 1,
            'kolom'  => self::R3_COL_PUBLIKASI_ILMIAH,
            'sheets' => ['R.3.1.c', 'R.3.1.d', 'R.3.1.e'],
        ],
    ];

    // Kode degree level yang termasuk jenjang Profesi
    private const KODE_PROFESI = ['profesi', 'spesialis-1', 'spesialis-2'];

    private array $degreeLevelCache = [];
    public function __construct(
        private readonly SyaratAkreditasiRepository $syaratRepo
    ) {}

    // =========================================================
    // READ — DAFTAR NAMA DTPS dari P.1.3
    // =========================================================

    /**
     * Ambil daftar nama dosen yang ditandai sebagai DTPS di P.1.3 EWMP.
     * P.1.1 berisi SEMUA DTPT; filter ke DTPS diperlukan untuk syarat akreditasi.
     *
     * @return string[]  Nama dosen (lowercase, trimmed) yang adalah DTPS
     */
    public function getNamaDtps(int $pengajuanId): array
    {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->where('sheet_name', 'P.1')
            ->where('table_index', 1) // Tabel P.1.3 EWMP
            ->first();

        if (!$record || empty($record->rows)) {
            return [];
        }

        $namaDtps = [];

        foreach ($record->rows as $row) {
            if (!isset($row[self::COL_NOMOR]) || !is_numeric($row[self::COL_NOMOR])) {
                continue;
            }

            $dtpsFlag = $row[self::P13_COL_DTPS] ?? null;
            $nama     = strtolower(trim((string)($row[self::P13_COL_NAMA_DOSEN] ?? '')));

            $isDtps = !empty($dtpsFlag)
                && !in_array(strtolower((string)$dtpsFlag), ['tidak', '0', 'false', 'no', '-']);

            if ($isDtps && $nama !== '') {
                $namaDtps[] = $nama;
            }
        }

        return $namaDtps;
    }

    // =========================================================
    // READ — JABATAN DTPS dari P.1.1 (difilter ke DTPS)
    // =========================================================

    /**
     * Ambil jabatan akademik DTPS dari P.1.1, difilter berdasarkan
     * daftar DTPS dari P.1.3. Jabatan yang dihitung "valid" ditentukan
     * dari DB sesuai degree level.
     *
     * Fallback: jika P.1.3 kosong, semua baris P.1.1 dihitung (tanpa filter).
     */
    public function getDtpsJabatan(int $pengajuanId, int $degreeLevelId): array
    {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->where('sheet_name', 'P.1')
            ->where('table_index', 1)
            ->first();
        if (!$record || empty($record->rows)) {
            return $this->emptyJabatanResult();
        }

        $namaDtps   = $this->getNamaDtps($pengajuanId);
        $filterDtps = !empty($namaDtps);

        $jabatanValid = array_map(
            'strtolower',
            $this->syaratRepo->getJabatanValid($degreeLevelId)
        );

        $jumlahValid      = 0;
        $jumlahTidakValid = 0;
        $jabatanList      = [];
        $detail           = [];

        foreach ($record->rows as $row) {
            if (!isset($row[self::COL_NOMOR]) || !is_numeric($row[self::COL_NOMOR])) {
                continue;
            }

            $nama    = strtolower(trim((string)($row[self::P1_COL_NAMA_DOSEN] ?? '')));
            $jabatan = strtolower(trim((string)($row[self::P1_COL_JABATAN_AKADEMIK] ?? '')));

            if ($jabatan === '') continue;

            if ($filterDtps && !in_array($nama, $namaDtps, true)) {
                continue;
            }

            $isValid = in_array($jabatan, $jabatanValid, true);

            $jabatanList[] = $jabatan;
            $detail[]      = [
                'no'       => $row[self::COL_NOMOR],
                'nama'     => $row[self::P1_COL_NAMA_DOSEN] ?? null,
                'nidn'     => $row[self::P1_COL_NIDN] ?? null,
                'jabatan'  => $row[self::P1_COL_JABATAN_AKADEMIK],
                'is_valid' => $isValid,
                'is_dtps'  => true,
            ];

            $isValid ? $jumlahValid++ : $jumlahTidakValid++;
        }

        $total       = count($jabatanList);
        $persenValid = $total > 0 ? round(($jumlahValid / $total) * 100, 2) : 0.0;

        return [
            'raw'                => $jabatanList,
            'jumlah_valid'       => $jumlahValid,
            'jumlah_tidak_valid' => $jumlahTidakValid,
            'total'              => $total,
            'persen_valid'       => $persenValid,
            'filter_dtps_aktif'  => $filterDtps,
            'detail'             => $detail,
        ];
    }

    // =========================================================
    // READ — SERTIFIKAT KOMPETENSI/PROFESI dari P.1.1
    // =========================================================

    /**
     * Ambil data sertifikat kompetensi/profesi/industri (col K) dari P.1.1.
     * Khusus jenjang Profesi & Spesialis.
     * Col J (Sertifikat Pendidik Profesional) TIDAK dipakai — itu sertifikasi mengajar.
     */
    public function getDtpsSertifikatProfesi(int $pengajuanId): array
    {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->where('sheet_name', 'P.1')
            ->where('table_index', 1)
            ->first();

        if (!$record || empty($record->rows)) {
            return $this->emptySertifikatResult();
        }

        $namaDtps   = $this->getNamaDtps($pengajuanId);
        $filterDtps = !empty($namaDtps);

        $jumlahBersertifikat = 0;
        $total               = 0;
        $detail              = [];

        foreach ($record->rows as $row) {
            if (!isset($row[self::COL_NOMOR]) || !is_numeric($row[self::COL_NOMOR])) {
                continue;
            }

            $nama = strtolower(trim((string)($row[self::P1_COL_NAMA_DOSEN] ?? '')));

            if ($filterDtps && !in_array($nama, $namaDtps, true)) {
                continue;
            }

            $sertifikat    = trim((string)($row[self::P1_COL_SERTIFIKAT_KOMPETENSI] ?? ''));
            $hasSertifikat = !empty($sertifikat)
                && !in_array(strtolower($sertifikat), ['tidak', '-', '—', '0', 'false', 'no']);

            $total++;
            $detail[] = [
                'no'             => $row[self::COL_NOMOR],
                'nama'           => $row[self::P1_COL_NAMA_DOSEN] ?? null,
                'nidn'           => $row[self::P1_COL_NIDN] ?? null,
                'sertifikat'     => $sertifikat ?: null,
                'has_sertifikat' => $hasSertifikat,
            ];

            if ($hasSertifikat) $jumlahBersertifikat++;
        }
        $persenBersertifikat = $total > 0
            ? round(($jumlahBersertifikat / $total) * 100, 2)
            : 0.0;

        return [
            'jumlah_bersertifikat' => $jumlahBersertifikat,
            'jumlah_tidak'         => $total - $jumlahBersertifikat,
            'total'                => $total,
            'persen_bersertifikat' => $persenBersertifikat,
            'filter_dtps_aktif'    => $filterDtps,
            'detail'               => $detail,
        ];
    }

    // =========================================================
    // READ — MAHASISWA AKTIF dari E.2
    // =========================================================

    public function getMahasiswaAktif(int $pengajuanId): ?int
    {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->where('sheet_name', 'E.2')
            ->where('table_index', 1)
            ->first();

        if (!$record || empty($record->rows)) {
            return null;
        }

        $rekap   = null;
        $tsAktif = null;

        foreach ($record->rows as $row) {
            $label = strtolower(trim((string)($row[self::E2_COL_TAHUN_AKADEMIK] ?? '')));
            $raw   = $row[self::E2_COL_MAHASISWA_AKTIF] ?? null;

            $val = $this->resolveFormulaOrValue($raw, $row, $record->rows, self::E2_COL_MAHASISWA_AKTIF);

            if (!is_numeric($val)) continue;

            if (str_contains($label, 'rekapitulasi')) {
                $rekap = (int)$val;
            } elseif ($label === 'ts') {
                $tsAktif = (int)$val;
            }
        }

        return $rekap ?? $tsAktif;
    }

    private function resolveFormulaOrValue(mixed $val, array $row, array $allRows, int $colIndex): mixed
    {
        if (!is_string($val) || !str_starts_with($val, '=')) {
            return $val;
        }

        if (preg_match('/^=SUM\(([A-Z]+)\d+:([A-Z]+)\d+\)$/i', $val, $m)) {
            $colStart = $this->excelColToIndex($m[1]); // I → 8
            $colEnd   = $this->excelColToIndex($m[2]); // L → 11

            // Coba resolve dari row sendiri dulu
            $sum      = 0;
            $foundAny = false;

            for ($c = $colStart; $c <= $colEnd; $c++) {
                $v = $row[$c] ?? null;
                if (is_numeric($v)) {
                    $sum     += (int)$v;
                    $foundAny = true;
                }
            }

            if ($foundAny) {
                return $sum;
            }

            // Fallback: kolom colStart–colEnd tidak tersimpan di baris ini
            // → jumlahkan range kolom yang sama dari semua baris data angkatan
            $sum = 0;
            foreach ($allRows as $r) {
                $label = strtolower(trim((string)($r[self::E2_COL_TAHUN_AKADEMIK] ?? '')));

                // Hanya baris angkatan (TS, TS-1, dst.) — skip summary/rekapitulasi
                if (
                    $label === '' ||
                    str_contains($label, 'rekapitulasi') ||
                    str_contains($label, 'jumlah') ||
                    str_contains($label, 'keterangan')
                ) {
                    continue;
                }

                // Jumlahkan colStart s/d colEnd — sama persis dengan range formula
                for ($c = $colStart; $c <= $colEnd; $c++) {
                    $v = $r[$c] ?? null;
                    if (is_numeric($v)) {
                        $sum += (int)$v;
                    }
                }
            }

            return $sum;
        }

        return null;
    }

    private function excelColToIndex(string $col): int
    {
        $col   = strtoupper($col);
        $index = 0;
        $len   = strlen($col);

        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }

        return $index - 1; // 0-based
    }

    // =========================================================
    // READ — CAPAIAN LULUSAN dari R.3.1.c + R.3.1.e
    // =========================================================

    /**
     * Gabungkan data luaran mahasiswa dari R.3.1.c (kolaborasi DTPS+mahasiswa)
     * dan R.3.1.e (mahasiswa mandiri).
     *
     * tipe_capaian menentukan:
     *   - sub-tabel yang dibaca (skala 1–4 atau rekapitulasi .5)
     *   - kolom luaran (Publikasi Ilmiah atau Karya/Inovasi)
     */
    public function getDataCapaianLulusan(int $pengajuanId, string $tipeCapaian): array
    {
        $config      = self::TIPE_CAPAIAN_CONFIG[$tipeCapaian]
            ?? ['skala' => 5, 'kolom' => self::R3_COL_KARYA_INOVASI, 'sheets' => ['R.3.1.c', 'R.3.1.d', 'R.3.1.e']];
        $skalaIndex  = $config['skala'];
        $kolomLuaran = $config['kolom'];
        $sheets      = $config['sheets'];

        $totalLuaran     = 0;
        $jumlahMahasiswa = 0;
        $detailPerSheet  = [];

        foreach ($sheets as $sheetName) {
            $data = $this->parseR3Sheet($pengajuanId, $sheetName, $skalaIndex, $kolomLuaran);
            $totalLuaran += $data['jumlah_luaran'];

            // Denominator diambil dari sheet pertama yang punya data
            // (jumlah mahasiswa TA sama di semua sheet untuk pengajuan yang sama)
            if ($jumlahMahasiswa === 0 && $data['jumlah_mahasiswa'] > 0) {
                $jumlahMahasiswa = $data['jumlah_mahasiswa'];
            }

            $detailPerSheet[$sheetName] = $data;
        }

        if ($jumlahMahasiswa === 0) {
            return $this->emptyLulusanResult($tipeCapaian);
        }

        $persen = round(($totalLuaran / $jumlahMahasiswa) * 100, 2);
        // dd($detailPerSheet);
        return [
            'jumlah_luaran'     => $totalLuaran,
            'jumlah_mahasiswa'  => $jumlahMahasiswa,
            'persen'            => $persen,
            'tipe_capaian'      => $tipeCapaian,
            'skala_index'       => $skalaIndex,
            'detail_kolaborasi' => $detailPerSheet['R.3.1.c'] ?? ['jumlah_luaran' => 0, 'jumlah_mahasiswa' => 0, 'ratio' => 0.0],
            'detail_mandiri'    => $detailPerSheet['R.3.1.e'] ?? ['jumlah_luaran' => 0, 'jumlah_mahasiswa' => 0, 'ratio' => 0.0],
            'detail_per_sheet'  => $detailPerSheet, // ← lengkap untuk debugging/tampilan
        ];
    }

    /**
     * Parse satu sheet R.3.1.x untuk sub-tabel ke-N.
     *
     * @param  int     $skalaIndex  1–4 = skala spesifik, 5 = rekapitulasi
     * @param  int     $kolomLuaran Indeks kolom luaran yang dihitung
     */
    private function parseR3Sheet(
        int    $pengajuanId,
        string $sheetName,
        int    $skalaIndex,
        int    $kolomLuaran
    ): array {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->where('sheet_name', $sheetName)
            ->where('table_index', 1)
            ->first();

        $empty = ['jumlah_luaran' => 0, 'jumlah_mahasiswa' => 0, 'ratio' => 0.0];
        if (!$record || empty($record->rows)) return $empty;

        // Skala 5 = rekapitulasi: jumlahkan skala 1–4 secara eksplisit
        if ($skalaIndex === 5) {
            $totalLuaran     = 0;
            $maxMahasiswa    = 0;

            for ($s = 1; $s <= 4; $s++) {
                $sub = $this->parseR3SubTabel($record->rows, $s, $kolomLuaran);
                $totalLuaran  += $sub['jumlah_luaran'];
                $maxMahasiswa  = max($maxMahasiswa, $sub['jumlah_mahasiswa']);
            }

            return [
                'jumlah_luaran'    => $totalLuaran,
                'jumlah_mahasiswa' => $maxMahasiswa,
                'ratio'            => $maxMahasiswa > 0
                    ? round($totalLuaran / $maxMahasiswa, 4)
                    : 0.0,
            ];
        }

        return $this->parseR3SubTabel($record->rows, $skalaIndex, $kolomLuaran);
    }

    /**
     * Parse sub-tabel skala 1–4.
     *
     * Identifikasi: cari kemunculan ke-N dari baris
     * "Jumlah Mahasiswa yang Mengambil Tugas Akhir".
     * Baris sebelumnya adalah "Jumlah Penelitian..." (numerator).
     */
    private function parseR3SubTabel(array $rows, int $n, int $kolomLuaran): array
    {
        $mahasiswaRowCount = 0;
        $lastJumlahRow     = null;

        foreach ($rows as $row) {
            $label = strtolower(trim((string)($row[1] ?? '')));

            if (
                str_starts_with($label, 'jumlah penelitian') ||
                str_starts_with($label, 'jumlah dan luaran')
            ) {
                $lastJumlahRow = $row;
            }

            if (str_contains($label, 'jumlah mahasiswa yang mengambil tugas akhir')) {
                $mahasiswaRowCount++;

                if ($mahasiswaRowCount === $n) {
                    $jumlahMahasiswa = (int)($row[3] ?? 0);

                    // Resolve formula jika ada (=SUM(...))
                    $rawLuaran    = $lastJumlahRow[$kolomLuaran] ?? 0;
                    $jumlahLuaran = (int)$this->resolveFormulaOrValueFromRows(
                        $rawLuaran,
                        $lastJumlahRow ?? [],
                        $rows,
                        $kolomLuaran
                    );

                    return [
                        'jumlah_luaran'    => $jumlahLuaran,
                        'jumlah_mahasiswa' => $jumlahMahasiswa,
                        'ratio'            => $jumlahMahasiswa > 0
                            ? round($jumlahLuaran / $jumlahMahasiswa, 4)
                            : 0.0,
                    ];
                }

                $lastJumlahRow = null;
            }
        }

        return ['jumlah_luaran' => 0, 'jumlah_mahasiswa' => 0, 'ratio' => 0.0];
    }

    private function resolveFormulaOrValueFromRows(
        mixed  $val,
        array  $row,
        array  $allRows,
        int    $colIndex
    ): mixed {
        if (!is_string($val) || !str_starts_with($val, '=')) {
            return $val;
        }

        if (preg_match('/^=SUM\(([A-Z]+)\d+:([A-Z]+)\d+\)$/i', $val, $m)) {
            $colStart = $this->excelColToIndex($m[1]);
            $colEnd   = $this->excelColToIndex($m[2]);

            // Coba resolve dari row sendiri
            $sum      = 0;
            $foundAny = false;
            for ($c = $colStart; $c <= $colEnd; $c++) {
                $v = $row[$c] ?? null;
                if (is_numeric($v)) {
                    $sum     += (int)$v;
                    $foundAny = true;
                }
            }

            if ($foundAny) return $sum;

            // Fallback: jumlahkan kolom range dari baris-baris data (baris bernomor urut)
            $sum = 0;
            dd($allRows);
            foreach ($allRows as $r) {
                // Hanya baris data: kolom pertama bernomor integer
                if (!isset($r[1]) || !is_numeric($r[1])) continue;

                for ($c = $colStart; $c <= $colEnd; $c++) {
                    $v = $r[$c] ?? null;
                    if (is_numeric($v)) {
                        $sum += (int)$v;
                    }
                }
            }

            return $sum;
        }

        return null;
    }

    // =========================================================
    // CEK SYARAT — RASIO DTPS:MAHASISWA
    // =========================================================

    public function cekSyaratRasioDtps(int $pengajuanId, string $rumpun, int $degreeLevelId): array
    {
        $dtpsData        = $this->getDtpsJabatan($pengajuanId, $degreeLevelId);
        $jumlahDtps      = $dtpsData['total'];
        $jumlahMahasiswa = $this->getMahasiswaAktif($pengajuanId);
        $rasioMax        = $this->syaratRepo->getRasioMaksForRumpun($rumpun);

        if ($jumlahDtps === 0 || $jumlahMahasiswa === null) {
            return [
                'memenuhi'         => false,
                'rasio'            => null,
                'rasio_max'        => $rasioMax,
                'jumlah_dtps'      => $jumlahDtps,
                'jumlah_mahasiswa' => $jumlahMahasiswa,
                'rumpun'           => $rumpun,
                'keterangan'       => 'Data DTPS atau mahasiswa tidak tersedia dari LKPS.',
            ];
        }

        $rasioAktual = round($jumlahMahasiswa / $jumlahDtps, 2);
        $memenuhi    = $rasioAktual <= $rasioMax;

        return [
            'memenuhi'         => $memenuhi,
            'rasio'            => $rasioAktual,
            'rasio_max'        => $rasioMax,
            'jumlah_dtps'      => $jumlahDtps,
            'jumlah_mahasiswa' => $jumlahMahasiswa,
            'rumpun'           => $rumpun,
            'keterangan'       => $memenuhi
                ? "☑ Rasio 1:{$rasioAktual} ≤ batas 1:{$rasioMax} untuk rumpun {$rumpun}."
                : "☒ Rasio 1:{$rasioAktual} melebihi batas 1:{$rasioMax} untuk rumpun {$rumpun}.",
        ];
    }

    // =========================================================
    // CEK SYARAT — KOMPETENSI DOSEN
    // =========================================================

    /**
     * Router: profesi → cek sertifikat, lainnya → cek jabatan.
     */
    public function cekSyaratKompetensiDosen(int $pengajuanId, int $degreeLevelId): array
    {
        return $this->isProfesi($degreeLevelId)
            ? $this->cekSyaratSertifikatProfesi($pengajuanId, $degreeLevelId)
            : $this->cekSyaratJabatan($pengajuanId, $degreeLevelId);
    }

    public function cekSyaratJabatan(int $pengajuanId, int $degreeLevelId): array
    {
        $data      = $this->getDtpsJabatan($pengajuanId, $degreeLevelId);
        $minPersen = $this->syaratRepo->getPersenMinimumJabatan($degreeLevelId);
        $label     = $this->getLabelJabatan($degreeLevelId);

        if ($data['total'] === 0) {
            return $this->emptyKompetensiResult(
                'jabatan',
                $minPersen,
                'Data P.1 (DTPS) tidak ditemukan di LKPS.'
            );
        }

        $memenuhi = $data['persen_valid'] >= $minPersen;

        return [
            'memenuhi'          => $memenuhi,
            'tipe'              => 'jabatan',
            'persen_valid'      => $data['persen_valid'],
            'persen_minimum'    => $minPersen,
            'jumlah_valid'      => $data['jumlah_valid'],
            'total_dtps'        => $data['total'],
            'label_jabatan'     => $label,
            'filter_dtps_aktif' => $data['filter_dtps_aktif'],
            'detail'            => $data['detail'],
            'keterangan'        => $memenuhi
                ? "☑ {$data['jumlah_valid']} dari {$data['total']} DTPS "
                . "({$data['persen_valid']}%) {$label}. Syarat ≥ {$minPersen}%."
                : "☒ Hanya {$data['jumlah_valid']} dari {$data['total']} DTPS "
                . "({$data['persen_valid']}%) {$label}. Syarat ≥ {$minPersen}%.",
        ];
    }

    public function cekSyaratSertifikatProfesi(int $pengajuanId, int $degreeLevelId): array
    {
        $data      = $this->getDtpsSertifikatProfesi($pengajuanId);
        $minPersen = $this->syaratRepo->getPersenMinimumSertifikatProfesi($degreeLevelId);

        if ($data['total'] === 0) {
            return $this->emptyKompetensiResult(
                'sertifikat_profesi',
                $minPersen,
                'Data P.1 (DTPS) tidak ditemukan di LKPS.'
            );
        }

        $memenuhi = $data['persen_bersertifikat'] >= $minPersen;

        return [
            'memenuhi'          => $memenuhi,
            'tipe'              => 'sertifikat_profesi',
            'persen_valid'      => $data['persen_bersertifikat'],
            'persen_minimum'    => $minPersen,
            'jumlah_valid'      => $data['jumlah_bersertifikat'],
            'total_dtps'        => $data['total'],
            'label_jabatan'     => 'bersertifikat kompetensi/profesi/industri',
            'filter_dtps_aktif' => $data['filter_dtps_aktif'],
            'detail'            => $data['detail'],
            'keterangan'        => $memenuhi
                ? "☑ {$data['jumlah_bersertifikat']} dari {$data['total']} DTPS "
                . "({$data['persen_bersertifikat']}%) bersertifikat kompetensi/profesi. Syarat ≥ {$minPersen}%."
                : "☒ Hanya {$data['jumlah_bersertifikat']} dari {$data['total']} DTPS "
                . "({$data['persen_bersertifikat']}%) bersertifikat kompetensi/profesi. Syarat ≥ {$minPersen}%.",
        ];
    }

    // =========================================================
    // CEK SYARAT — CAPAIAN LULUSAN
    // =========================================================

    public function cekSyaratCapaianLulusan(int $pengajuanId, int $degreeLevelId): array
    {
        $minPersen    = $this->syaratRepo->getPersenMinimumLulusan($degreeLevelId);
        $tipeCapaian  = $this->syaratRepo->getTipeCapaianLulusan($degreeLevelId);
        $data         = $this->getDataCapaianLulusan($pengajuanId, $tipeCapaian);
        $labelCapaian = $this->getLabelCapaianLulusan($tipeCapaian);

        if ($data['jumlah_mahasiswa'] === 0) {
            return $this->emptyKompetensiResult(
                'lulusan',
                $minPersen,
                'Data R.3.1 (Luaran Mahasiswa) tidak ditemukan di LKPS.'
            );
        }

        $memenuhi = $data['persen'] >= $minPersen;

        return [
            'memenuhi'         => $memenuhi,
            'tipe'             => 'lulusan',
            'tipe_capaian'     => $tipeCapaian,
            'persen'           => $data['persen'],
            'persen_minimum'   => $minPersen,
            'jumlah_luaran'    => $data['jumlah_luaran'],
            'jumlah_mahasiswa' => $data['jumlah_mahasiswa'],
            'label_capaian'    => $labelCapaian,
            'detail'           => [
                'kolaborasi' => $data['detail_kolaborasi'],
                'mandiri'    => $data['detail_mandiri'],
            ],
            'detail_per_sheet' => $data['detail_per_sheet'] ?? [], // ← tambah ini
            'keterangan'       => $memenuhi
                ? "☑ {$data['jumlah_luaran']} dari {$data['jumlah_mahasiswa']} mahasiswa "
                . "({$data['persen']}%) {$labelCapaian}. Syarat ≥ {$minPersen}%."
                : "☒ Hanya {$data['jumlah_luaran']} dari {$data['jumlah_mahasiswa']} mahasiswa "
                . "({$data['persen']}%) {$labelCapaian}. Syarat ≥ {$minPersen}%.",
        ];
    }

    // =========================================================
    // CEK SEMUA SYARAT
    // =========================================================

    /**
     * Entry point utama: cek semua syarat LKPS sekaligus.
     * Mencakup: rasio DTPS, kompetensi dosen, dan capaian lulusan.
     */
    public function cekSemuaSyarat(int $pengajuanId, string $rumpun, int $degreeLevelId): array
    {
        $rasio      = $this->cekSyaratRasioDtps($pengajuanId, $rumpun, $degreeLevelId);
        $kompetensi = $this->cekSyaratKompetensiDosen($pengajuanId, $degreeLevelId);
        $lulusan    = $this->cekSyaratCapaianLulusan($pengajuanId, $degreeLevelId);

        return [
            'semua_memenuhi' => $rasio['memenuhi'] && $kompetensi['memenuhi'] && $lulusan['memenuhi'],
            'rasio'          => $rasio,
            'jabatan'        => $kompetensi, // key dipertahankan agar backward compatible
            'lulusan'        => $lulusan,
        ];
    }

    /**
     * Alias backward-compatible untuk cekSemuaSyarat().
     */
    public function cekSemuaSyaratP1(int $pengajuanId, string $rumpun, int $degreeLevelId): array
    {
        return $this->cekSemuaSyarat($pengajuanId, $rumpun, $degreeLevelId);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function isProfesi(int $degreeLevelId): bool
    {
        if (!isset($this->degreeLevelCache[$degreeLevelId])) {
            $this->degreeLevelCache[$degreeLevelId] = DegreeLevel::find($degreeLevelId);
        }
        $code = $this->degreeLevelCache[$degreeLevelId]?->code ?? '';
        return in_array($code, self::KODE_PROFESI, true);
    }

    private function getLabelJabatan(int $degreeLevelId): string
    {
        $jabatan         = $this->syaratRepo->getJabatanValid($degreeLevelId);
        $hasLektor       = in_array('lektor', $jabatan);
        $hasLektorKepala = in_array('lektor kepala', $jabatan);
        $hasGB           = in_array('guru besar', $jabatan);

        return match (true) {
            $hasLektor && $hasLektorKepala && $hasGB  => 'Lektor ke atas',
            !$hasLektor && $hasLektorKepala && $hasGB => 'Lektor Kepala ke atas',
            !$hasLektor && $hasLektorKepala           => 'Lektor Kepala ke atas',
            default                                    => 'jabatan valid',
        };
    }

    private function getLabelCapaianLulusan(string $tipeCapaian): string
    {
        return match ($tipeCapaian) {
            'inovasi_industri'        => 'memiliki karya inovasi yang dipakai industri atau masyarakat',
            'publikasi_sinta'         => 'memiliki publikasi terakreditasi SINTA atau karya inovatif setara',
            'publikasi_internasional' => 'memiliki publikasi internasional bereputasi atau karya inovatif setara',
            'inovasi_profesi'         => 'memiliki karya inovasi yang divalidasi organisasi profesi atau lulus ujian kompetensi',
            default                   => 'memenuhi capaian lulusan',
        };
    }

    private function emptyJabatanResult(): array
    {
        return [
            'raw'                => [],
            'jumlah_valid'       => 0,
            'jumlah_tidak_valid' => 0,
            'total'              => 0,
            'persen_valid'       => 0.0,
            'filter_dtps_aktif'  => false,
            'detail'             => [],
        ];
    }

    private function emptySertifikatResult(): array
    {
        return [
            'jumlah_bersertifikat' => 0,
            'jumlah_tidak'         => 0,
            'total'                => 0,
            'persen_bersertifikat' => 0.0,
            'filter_dtps_aktif'    => false,
            'detail'               => [],
        ];
    }

    private function emptyLulusanResult(string $tipeCapaian): array
    {
        return [
            'jumlah_luaran'     => 0,
            'jumlah_mahasiswa'  => 0,
            'persen'            => 0.0,
            'tipe_capaian'      => $tipeCapaian,
            'skala_index'       => 0,
            'detail_kolaborasi' => ['jumlah_luaran' => 0, 'jumlah_mahasiswa' => 0, 'ratio' => 0.0],
            'detail_mandiri'    => ['jumlah_luaran' => 0, 'jumlah_mahasiswa' => 0, 'ratio' => 0.0],
            'detail_per_sheet'  => [],
        ];
    }

    private function emptyKompetensiResult(string $tipe, float $minPersen, string $pesan): array
    {
        return [
            'memenuhi'          => false,
            'tipe'              => $tipe,
            'persen_valid'      => 0.0,
            'persen_minimum'    => $minPersen,
            'jumlah_valid'      => 0,
            'total_dtps'        => 0,
            'label_jabatan'     => '-',
            'filter_dtps_aktif' => false,
            'detail'            => [],
            'keterangan'        => $pesan,
        ];
    }
}
