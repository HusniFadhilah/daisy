<?php

namespace App\Http\Controllers\Prodi;

use Illuminate\Http\Request;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;

class PengajuanBorangController extends Controller
{
    public function validationSummary(PengajuanAkreditasi $pengajuan)
    {
        // ambil assignment validator aktif (accepted) dari helper model kamu
        $assignment = $pengajuan->activeBorangValidator();
        // atau kalau ingin ambil yang latest accepted:
        // $assignment = $pengajuan->borangValidators()->where('status_penawaran','accepted')->latest()->first();

        if (!$assignment) {
            return response()->json([
                'success' => true,
                'has_validation' => false,
                'message' => 'Belum ada validator aktif.',
            ]);
        }

        // pastikan relasi borangValidation kebaca
        $assignment->load(['user', 'borangValidation']);

        $validation = $assignment->borangValidation;

        if (!$validation) {
            return response()->json([
                'success' => true,
                'has_validation' => false,
                'message' => 'Validator aktif, tetapi belum ada data validasi.',
                'validator' => [
                    'name' => $assignment->user->name ?? '-',
                ],
            ]);
        }

        // Progress (pakai method yang kamu sudah punya)
        $progress = $validation->getProgressPercentage();
        // contoh expected: ['percentage'=>..,'reviewed'=>..,'total'=>..,'led_percentage'=>.. dst]

        // Tentukan hasil akhir (approve/revision) dari data validasi final
        // Sesuaikan nama kolommu. Misal: $validation->final_action / $validation->status_final / dll.
        // Kalau belum ada, tampilkan "Belum disubmit".
        $finalAction = $validation->final_action ?? null; // <- sesuaikan kalau beda

        return response()->json([
            'success' => true,
            'has_validation' => true,
            'validator' => [
                'name' => $assignment->user->name ?? '-',
            ],
            'assignment' => [
                'id' => $assignment->id,
                'status_pekerjaan' => $assignment->status_pekerjaan ?? null,
            ],
            'validation' => [
                'final_action' => $finalAction, // approve|revision|null
                'is_complete'  => (bool) $validation->isCompletelyReviewed(),
                'notes' => [
                    'catatan_validator' => $validation->catatan_validator ?? '',
                    'catatan_led' => $validation->catatan_led ?? '',
                    'catatan_suplemen' => $validation->catatan_suplemen ?? '',
                    'catatan_lkps' => $validation->catatan_lkps ?? '',
                ],
                'counts' => [
                    'led' => [
                        'reviewed' => (int) $validation->reviewed_led,
                        'total' => (int) $validation->total_elemen_led,
                        'percentage' => (int) ($progress['led_percentage'] ?? 0),
                    ],
                    'suplemen' => [
                        'reviewed' => (int) $validation->reviewed_suplemen,
                        'total' => (int) $validation->total_elemen_suplemen,
                        'percentage' => (int) ($progress['suplemen_percentage'] ?? 0),
                    ],
                    'lkps' => [
                        'reviewed' => (int) $validation->reviewed_lkps,
                        'total' => (int) $validation->total_indikator_lkps,
                        'percentage' => (int) ($progress['lkps_percentage'] ?? 0),
                    ],
                    'total' => [
                        'reviewed' => (int) ($progress['reviewed'] ?? 0),
                        'total' => (int) ($progress['total'] ?? 0),
                        'percentage' => (int) ($progress['percentage'] ?? 0),
                    ],
                ],
                'updated_at' => optional($validation->updated_at)->format('d/m/Y H:i'),
            ],
        ]);
    }
}
