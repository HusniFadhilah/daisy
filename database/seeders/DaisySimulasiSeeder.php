<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DaisySimulasiSeeder
 *
 * Menduplikasi data pengajuan id=1 sebanyak TARGET kali dengan ID baru.
 * Semua tabel relasi ikut terduplikasi dengan FK yang diupdate.
 *
 * Jalankan:
 *   php artisan db:seed --class=DaisySimulasiSeeder
 *
 * Rollback:
 *   php artisan daisy:rollback-simulasi
 */
class DaisySimulasiSeeder extends Seeder
{
    const TARGET     = 90000;  // jumlah pengajuan yang digenerate
    const SOURCE_ID  = 1;    // id pengajuan yang jadi template
    const CHUNK_SIZE = 50;   // flush ke DB tiap N pengajuan

    // ── Enum valid sesuai schema migration ────────────────────────────────
    private array $jenisAkreditasi = [
        'menuju_unggul',
        'baru',
        'terakreditasi',
        'perpanjangan',
    ];

    private array $tahunVariasi = ['2021', '2022', '2023', '2024', '2025', '2026'];

    public function run(): void
    {
        $this->command->info('Memuat data sumber (row pertama)...');

        // ── Ambil data sumber: row pertama dari pengajuan_akreditasi ──────
        $srcPA = DB::table('pengajuan_akreditasi')->orderBy('id')->first();
        if (! $srcPA) {
            $this->command->error('Tabel pengajuan_akreditasi kosong!');
            return;
        }

        $srcPid = $srcPA->id;
        $this->command->info("Menggunakan pengajuan id={$srcPid} sebagai template.");

        $srcAsm   = DB::table('asesmens')->where('id_pengajuan', $srcPid)->first();
        $srcAsmId = $srcAsm?->id;

        $srcAK     = $srcAsmId ? DB::table('asesmen_kecukupan')->where('id_asesmen', $srcAsmId)->get()    : collect();
        $srcAL     = $srcAsmId ? DB::table('asesmen_lapangan')->where('id_asesmen', $srcAsmId)->get()     : collect();
        $srcAUR    = $srcAsmId ? DB::table('asesmen_user_roles')->where('id_asesmen', $srcAsmId)->get()   : collect();
        $srcADoc   = $srcAsmId ? DB::table('asesmen_documents')->where('id_asesmen', $srcAsmId)->get()    : collect();
        $srcLog    = DB::table('pengajuan_status_log')->where('id_pengajuan', $srcPid)->get();
        $srcBayar  = DB::table('pengajuan_pembayaran')->where('id_pengajuan', $srcPid)->get();
        $srcDok    = DB::table('pengajuan_dokumen')->where('id_pengajuan', $srcPid)->get();
        $srcBI     = DB::table('borang_imports')->where('id_pengajuan', $srcPid)->get();
        $srcBD     = DB::table('borang_data')->where('id_pengajuan', $srcPid)->get();
        $srcBV     = DB::table('borang_validations')->where('id_pengajuan', $srcPid)->get();
        $srcLHA    = $srcAsmId ? DB::table('lha_asesors')->where('id_asesmen', $srcAsmId)->get()         : collect();
        $srcHasil  = DB::table('hasil_akreditasi')->where('id_pengajuan', $srcPid)->get();
        $srcPeakAK = $srcAsmId ? DB::table('penilaian_elemen_ak')->where('id_asesmen', $srcAsmId)->get() : collect();
        $srcPeakAL = $srcAsmId ? DB::table('penilaian_elemen_al')->where('id_asesmen', $srcAsmId)->get() : collect();
        $srcPingat = DB::table('pengingat_akreditasi')->where('id_pengajuan', $srcPid)->get();

        // ── Study programs: butuh id_category untuk hasil_akreditasi ──────
        $studyPrograms = DB::table('study_programs')
            ->whereNotNull('id_category')
            ->get(['id', 'id_category'])
            ->toArray();

        if (empty($studyPrograms)) {
            $studyPrograms = DB::table('study_programs')
                ->get(['id', 'id_category'])
                ->toArray();
        }
        shuffle($studyPrograms);

        // ── Hitung ID awal per tabel ───────────────────────────────────────
        $tables = [
            'pengajuan_akreditasi',
            'asesmens',
            'asesmen_kecukupan',
            'asesmen_lapangan',
            'asesmen_user_roles',
            'asesmen_documents',
            'pengajuan_status_log',
            'pengajuan_pembayaran',
            'pengajuan_dokumen',
            'borang_imports',
            'borang_data',
            'borang_validations',
            'lha_asesors',
            'hasil_akreditasi',
            'penilaian_elemen_ak',
            'penilaian_elemen_al',
            'pengingat_akreditasi',
        ];
        $nextId = [];
        foreach ($tables as $tbl) {
            $nextId[$tbl] = (DB::table($tbl)->max('id') ?? 0) + 1;
        }

        $this->command->info('Mulai generate ' . self::TARGET . ' duplikat...');

        $batches = array_fill_keys($tables, []);
        $bar     = $this->command->getOutput()->createProgressBar(self::TARGET);
        $bar->start();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        for ($i = 0; $i < self::TARGET; $i++) {
            $newPid  = $nextId['pengajuan_akreditasi']++;
            $sp      = $studyPrograms[$i % count($studyPrograms)];
            $spId    = $sp->id;
            $catId   = $sp->id_category;
            $jenis   = $this->jenisAkreditasi[$i % count($this->jenisAkreditasi)];
            $jenisC  = match ($jenis) {
                'menuju_unggul' => 'UNGGUL',
                'baru'          => 'BARU',
                'terakreditasi' => 'REAK',
                'perpanjangan'  => 'PERP',
                default         => 'SIM',
            };
            $tahun = $this->tahunVariasi[$i % count($this->tahunVariasi)];
            $pad   = str_pad($newPid, 4, '0', STR_PAD_LEFT);

            // ── pengajuan_akreditasi ───────────────────────────────────────
            $rowPA                     = (array) $srcPA;
            $rowPA['id']               = $newPid;
            $rowPA['nomor_pengajuan']  = "LAMDEPILAR/{$tahun}/{$jenisC}/{$pad}";
            $rowPA['nomor_permohonan'] = str_pad($newPid, 2, '0', STR_PAD_LEFT) . "/03/SIM/{$jenisC}";
            $rowPA['id_program_studi'] = $spId;
            $rowPA['jenis_akreditasi'] = $jenis;
            $rowPA['tahun_akreditasi'] = $tahun;
            $rowPA['pemohon_email']    = "prodi{$spId}@sim.lamdepilar.or.id";
            $rowPA['nomor_sertifikat'] = $rowPA['nomor_sertifikat']
                ? "SERT/{$tahun}/{$jenisC}/{$pad}" : null;
            $rowPA['is_example']       = 1;
            $batches['pengajuan_akreditasi'][] = $rowPA;

            // ── asesmens ──────────────────────────────────────────────────
            $newAsmId = null;
            if ($srcAsm) {
                $newAsmId                    = $nextId['asesmens']++;
                $rowAsm                      = (array) $srcAsm;
                $rowAsm['id']                = $newAsmId;
                $rowAsm['id_pengajuan']      = $newPid;
                $rowAsm['id_study_program']  = $spId;
                $rowAsm['code']              = "ASM/{$tahun}/{$jenisC}/{$pad}"; // unique
                $rowAsm['name']              = "Asesmen {$jenisC} Prodi {$spId} {$tahun}";
                $rowAsm['is_example']        = 1;
                $batches['asesmens'][]       = $rowAsm;
            }

            // ── asesmen_kecukupan ─────────────────────────────────────────
            $akMap = [];
            foreach ($srcAK as $src) {
                $newId           = $nextId['asesmen_kecukupan']++;
                $akMap[$src->id] = $newId;
                $row             = (array) $src;
                $row['id']       = $newId;
                $row['id_asesmen'] = $newAsmId;
                $row['code']     = "AK/{$tahun}/{$jenisC}/{$pad}"; // unique
                $row['is_example'] = 1;
                $batches['asesmen_kecukupan'][] = $row;
            }

            // ── asesmen_lapangan ──────────────────────────────────────────
            $alMap = [];
            foreach ($srcAL as $src) {
                $newId           = $nextId['asesmen_lapangan']++;
                $alMap[$src->id] = $newId;
                $row             = (array) $src;
                $row['id']       = $newId;
                $row['id_asesmen'] = $newAsmId;
                $row['code']     = "AL/{$tahun}/{$jenisC}/{$pad}"; // unique
                $row['id_asesmen_kecukupan'] = isset($src->id_asesmen_kecukupan)
                    ? ($akMap[$src->id_asesmen_kecukupan] ?? null) : null;
                $row['is_example'] = 1;
                $batches['asesmen_lapangan'][] = $row;
            }

            // ── asesmen_user_roles ────────────────────────────────────────
            // unique: (id_asesmen, id_user, jenis_asesmen, id_role)
            // status_pekerjaan enum: not_started|in_progress|submitted|validated|revision_required|approved
            $aurMap = [];
            $validPekerjaan = ['not_started', 'in_progress', 'submitted', 'validated', 'revision_required', 'approved'];
            foreach ($srcAUR as $src) {
                $newId            = $nextId['asesmen_user_roles']++;
                $aurMap[$src->id] = $newId;
                $row              = (array) $src;
                $row['id']        = $newId;
                $row['id_asesmen'] = $newAsmId;
                $row['id_asesmen_kecukupan'] = isset($src->id_asesmen_kecukupan)
                    ? ($akMap[$src->id_asesmen_kecukupan] ?? null) : null;
                $row['id_asesmen_lapangan'] = isset($src->id_asesmen_lapangan)
                    ? ($alMap[$src->id_asesmen_lapangan] ?? null) : null;
                if (! in_array($row['status_pekerjaan'] ?? '', $validPekerjaan)) {
                    $row['status_pekerjaan'] = 'not_started';
                }
                $row['is_example'] = 1;
                $batches['asesmen_user_roles'][] = $row;
            }

            // ── asesmen_documents ─────────────────────────────────────────
            foreach ($srcADoc as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['asesmen_documents']++;
                $row['id_asesmen'] = $newAsmId;
                $row['path']       = $this->swapPath($src->path ?? '', 1, $newPid, $srcAsmId, $newAsmId);
                $batches['asesmen_documents'][] = $row;
            }

            // ── pengajuan_status_log ──────────────────────────────────────
            foreach ($srcLog as $src) {
                $row                 = (array) $src;
                $row['id']           = $nextId['pengajuan_status_log']++;
                $row['id_pengajuan'] = $newPid;
                $batches['pengajuan_status_log'][] = $row;
            }

            // ── pengajuan_pembayaran ──────────────────────────────────────
            // status_pembayaran enum: menunggu_pembayaran|menunggu_verifikasi|upload_ulang|ditolak|terverifikasi
            // nomor_invoice: unique
            $validStatusBayar = ['menunggu_pembayaran', 'menunggu_verifikasi', 'upload_ulang', 'ditolak', 'terverifikasi'];
            $bayarIdx = 0;
            foreach ($srcBayar as $src) {
                $suffix              = $bayarIdx === 0 ? 'AKR' : 'BDG';
                $row                 = (array) $src;
                $row['id']           = $nextId['pengajuan_pembayaran']++;
                $row['id_pengajuan'] = $newPid;
                $row['nomor_invoice'] = "INV/{$tahun}/{$pad}-{$suffix}";
                if (! in_array($row['status_pembayaran'] ?? '', $validStatusBayar)) {
                    $row['status_pembayaran'] = 'terverifikasi';
                }
                if ($row['bukti_path']) {
                    $row['bukti_path'] = $this->swapPath($row['bukti_path'], 1, $newPid);
                }
                $batches['pengajuan_pembayaran'][] = $row;
                $bayarIdx++;
            }

            // ── pengajuan_dokumen ─────────────────────────────────────────
            $dokMap = [];
            foreach ($srcDok as $src) {
                $newId            = $nextId['pengajuan_dokumen']++;
                $dokMap[$src->id] = $newId;
                $row              = (array) $src;
                $row['id']        = $newId;
                $row['id_pengajuan'] = $newPid;
                $row['path_file'] = $this->swapPath($src->path_file ?? '', 1, $newPid);
                $batches['pengajuan_dokumen'][] = $row;
            }

            // ── borang_imports ────────────────────────────────────────────
            $biMap = [];
            foreach ($srcBI as $src) {
                $newId            = $nextId['borang_imports']++;
                $biMap[$src->id]  = $newId;
                $row              = (array) $src;
                $row['id']        = $newId;
                $row['id_pengajuan'] = $newPid;
                $row['stored_path']  = $this->swapPath($src->stored_path ?? '', 1, $newPid);
                $row['id_dokumen']   = isset($src->id_dokumen)
                    ? ($dokMap[$src->id_dokumen] ?? null) : null;
                $batches['borang_imports'][] = $row;
            }

            // ── borang_data ───────────────────────────────────────────────
            // unique: (id_pengajuan, dataset_id) — aman karena id_pengajuan berbeda
            foreach ($srcBD as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['borang_data']++;
                $row['id_pengajuan'] = $newPid;
                $row['id_borang_import'] = isset($src->id_borang_import)
                    ? ($biMap[$src->id_borang_import] ?? null) : null;
                $batches['borang_data'][] = $row;
            }

            // ── borang_validations ────────────────────────────────────────
            // id_assignment → map ke asesmen_user_roles baru
            foreach ($srcBV as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['borang_validations']++;
                $row['id_pengajuan'] = $newPid;
                $row['id_assignment'] = isset($src->id_assignment)
                    ? ($aurMap[$src->id_assignment] ?? null) : null;
                $batches['borang_validations'][] = $row;
            }

            // ── lha_asesors ───────────────────────────────────────────────
            foreach ($srcLHA as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['lha_asesors']++;
                $row['id_asesmen'] = $newAsmId;
                $batches['lha_asesors'][] = $row;
            }

            // ── hasil_akreditasi ──────────────────────────────────────────
            // Butuh id_category (ambil dari study_program yang dipilih)
            foreach ($srcHasil as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['hasil_akreditasi']++;
                $row['id_pengajuan']     = $newPid;
                $row['id_asesmen']       = $newAsmId;
                $row['id_study_program'] = $spId;
                $row['id_category']      = $catId; // ← wajib, ambil dari study_program
                $batches['hasil_akreditasi'][] = $row;
            }

            // ── penilaian_elemen_ak ───────────────────────────────────────
            // unique: (id_asesmen, id_asesor, id_elemen) — aman karena id_asesmen baru
            foreach ($srcPeakAK as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['penilaian_elemen_ak']++;
                $row['id_asesmen'] = $newAsmId;
                $batches['penilaian_elemen_ak'][] = $row;
            }

            // ── penilaian_elemen_al ───────────────────────────────────────
            foreach ($srcPeakAL as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['penilaian_elemen_al']++;
                $row['id_asesmen'] = $newAsmId;
                $batches['penilaian_elemen_al'][] = $row;
            }

            // ── pengingat_akreditasi ──────────────────────────────────────
            foreach ($srcPingat as $src) {
                $row               = (array) $src;
                $row['id']         = $nextId['pengingat_akreditasi']++;
                $row['id_pengajuan']     = $newPid;
                $row['id_program_studi'] = $spId;
                $batches['pengingat_akreditasi'][] = $row;
            }

            // ── Flush per CHUNK_SIZE ──────────────────────────────────────
            if (($i + 1) % self::CHUNK_SIZE === 0) {
                $this->flush($batches, $tables);
                $batches = array_fill_keys($tables, []);
            }

            $bar->advance();
        }

        // Flush sisa
        $this->flush($batches, $tables);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info('✓ Selesai! ' . self::TARGET . ' pengajuan simulasi berhasil dibuat.');
        $this->command->info('  Hapus: php artisan daisy:rollback-simulasi');
    }

    // ── Flush batch ke DB (urutan FK-safe) ────────────────────────────────

    private function flush(array $batches, array $order): void
    {
        foreach ($order as $table) {
            if (empty($batches[$table])) continue;
            foreach (array_chunk($batches[$table], 100) as $chunk) {
                DB::table($table)->insertOrIgnore($chunk);
            }
        }
    }

    // ── Helper: update path file ───────────────────────────────────────────

    private function swapPath(
        string $path,
        int $oldPid,
        int $newPid,
        ?int $oldAsmId = null,
        ?int $newAsmId = null
    ): string {
        $path = str_replace(
            "permohonan-akreditasi/{$oldPid}/",
            "permohonan-akreditasi/{$newPid}/",
            $path
        );
        if ($oldAsmId && $newAsmId) {
            $path = str_replace(
                ["asesmen/document/{$oldAsmId}/", "asesmen_documents/{$oldAsmId}/"],
                ["asesmen/document/{$newAsmId}/", "asesmen_documents/{$newAsmId}/"],
                $path
            );
        }
        return $path;
    }

    // ── Rollback: hapus semua data simulasi ────────────────────────────────

    public static function rollback(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $minPid = DB::table('pengajuan_akreditasi')
            ->where('is_example', 1)->min('id') ?? PHP_INT_MAX;

        $minAsmId = DB::table('asesmens')
            ->where('is_example', 1)->min('id') ?? PHP_INT_MAX;

        DB::table('penilaian_elemen_al')->where('id_asesmen', '>=', $minAsmId)->delete();
        DB::table('penilaian_elemen_ak')->where('id_asesmen', '>=', $minAsmId)->delete();
        DB::table('lha_asesors')->where('id_asesmen', '>=', $minAsmId)->delete();
        DB::table('hasil_akreditasi')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('borang_validations')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('borang_data')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('borang_imports')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('pengajuan_dokumen')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('pengajuan_pembayaran')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('pengajuan_status_log')->where('id_pengajuan', '>=', $minPid)->delete();
        DB::table('asesmen_documents')->where('id_asesmen', '>=', $minAsmId)->delete();
        DB::table('asesmen_user_roles')->where('is_example', 1)->delete();
        DB::table('asesmen_lapangan')->where('is_example', 1)->delete();
        DB::table('asesmen_kecukupan')->where('is_example', 1)->delete();
        DB::table('asesmens')->where('is_example', 1)->delete();
        DB::table('pengajuan_akreditasi')->where('is_example', 1)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
