<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\User;
use App\Models\Asesmen;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemen;
use App\Models\JenjangPenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\ValidasiExcelService;
use App\Services\PenilaianExcelService;

class ValidasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get asesmen yang sudah di-assign ke validator ini
        $assignments = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->where('id_role', 4)
            ->with(['asesmen'])
            ->get();

        // Get penilaian yang perlu divalidasi
        $idAsesmens = $assignments->pluck('id_asesmen');

        $needsValidation = [];
        foreach ($idAsesmens as $idAsesmen) {
            $asesmen = Asesmen::find($idAsesmen);

            // Get asesor yang sudah submit
            $submittedAsesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('status_pekerjaan', 'submitted')
                ->where('id_role', 3)
                ->with('user')
                ->get();

            if ($submittedAsesors->count() > 0) {
                $needsValidation[] = [
                    'asesmen' => $asesmen,
                    'asesors' => $submittedAsesors,
                ];
            }
        }

        return view('asesmen.ak.validasi.index', compact('needsValidation'));
    }

    /**
     * Halaman validasi untuk specific asesmen dan asesor
     */
    public function asesor($idAsesmen, $asesor1Id, $asesor2Id = null)
    {
        $validator = Auth::user();

        // Pastikan validator punya akses
        AsesmenUserRole::where([
            'id_asesmen' => $idAsesmen,
            'id_user' => $validator->id,
            'id_role' => 4
        ])->firstOrFail();

        $asesmen = Asesmen::findOrFail($idAsesmen);

        // Ambil semua jenjang penilaian sekaligus
        $jenjangPenilaian = JenjangPenilaian::orderBy('skor')->get()->keyBy('skor');
        $isApproved = AsesmenUserRole::where('id_asesmen', $idAsesmen)->where('status_pekerjaan', 'approved')->exists();

        // Ambil asesor 1 & 2
        $asesor1 = User::findOrFail($asesor1Id);
        $asesor2 = $asesor2Id
            ? User::findOrFail($asesor2Id)
            : AsesmenUserRole::with('user')
            ->where('id_asesmen', $idAsesmen)
            ->where('id_role', 3)
            ->where('id_user', '!=', $asesor1Id)
            ->firstOrFail()
            ->user;

        // --- CEK APAKAH KEDUA ASESOR SUDAH SUBMIT ---
        $submittedAsesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_role', 3)
            ->whereNotIn('status_pekerjaan', ['not_started'])
            ->whereIn('id_user', [$asesor1->id, $asesor2->id])
            ->pluck('id_user')
            ->toArray();

        $submittedCount = count($submittedAsesors);

        if ($submittedCount < 2) {
            $message = $submittedCount === 0
                ? 'Kedua asesor belum submit penilaian, mohon tunggu hingga keduanya lengkap.'
                : 'Salah satu asesor belum submit penilaian, mohon tunggu hingga keduanya lengkap.';
            abort(403, $message);
        }

        // Ambil kriteria + elemen + indikator + penilaian untuk kedua asesor sekaligus
        $kriterias = Kriteria::with([
            'elemenStandar.indikator',
            'elemenStandar.penilaian' => fn($q) => $q->where('id_asesmen', $idAsesmen)
                ->whereIn('id_asesor', [$asesor1->id, $asesor2->id])
        ])->get();

        // Total elemen (tetap pakai count() seperti permintaan)
        $totalElemen = ElemenStandar::count();

        // Hitung progress keduanya sekaligus
        $completedByAsesor = PenilaianElemen::where('id_asesmen', $idAsesmen)
            ->whereNotNull('skor')
            ->whereIn('id_asesor', [$asesor1->id, $asesor2->id])
            ->select('id_asesor', DB::raw('COUNT(DISTINCT id_elemen) as total'))
            ->groupBy('id_asesor')
            ->pluck('total', 'id_asesor');

        $progress1 = [
            'total' => $totalElemen,
            'completed' => $completedByAsesor[$asesor1->id] ?? 0,
            'percentage' => $totalElemen ? round(($completedByAsesor[$asesor1->id] ?? 0) / $totalElemen * 100) : 0
        ];
        $progress2 = [
            'total' => $totalElemen,
            'completed' => $completedByAsesor[$asesor2->id] ?? 0,
            'percentage' => $totalElemen ? round(($completedByAsesor[$asesor2->id] ?? 0) / $totalElemen * 100) : 0
        ];

        // Hitung validasi
        $validatedCount = PenilaianElemen::where('id_asesmen', $idAsesmen)
            ->whereIn('status_validasi', ['validated', 'revision_required'])
            ->distinct('id_elemen')
            ->count('id_elemen');

        $validationPercentage = $totalElemen ? round(($validatedCount / $totalElemen) * 100) : 0;
        $allValidated = $validatedCount == $totalElemen;

        return view('asesmen.ak.validasi.asesor', compact(
            'asesmen',
            'asesor1',
            'asesor2',
            'kriterias',
            'progress1',
            'progress2',
            'totalElemen',
            'validatedCount',
            'validationPercentage',
            'allValidated',
            'jenjangPenilaian',
            'isApproved'
        ));
    }

    public function getValidasiDetail($idAsesmen, $elemenId)
    {
        try {
            $elemen = ElemenStandar::with('kriteria')->findOrFail($elemenId);

            // Get validasi data
            $validasi = PenilaianElemen::where('id_asesmen', $idAsesmen)
                ->where('id_elemen', $elemenId)
                ->whereIn('status_validasi', ['validated', 'revision_needed'])
                ->with('validator')
                ->first();

            if (!$validasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data validasi tidak ditemukan'
                ], 404);
            }

            // Get penilaian dari semua asesor
            $penilaianAsesor = PenilaianElemen::where('id_asesmen', $idAsesmen)
                ->where('id_elemen', $elemenId)
                ->with('asesor')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'elemen' => $elemen,
                    'validasi' => $validasi,
                    'penilaianAsesor' => $penilaianAsesor,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error get validasi detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail penilaian untuk modal validasi
     */
    public function getElemenDetail(Request $request, Asesmen $asesmen, ElemenStandar $elemen)
    {
        // Misal kamu sudah punya $asesor1 dan $asesor2 (dari asesmen / route)
        $penilaianElemen = PenilaianElemen::where('id_asesmen', $asesmen->id)->where('id_elemen', $elemen->id)->with('asesor')->get();
        $elemen = ElemenStandar::whereId($elemen->id)->with(['kriteria', 'indikator'])->firstOrFail();
        $penilaianAsesor1 = $penilaianElemen[0];
        $penilaianAsesor2 = $penilaianElemen[1];

        $hasDifference = $penilaianAsesor1 && $penilaianAsesor2
            ? $penilaianAsesor1->skor !== $penilaianAsesor2->skor
            : false;

        return response()->json([
            'success' => true,
            'data' => [
                'elemen' => $elemen,
                'asesor1' => [
                    'user' => $penilaianAsesor1?->asesor,
                    'penilaian' => $penilaianAsesor1,
                ],
                'asesor2' => [
                    'user' => $penilaianAsesor2?->asesor,
                    'penilaian' => $penilaianAsesor2,
                ],
                'hasDifference' => $hasDifference,
            ]
        ]);
    }

    /**
     * Validasi satu elemen
     */
    public function validateElemen(Request $request, $idAsesmen, $elemenId)
    {
        $request->validate([
            'status' => 'required|in:validated,revision_required',
            'catatan_validator' => 'nullable|string',
            'skor_final' => 'required_if:status,validated|integer|min:0|max:4',
            'id_asesors' => 'required_if:status,revision_required',
        ]);

        DB::beginTransaction();
        try {
            // Update semua penilaian untuk elemen ini
            $penilaianElemen = PenilaianElemen::where('id_asesmen', $idAsesmen)
                ->where('id_elemen', $elemenId);
            if ($request->id_asesors)
                $penilaianElemen->whereIn('id_asesor', $request->id_asesors);
            $penilaianElemen->update([
                'status_validasi' => $request->status,
                'catatan_validator' => $request->catatan_validator,
                'validated_at' => now(),
                'validated_by' => Auth::user()->id,
            ]);

            if ($request->status === 'revision_required') {
                AsesmenUserRole::where('id_asesmen', $idAsesmen)
                    ->whereIn('id_user', $request->id_asesors)
                    ->update([
                        'status_pekerjaan' => 'revision_required',
                        'submitted_at' => null, // ← Reset submitted_at
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->status === 'validated'
                    ? 'Penilaian berhasil divalidasi'
                    : 'Permintaan revisi berhasil dikirim. Asesor sekarang dapat mengedit penilaian.',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validasi otomatis semua elemen yang nilainya sama
     */
    public function validateAllAgreed($idAsesmen)
    {
        DB::beginTransaction();
        try {
            // Get all elemen in this asesmen
            $elemenIds = ElemenStandar::pluck('id');

            $validatedCount = 0;

            foreach ($elemenIds as $elemenId) {
                // Get penilaian from both asesor
                $penilaianList = PenilaianElemen::where('id_asesmen', $idAsesmen)
                    ->where('id_elemen', $elemenId)
                    ->whereNotNull('skor')
                    ->get();

                // Must have exactly 2 penilaian
                if ($penilaianList->count() != 2) {
                    continue;
                }

                // Check if both scores are the same
                $skor1 = $penilaianList[0]->skor;
                $skor2 = $penilaianList[1]->skor;

                if ($skor1 == $skor2) {
                    // Auto-validate with the agreed score
                    PenilaianElemen::where('id_asesmen', $idAsesmen)
                        ->where('id_elemen', $elemenId)
                        ->update([
                            'status_validasi' => 'validated',
                            'skor_final' => $skor1,
                            'catatan_validator' => 'Auto-validated: Kedua asesor memberikan nilai yang sama',
                            'validated_at' => now(),
                            'validated_by' => Auth::user()->id,
                        ]);

                    $validatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memvalidasi {$validatedCount} elemen yang nilainya sama",
                'validated_count' => $validatedCount,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan validasi otomatis: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve semua penilaian (final step)
     */
    public function approveAllPenilaian($idAsesmen)
    {
        DB::beginTransaction();
        try {
            // Check if all elemen are validated
            $totalElemen = ElemenStandar::count();
            $validatedCount = PenilaianElemen::where('id_asesmen', $idAsesmen)
                ->where('status_validasi', 'validated')
                ->distinct('id_elemen')
                ->count('id_elemen');

            if ($validatedCount < $totalElemen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak semua elemen telah divalidasi. Harap lengkapi validasi terlebih dahulu.',
                ], 422);
            }

            // Update asesor assignment status
            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::user()->id,
                ]);

            // Lock all penilaian
            PenilaianElemen::where('id_asesmen', $idAsesmen)
                ->update([
                    'is_locked' => true,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua penilaian telah disetujui dan dikunci',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export perbandingan penilaian ke Excel
     */
    public function exportComparison($idAsesmen, $asesor1Id, $asesor2Id)
    {
        $asesmen = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->firstOrFail()
            ->asesmen;

        $penilaianExcelService = ValidasiExcelService::generateTemplate($asesmen, $asesor1Id, $asesor2Id);
        $tempFile = $penilaianExcelService[0];
        $filename = $penilaianExcelService[1];

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
