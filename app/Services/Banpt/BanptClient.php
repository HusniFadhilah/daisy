<?php

namespace App\Services\Banpt;

use App\Models\StudyProgram;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BanptClient
{
    private const BASE_URL = 'https://service.banpt.or.id/bianglala/';
    private const TIMEOUT  = 25;
    private const SLEEP_MS = 500; // delay antar request (ms)

    /** Jenjang alias BAN-PT -> alias lokal */
    private const JENJANG_MAP = [
        's1'         => 'S1',
        's2'         => 'S2',
        's3'         => 'S3',
        's2 terapan' => 'S2 Terapan',
        's3 terapan' => 'S3 Terapan',
        'd-iv'       => 'D-IV',
        'd4'         => 'D-IV',
        'd-iii'      => 'D-III',
        'd3'         => 'D-III',
        'd-ii'       => 'D-II',
        'd-i'        => 'D-I',
        'profesi'    => 'Profesi',
        'sp-1'       => 'Sp-1',
        'sp-2'       => 'Sp-2',
    ];

    public function searchPt(string $universityName): array
    {
        $response = $this->get('searchPT.php', ['term' => $universityName]);
        if ($response === null) {
            return [];
        }
        return is_array($response) ? $response : [];
    }

    public function searchPs(string $ptLabel, string $programName): array
    {
        usleep(self::SLEEP_MS * 1000);
        $response = $this->get('searchPS.php', ['pt' => $ptLabel, 'term' => $programName]);
        if ($response === null) {
            return [];
        }
        return is_array($response) ? $response : [];
    }

    public function getAccreditationHistory(string $ptLabel, string $psLabel): array
    {
        usleep(self::SLEEP_MS * 1000);
        $response = $this->get('riakps.php', ['pt' => $ptLabel, 'ps' => $psLabel]);
        if ($response === null) {
            return [];
        }
        return is_array($response) ? $response : [];
    }

    /**
     * Cari akreditasi aktif untuk satu StudyProgram.
     * Return null jika tidak ditemukan atau error.
     * Return array dengan key:
     *   pt_label, ps_label, nama_pt, nama_ps, jenjang,
     *   peringkat, tanggal_sk, tanggal_kedaluwarsa, aktif, raw_payload
     */
    public function findActiveAccreditation(StudyProgram $studyProgram): ?array
    {
        $studyProgram->load(['university', 'degreeLevel']);
        $university = $studyProgram->university;
        if (!$university) {
            return null;
        }

        // 1. Cari PT
        $ptResults = $this->searchPt($university->name);
        if (empty($ptResults)) {
            return null;
        }

        $ptLabel = $this->pickBestMatch($ptResults, $university->name);
        if (!$ptLabel) {
            return null;
        }

        // 2. Cari PS
        $psResults = $this->searchPs($ptLabel, $studyProgram->name);
        if (empty($psResults)) {
            return null;
        }

        $psLabel = $this->pickBestPsMatch($psResults, $studyProgram->name, $studyProgram->degreeLevel?->alias ?? '');
        if (!$psLabel) {
            return null;
        }

        // 3. Ambil riwayat
        $history = $this->getAccreditationHistory($ptLabel, $psLabel);
        if (empty($history)) {
            return null;
        }

        return $this->resolveActiveRecord($history, $ptLabel, $psLabel);
    }

    private function pickBestMatch(array $results, string $target): ?string
    {
        $target = mb_strtolower(trim($target));
        $best   = null;
        $bestScore = 0;

        foreach ($results as $item) {
            $label = $item['label'] ?? $item['value'] ?? null;
            if (!$label) {
                continue;
            }
            similar_text($target, mb_strtolower(trim($label)), $pct);
            if ($pct > $bestScore) {
                $bestScore = $pct;
                $best      = $item['value'] ?? $label;
            }
        }

        // Ambil jika mirip setidaknya 60%
        return $bestScore >= 60 ? $best : null;
    }

    private function pickBestPsMatch(array $results, string $programName, string $jenjang): ?string
    {
        $targetName   = mb_strtolower(trim($programName));
        $targetJenjang = mb_strtolower(trim($jenjang));
        $best          = null;
        $bestScore     = 0;

        foreach ($results as $item) {
            $label = $item['label'] ?? $item['value'] ?? null;
            if (!$label) {
                continue;
            }
            $labelLower = mb_strtolower(trim($label));

            similar_text($targetName, $labelLower, $namePct);

            // Bonus jika jenjang cocok
            $jenjangBonus = 0;
            if ($targetJenjang && str_contains($labelLower, $targetJenjang)) {
                $jenjangBonus = 10;
            }

            $score = $namePct + $jenjangBonus;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $item['value'] ?? $label;
            }
        }

        return $bestScore >= 55 ? $best : null;
    }

    private function resolveActiveRecord(array $history, string $ptLabel, string $psLabel): ?array
    {
        // Struktur tiap record: [nama_pt, nama_ps, jenjang, tanggal_sk, peringkat, tanggal_kedaluwarsa, aktif]
        $activeRecord = null;
        $lastRecord   = null;

        foreach ($history as $record) {
            if (!is_array($record) || count($record) < 6) {
                continue;
            }

            $lastRecord = $record;

            $aktif = mb_strtolower(trim($record[6] ?? ''));
            if ($aktif === 'ya' || $aktif === 'y' || $aktif === '1') {
                $activeRecord = $record;
                break;
            }
        }

        $chosenRecord = $activeRecord ?? $lastRecord;
        if (!$chosenRecord) {
            return null;
        }

        $isActiveRecord = $chosenRecord === $activeRecord;

        return [
            'pt_label'           => $ptLabel,
            'ps_label'           => $psLabel,
            'nama_pt'            => trim($chosenRecord[0] ?? ''),
            'nama_ps'            => trim($chosenRecord[1] ?? ''),
            'jenjang'            => $this->normalizeJenjang(trim($chosenRecord[2] ?? '')),
            'tanggal_sk'         => trim($chosenRecord[3] ?? ''),
            'peringkat'          => trim($chosenRecord[4] ?? ''),
            'tanggal_kedaluwarsa' => $this->parseDate(trim($chosenRecord[5] ?? '')),
            'aktif'              => $isActiveRecord,
            'raw_payload'        => $chosenRecord,
        ];
    }

    public function normalizeJenjang(string $jenjang): string
    {
        $key = mb_strtolower(trim($jenjang));
        return self::JENJANG_MAP[$key] ?? $jenjang;
    }

    private function parseDate(string $date): ?string
    {
        if (empty($date) || $date === '-' || $date === '0000-00-00') {
            return null;
        }
        // Format BAN-PT biasanya DD-MM-YYYY atau YYYY-MM-DD
        $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $date);
            if ($dt) {
                return $dt->format('Y-m-d');
            }
        }
        return null;
    }

    private function get(string $endpoint, array $params): ?array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->retry(2, 1000)
                ->get(self::BASE_URL . $endpoint, $params);

            if (!$response->successful()) {
                Log::warning("BanptClient: {$endpoint} returned {$response->status()}");
                return null;
            }

            $json = $response->json();
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::error("BanptClient: {$endpoint} error — {$e->getMessage()}");
            return null;
        }
    }
}
