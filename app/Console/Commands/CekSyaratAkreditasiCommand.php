<?php
// app/Console/Commands/CekSyaratAkreditasiCommand.php

namespace App\Console\Commands;

use App\Repositories\SyaratAkreditasiRepository;
use App\Models\DegreeLevel;
use Illuminate\Console\Command;

class CekSyaratAkreditasiCommand extends Command
{
    protected $signature   = 'syarat:cek {--flush : Flush cache sebelum cek}';
    protected $description = 'Debug nilai syarat akreditasi dari DB dan cache';

    public function __construct(private SyaratAkreditasiRepository $repo)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if ($this->option('flush')) {
            $this->repo->invalidateCache();
            $this->info('Cache di-flush.');
        }

        $degreeLevels = DegreeLevel::all();

        // ── Global ──
        $this->info("\n=== GLOBAL ===");
        $this->line('Kriteria required : ' . implode(', ', $this->repo->getKriteriaRequired()));
        $this->line('Rasio max default : ' . $this->repo->getRasioMaksDefault());
        $this->line('Rasio max lingkgn : ' . $this->repo->getRasioMaksLingkungan());
        $this->line('Rumpun khusus     : ' . implode(', ', $this->repo->getRumpunRasioKhusus()));
        $this->line('Rentang skor      : ' . count($this->repo->getRentangSkor()) . ' entry');

        // ── Per degree level ──
        foreach ($degreeLevels as $dl) {
            $this->info("\n=== " . ($dl->nama ?? $dl->name ?? $dl->nama_jenjang ?? '?') . " (id={$dl->id}) ===");

            $jabatan    = $this->repo->getJabatanValid($dl->id);
            $pJabatan   = $this->repo->getPersenMinimumJabatan($dl->id);
            $pSertifkat = $this->repo->getPersenMinimumSertifikatProfesi($dl->id);
            $pLulusan   = $this->repo->getPersenMinimumLulusan($dl->id);
            $tLulusan   = $this->repo->getTipeCapaianLulusan($dl->id);

            $jabatanStr = is_array($jabatan) ? implode(', ', $jabatan) : var_export($jabatan, true);

            $rows = [
                ['jabatan_valid',          $jabatanStr,  $jabatan  ? '✓' : '⚠ NULL'],
                ['persen_jabatan',         $pJabatan,    $pJabatan ? '✓' : '⚠ fallback'],
                ['persen_sertifikat',      $pSertifkat,  $pSertifkat ? '✓' : '⚠ fallback'],
                ['persen_lulusan',         $pLulusan,    $pLulusan ? '✓' : '⚠ fallback'],
                ['tipe_capaian_lulusan',   $tLulusan,    $tLulusan ? '✓' : '⚠ fallback'],
            ];

            $this->table(['Kunci', 'Nilai', 'Status'], $rows);
        }

        $this->info("\nSelesai.");
    }
}
