<?php

namespace App\Services;

use App\Models\BorangDataExcel;
use App\Repositories\SyaratAkreditasiRepository;
use Illuminate\Support\Facades\Log;

class LkpsDataReaderService
{
    // Kolom 0-indexed sesuai posisi fisik di borang_data_excel.rows
    private const COL_NOMOR = 1;  // Kolom B Excel
    private const P1_COL_JABATAN_AKADEMIK = 8;  // Kolom I Excel
    private const E2_COL_TAHUN_AKADEMIK   = 1;  // Kolom B Excel
    private const E2_COL_MAHASISWA_AKTIF  = 8;  // Kolom I Excel

    public function __construct(
        private readonly SyaratAkreditasiRepository $syaratRepo
    ) {}

    // =========================================================
    // READ DATA LKPS
    // =========================================================

    /**
     * Ambil semua jabatan akademik DTPS dari P.1 Tabel 1.
     */
    public function getDtpsJabatan(int $pengajuanId): array
    {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->where('sheet_name', 'P.1')
            ->where('table_index', 1)
            ->first();

        if (!$record || empty($record->rows)) {
            return $this->emptyJabatanResult();
        }

        // Jabatan valid diambil dari DB (dinamis, bukan hardcode)
        $jabatanLektorKeAtas = array_map(
            'strtolower',
            $this->syaratRepo->getJabatanLektorKeAtas()
        );
        $lektorKeAtas = 0;
        $asisten      = 0;
        $jabatanList  = [];
        $detail       = [];

        foreach ($record->rows as $row) {
            // Skip baris summary (Jumlah, Rerata, dll) — bukan diawali nomor urut integer
            if (!isset($row[self::COL_NOMOR]) || !is_numeric($row[self::COL_NOMOR])) {
                continue;
            }

            $jabatan = strtolower(trim((string)($row[self::P1_COL_JABATAN_AKADEMIK] ?? '')));
            if ($jabatan === '') continue;

            $jabatanList[] = $jabatan;
            $detail[]      = [
                'no'      => $row[0],
                'nama'    => $row[1] ?? null,
                'nidn'    => $row[2] ?? null,
                'jabatan' => $row[self::P1_COL_JABATAN_AKADEMIK],
            ];

            if (in_array($jabatan, $jabatanLektorKeAtas, true)) {
                $lektorKeAtas++;
            } else {
                $asisten++;
            }
        }

        $total        = count($jabatanList);
        $persenLektor = $total > 0 ? round(($lektorKeAtas / $total) * 100, 2) : 0.0;
        return [
            'raw'            => $jabatanList,
            'lektor_ke_atas' => $lektorKeAtas,
            'asisten_ahli'   => $asisten,
            'total'          => $total,
            'persen_lektor'  => $persenLektor,
            'detail'         => $detail,
        ];
    }

    /**
     * Ambil jumlah mahasiswa aktif dari E.2 Tabel 1.
     *
     * Prioritas:
     *   1. Baris "Rekapitulasi" col H → total lintas angkatan (paling akurat)
     *   2. Baris "TS"            col H → fallback tahun terakhir
     *   3. null jika tidak ditemukan
     */
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
            $val   = $row[self::E2_COL_MAHASISWA_AKTIF] ?? null;

            if (!is_numeric($val)) continue;

            if (str_contains($label, 'rekapitulasi')) {
                $rekap = (int)$val;
            } elseif ($label === 'ts') {
                $tsAktif = (int)$val;
            }
        }
        return $rekap ?? $tsAktif;
    }

    // =========================================================
    // CEK SYARAT — JABATAN
    // =========================================================

    /**
     * Cek syarat (b): DTPS jabatan Lektor ke atas >= persen minimum dari konfigurasi DB.
     */
    public function cekSyaratJabatanLektor(int $pengajuanId): array
    {
        $data      = $this->getDtpsJabatan($pengajuanId);
        $minPersen = $this->syaratRepo->getPersenMinimumLektor();

        if ($data['total'] === 0) {
            return [
                'memenuhi'       => false,
                'persen_lektor'  => 0.0,
                'persen_minimum' => $minPersen,
                'lektor_ke_atas' => 0,
                'total_dtps'     => 0,
                'detail'         => [],
                'keterangan'     => 'Data P.1 (Dosen Tetap) tidak ditemukan di LKPS.',
            ];
        }

        $memenuhi = $data['persen_lektor'] >= $minPersen;

        return [
            'memenuhi'       => $memenuhi,
            'persen_lektor'  => $data['persen_lektor'],
            'persen_minimum' => $minPersen,
            'lektor_ke_atas' => $data['lektor_ke_atas'],
            'total_dtps'     => $data['total'],
            'detail'         => $data['detail'],
            'keterangan'     => $memenuhi
                ? "☑ {$data['lektor_ke_atas']} dari {$data['total']} DTPS "
                . "({$data['persen_lektor']}%) Lektor ke atas. Syarat ≥ {$minPersen}%."
                : "☒ Hanya {$data['lektor_ke_atas']} dari {$data['total']} DTPS "
                . "({$data['persen_lektor']}%) Lektor ke atas. Syarat ≥ {$minPersen}%.",
        ];
    }

    // =========================================================
    // CEK SYARAT — RASIO DTPS:MAHASISWA
    // =========================================================

    /**
     * Cek syarat (a): Rasio DTPS:Mahasiswa sesuai batas rumpun dari konfigurasi DB.
     */
    public function cekSyaratRasioDtps(int $pengajuanId, string $rumpun): array
    {
        $dtpsData        = $this->getDtpsJabatan($pengajuanId);
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
    // CEK SEMUA SYARAT P.1 SEKALIGUS
    // =========================================================

    public function cekSemuaSyaratP1(int $pengajuanId, string $rumpun): array
    {
        $rasio   = $this->cekSyaratRasioDtps($pengajuanId, $rumpun);
        $jabatan = $this->cekSyaratJabatanLektor($pengajuanId);

        return [
            'semua_memenuhi' => $rasio['memenuhi'] && $jabatan['memenuhi'],
            'rasio'          => $rasio,
            'jabatan'        => $jabatan,
        ];
    }

    // =========================================================
    // PRIVATE
    // =========================================================

    private function emptyJabatanResult(): array
    {
        return [
            'raw'            => [],
            'lektor_ke_atas' => 0,
            'asisten_ahli'   => 0,
            'total'          => 0,
            'persen_lektor'  => 0.0,
            'detail'         => [],
        ];
    }
}
