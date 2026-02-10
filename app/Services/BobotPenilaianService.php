<?php

namespace App\Services;

use App\Models\BobotPenilaian;
use App\Models\ElemenStandar;
use Illuminate\Support\Facades\DB;

class BobotPenilaianService
{
    protected $modelPenilaianElemen;

    public function __construct()
    {
        // Model akan di-set secara dinamis melalui setModel()
        $this->modelPenilaianElemen = null;
    }

    /**
     * Set model penilaian elemen secara dinamis
     */
    public function setModel($modelClass)
    {
        $this->modelPenilaianElemen = $modelClass;
        return $this;
    }

    public function getBobotForDegreeLevel($elemenId, $degreeLevelId)
    {
        return BobotPenilaian::where('id_elemen', $elemenId)
            ->where('id_degree_level', $degreeLevelId)
            ->first();
    }

    /**
     * Hitung nilai berbobot
     */
    public function calculateWeightedScore($nilai, $bobot)
    {
        return $nilai * $bobot;
    }

    /**
     * Hitung nilai berbobot per elemen, dikelompokkan berdasarkan kriteria
     */
    public function calculateByKriteria($asesmenId, $categoryId)
    {
        $modelPenilaianElemen = $this->modelPenilaianElemen;
        $penilaians = $modelPenilaianElemen::where('id_asesmen', $asesmenId)
            ->with(['elemenStandar.kriteria'])
            ->get();

        $hasil = [];

        foreach ($penilaians as $penilaian) {
            $bobot = $this->getBobotForDegreeLevel($penilaian->id_elemen, $penilaian->studyProgram->id_degree_level);

            if (!$bobot) {
                continue;
            }

            $nilaiBobot = $this->calculateWeightedScore($penilaian->skor, $bobot->bobot);

            $kriteriaId = $penilaian->elemenStandar->kriteria->id_kriteria ?? null;
            $kriteriaNama = $penilaian->elemenStandar->kriteria->nama_kriteria ?? 'Unknown';

            if (!isset($hasil[$kriteriaId])) {
                $hasil[$kriteriaId] = [
                    'kriteria_id' => $kriteriaId,
                    'kriteria_nama' => $kriteriaNama,
                    'kriteria_kode' => $penilaian->elemenStandar->kriteria->kode_kriteria ?? '-',
                    'elemen' => [],
                    'total' => 0,
                    'total_bobot' => 0,
                ];
            }

            $hasil[$kriteriaId]['elemen'][] = [
                'elemen_id' => $penilaian->id_elemen,
                'elemen_kode' => $penilaian->elemenStandar->kode_elemen,
                'elemen_nama' => $penilaian->elemenStandar->pernyataan_elemen,
                'skor' => $penilaian->skor,
                'bobot' => $bobot->bobot,
                'nilai_bobot' => $nilaiBobot,
            ];

            $hasil[$kriteriaId]['total'] += $nilaiBobot;
            $hasil[$kriteriaId]['total_bobot'] += $bobot->bobot;
        }

        return array_values($hasil);
    }

    /**
     * Hitung total skor keseluruhan
     */
    public function calculateTotalScore($asesmenId, $categoryId)
    {
        $byKriteria = $this->calculateByKriteria($asesmenId, $categoryId);

        $totalNilai = 0;
        $totalBobot = 0;

        foreach ($byKriteria as $kriteria) {
            $totalNilai += $kriteria['total'];
            $totalBobot += $kriteria['total_bobot'];
        }

        return [
            'per_kriteria' => $byKriteria,
            'total_nilai_bobot' => $totalNilai,
            'total_bobot' => $totalBobot,
            'nilai_akhir' => $totalBobot > 0 ? round($totalNilai / $totalBobot, 2) : 0,
        ];
    }

    /**
     * Ambil semua bobot dengan filter
     */
    public function getAll($filters = [])
    {
        $query = BobotPenilaian::with(['elemenStandar.kriteria', 'category']);

        if (isset($filters['id_elemen'])) {
            $query->where('id_elemen', $filters['id_elemen']);
        }

        if (isset($filters['id_category'])) {
            $query->where('id_category', $filters['id_category']);
        }

        return $query->get();
    }

    /**
     * Buat bobot baru
     */
    public function create($data)
    {
        return BobotPenilaian::create($data);
    }

    /**
     * Update bobot
     */
    public function update($id, $data)
    {
        $bobot = BobotPenilaian::findOrFail($id);
        $bobot->update($data);
        return $bobot;
    }

    /**
     * Hapus bobot
     */
    public function delete($id)
    {
        $bobot = BobotPenilaian::findOrFail($id);
        $bobot->delete();
        return true;
    }

    /**
     * Cari bobot by ID
     */
    public function find($id)
    {
        return BobotPenilaian::find($id);
    }
}
