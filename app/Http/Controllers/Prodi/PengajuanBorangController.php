<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Indikator;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenUserRole;
use App\Models\DatasetSuplemen;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PengajuanBorangController extends Controller
{
    use AuthorizesRequests;
    public function validationSummary($id)
    {
        $pengajuan = PengajuanAkreditasi::with('borangValidators.borangValidation')->findOrFail($id);
        $this->authorize('update', $pengajuan);

        // Ambil record validasi dari assignment validator (ambil yang paling relevan / terbaru)
        $assignment = AsesmenUserRole::with(['user', 'borangValidation'])
            ->whereHas('asesmen', fn($q) => $q->where('id_pengajuan', $pengajuan->id))
            ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'dokumen')
            ->latest('updated_at')
            ->first();

        $validation = $assignment?->borangValidation;

        if (!$validation) {
            return response()->json([
                'success' => true,
                'has_validation' => false,
            ]);
        }

        $progress = $validation->getProgressPercentage();

        return response()->json([
            'success' => true,
            'has_validation' => true,
            'validator' => $assignment?->user ? [
                'id' => $assignment->user->id,
                'name' => $assignment->user->name,
            ] : null,
            'validation' => [
                'updated_at' => optional($validation->updated_at)->format('d M Y H:i'),
                'final_action' => $validation->final_action ?? null, // kalau ada di tabelmu
                'is_complete' => $validation->isCompletelyReviewed(),
                'counts' => [
                    'total' => $progress,
                    'led' => [
                        'total' => (int) $validation->total_elemen_led,
                        'reviewed' => (int) $validation->reviewed_led,
                    ],
                    'suplemen' => [
                        'total' => (int) $validation->total_elemen_suplemen,
                        'reviewed' => (int) $validation->reviewed_suplemen,
                    ],
                    'lkps' => [
                        'total' => (int) $validation->total_indikator_lkps,
                        'reviewed' => (int) $validation->reviewed_lkps,
                    ],
                ],
                'notes' => [
                    'catatan_validator' => $validation->catatan_validator,
                    'catatan_led' => $validation->catatan_led,
                    'catatan_suplemen' => $validation->catatan_suplemen,
                    'catatan_lkps' => $validation->catatan_lkps,
                ],
            ]
        ]);
    }

    public function validationDetails($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        $assignment = AsesmenUserRole::with(['user', 'borangValidation'])
            ->whereHas('asesmen', fn($q) => $q->where('id_pengajuan', $pengajuan->id))
            ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'dokumen')
            ->latest('updated_at')
            ->first();

        $validation = $assignment?->borangValidation;

        if (!$validation) {
            return response()->json([
                'success' => true,
                'has_validation' => false,
                'items' => []
            ]);
        }

        // review_led: key = elemen_id, review_suplemen: key = dataset_suplemen_id, review_lkps: key = indikator_id
        $reviewLed = $validation->review_led ?? [];
        $reviewSuplemen = $validation->review_suplemen ?? [];
        $reviewLkps = $validation->review_lkps ?? [];

        // Ambil label biar enak ditampilkan
        $elemenMap = ElemenStandar::whereIn('id', array_map('intval', array_keys($reviewLed)))->get()->keyBy('id');
        $suplemenMap = DatasetSuplemen::whereIn('id', array_map('intval', array_keys($reviewSuplemen)))->get()->keyBy('id');
        $indikatorMap = Indikator::whereIn('id', array_map('intval', array_keys($reviewLkps)))->get()->keyBy('id');

        $items = [
            'led' => [],
            'suplemen' => [],
            'lkps' => [],
            'revision_points' => $validation->revision_points ?? [],
        ];

        foreach ($reviewLed as $elemenId => $r) {
            $e = $elemenMap->get((int)$elemenId);
            $items['led'][] = [
                'id' => (int)$elemenId,
                'kode' => $e?->kode_elemen,
                'label' => $e ? ($e->kode_elemen . ' - ' . $e->pernyataan_elemen) : ('Elemen ID ' . $elemenId),
                'grade' => $r['grade'] ?? null,
                'catatan' => $r['catatan'] ?? null,
                'needs_revision' => in_array(($r['grade'] ?? ''), ['B', 'C']),
            ];
        }

        foreach ($reviewSuplemen as $dsId => $r) {
            $ds = $suplemenMap->get((int)$dsId);
            $items['suplemen'][] = [
                'id' => (int)$dsId,
                'section' => $ds?->section_key,
                'label' => $ds ? ('[' . $ds->section_key . '] ' . $ds->text_content) : ('Suplemen ID ' . $dsId),
                'grade' => $r['grade'] ?? null,
                'catatan' => $r['catatan'] ?? null,
                'needs_revision' => in_array(($r['grade'] ?? ''), ['B', 'C']),
            ];
        }

        foreach ($reviewLkps as $indikatorId => $r) {
            $i = $indikatorMap->get((int)$indikatorId);
            $items['lkps'][] = [
                'id' => (int)$indikatorId,
                'kode' => $i?->kode_indikator,
                'label' => $i ? ($i->kode_indikator . ' - ' . $i->deskripsi_indikator) : ('Indikator ID ' . $indikatorId),
                'grade' => $r['grade'] ?? null,
                'catatan' => $r['catatan'] ?? null,
                'needs_revision' => in_array(($r['grade'] ?? ''), ['B', 'C']),
            ];
        }

        return response()->json([
            'success' => true,
            'has_validation' => true,
            'validator' => $assignment?->user ? [
                'id' => $assignment->user->id,
                'name' => $assignment->user->name,
            ] : null,
            'updated_at' => optional($validation->updated_at)->format('d M Y H:i'),
            'items' => $items,
        ]);
    }
}
