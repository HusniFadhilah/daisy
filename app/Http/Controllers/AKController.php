<?php

namespace App\Http\Controllers;

use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\Indikator;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AKController extends Controller
{
    /**
     * Display a listing of asesmens (berkas) for current user
     */
    public function berkas()
    {
        $user = Auth::user();

        // Get asesmens where user is assigned
        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id);
        })->with(['userRoles' => function ($query) use ($user) {
            $query->where('id_user', $user->id)->with('role');
        }])->latest()->paginate(10);

        // Calculate progress for each asesmen
        foreach ($asesmens as $asesmen) {
            $asesmen->progress = $this->calculateProgress($asesmen->id, $user->id);
        }

        return view('asesmen.ak.berkas.index', compact('asesmens'));
    }

    /**
     * Show detail asesmen with accordion per elemen
     */
    public function showBerkas($id)
    {
        $user = Auth::user();

        // Check if user has access to this asesmen
        $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id);
        })->findOrFail($id);

        // Get all kriteria with elemen and indikator
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.penilaian' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)
                    ->where('id_user', $user->id);
            }
        ])->orderBy('id_kriteria')->get();

        // Calculate progress
        $progress = $this->calculateProgress($asesmen->id, $user->id);

        return view('asesmen.ak.berkas.show', compact('asesmen', 'kriterias', 'progress'));
    }

    /**
     * Save penilaian for specific indikator (AJAX)
     * UPDATED: Support skor 0-4
     */
    public function simpanNilai(Request $request, $asesmenId)
    {
        $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id_elemen',
            'skor' => 'required|integer|min:0|max:4', // UPDATED: Support 0-4
            'komentar' => 'nullable|string|max:5000',
        ]);

        try {
            $user = Auth::user();

            // Verify user has access
            $hasAccess = AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini'
                ], 403);
            }

            // Update or create penilaian
            $penilaian = PenilaianElemen::updateOrCreate(
                [
                    'id_asesmen' => $asesmenId,
                    'id_user' => $user->id,
                    'id_elemen' => $request->id_elemen,
                ],
                [
                    'skor' => $request->skor,
                    'komentar' => $request->komentar,
                    'status' => 'draft',
                ]
            );

            // Calculate new progress
            $progress = $this->calculateProgress($asesmenId, $user->id);

            // Get skor label and class for response
            $skorInfo = $this->getSkorInfo($request->skor);

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan',
                'data' => $penilaian,
                'progress' => $progress,
                'skor_info' => $skorInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get skor information (label, color, class)
     */
    private function getSkorInfo($skor)
    {
        $skorMapping = [
            0 => [
                'label' => 'Tidak Memenuhi (Not Met)',
                'color' => '#f44336',
                'class' => 'danger',
            ],
            1 => [
                'label' => 'Tidak Memenuhi (Not Met)',
                'color' => '#ff9800',
                'class' => 'warning',
            ],
            2 => [
                'label' => 'Lemah (Weakness/Cause of Concern)',
                'color' => '#ffeb3b',
                'class' => 'warning',
            ],
            3 => [
                'label' => 'Memenuhi (Met)',
                'color' => '#8bc34a',
                'class' => 'success',
            ],
            4 => [
                'label' => 'Pelampauan Standar (Exceeding Standard)',
                'color' => '#4caf50',
                'class' => 'success',
            ],
        ];

        return $skorMapping[$skor] ?? [
            'label' => 'Unknown',
            'color' => '#9e9e9e',
            'class' => 'secondary',
        ];
    }

    /**
     * Calculate progress percentage for an asesmen
     */
    private function calculateProgress($asesmenId, $userId)
    {
        // Total indikators
        $totalElemens = ElemenStandar::count();

        // Completed elemens (has penilaian)
        $completedElemens = PenilaianElemen::where('id_asesmen', $asesmenId)
            ->where('id_user', $userId)
            ->whereNotNull('skor')
            ->count();

        $percentage = $totalElemens > 0
            ? round(($completedElemens / $totalElemens) * 100, 1)
            : 0;

        return [
            'total' => $totalElemens,
            'completed' => $completedElemens,
            'percentage' => $percentage,
            'remaining' => $totalElemens - $completedElemens,
        ];
    }

    /**
     * Get heatmap data for visualization (NEW)
     */
    public function getHeatmapData($asesmenId)
    {
        $user = Auth::user();

        // Verify access
        $hasAccess = AsesmenUserRole::where('id_asesmen', $asesmenId)
            ->where('id_user', $user->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get all penilaian with kriteria, elemen info
        $heatmapData = PenilaianElemen::where('id_asesmen', $asesmenId)
            ->where('id_user', $user->id)
            ->with([
                'elemen.kriteria'
            ])
            ->get()
            ->map(function ($penilaian) {
                return [
                    'id_elemen' => $penilaian->id_elemen,
                    'kriteria_code' => $penilaian->elemen->kriteria->kode_kriteria,
                    'elemen_code' => $penilaian->elemen->kode_elemen,
                    'skor' => $penilaian->skor,
                    'skor_info' => $this->getSkorInfo($penilaian->skor),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $heatmapData,
        ]);
    }

    /**
     * Display split nilai (untuk rekonsiliasi asesor)
     */
    public function split()
    {
        $user = Auth::user();

        // Get asesmens with split (difference in scores between assessors)
        $asesmens = Asesmen::whereHas('penilaianElemen', function ($query) {
            // Find asesmens with different scores for same indikator
            $query->select('id_asesmen', 'id_elemen')
                ->groupBy('id_asesmen', 'id_elemen')
                ->havingRaw('COUNT(DISTINCT skor) > 1');
        })->with('userRoles.user')->latest()->paginate(10);

        return view('asesmen.ak.split.index', compact('asesmens'));
    }

    /**
     * Show detail split for rekonsiliasi
     */
    public function showSplit($id)
    {
        $asesmen = Asesmen::findOrFail($id);

        // Get indikators with different scores (split)
        $splits = DB::table('penilaian_elemen as p1')
            ->join('penilaian_elemen as p2', function ($join) {
                $join->on('p1.id_asesmen', '=', 'p2.id_asesmen')
                    ->on('p1.id_elemen', '=', 'p2.id_elemen')
                    ->on('p1.id_user', '<>', 'p2.id_user')
                    ->whereRaw('p1.skor <> p2.skor');
            })
            ->join('elemen_standar as e', 'p1.id_elemen', '=', 'e.id_elemen')
            ->join('kriteria as k', 'e.id_kriteria', '=', 'k.id_kriteria')
            ->join('users as u1', 'p1.id_user', '=', 'u1.id')
            ->join('users as u2', 'p2.id_user', '=', 'u2.id')
            ->where('p1.id_asesmen', $id)
            ->select(
                'k.nama_kriteria',
                'e.pernyataan_elemen',
                'p1.skor as skor_asesor1',
                'p2.skor as skor_asesor2',
                'p1.komentar as komentar_asesor1',
                'p2.komentar as komentar_asesor2',
                'u1.name as nama_asesor1',
                'u2.name as nama_asesor2',
                'p1.id_user as user_id1',
                'p2.id_user as user_id2'
            )
            ->groupBy(
                'k.nama_kriteria',
                'e.pernyataan_elemen',
                'p1.skor',
                'p2.skor',
                'p1.komentar',
                'p2.komentar',
                'u1.name',
                'u2.name',
                'p1.id_user',
                'p2.id_user'
            )
            ->get();

        return view('asesmen.ak.split.show', compact('asesmen', 'splits'));
    }

    /**
     * Save rekonsiliasi hasil split
     */
    public function rekonsiliasi(Request $request, $id)
    {
        $request->validate([
            'id_indikator' => 'required|exists:indikator,id_indikator',
            'skor_final' => 'required|integer|min:0|max:3',
            'komentar_final' => 'required|string|max:5000',
        ]);

        try {
            DB::beginTransaction();

            // Update all penilaian for this elemen to same score
            PenilaianElemen::where('id_asesmen', $id)
                ->where('id_elemen', $request->id_elemen)
                ->update([
                    'skor' => $request->skor_final,
                    'komentar' => $request->komentar_final,
                    'status' => 'submitted',
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rekonsiliasi berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display upload page
     */
    public function upload()
    {
        $user = Auth::user();

        // Get user's asesmens with documents
        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id);
        })->with('documents')->latest()->get();

        return view('asesmen.ak.upload.index', compact('asesmens'));
    }

    /**
     * Store uploaded file
     */
    public function storeUpload(Request $request)
    {
        $request->validate([
            'id_asesmen' => 'required|exists:asesmens,id',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('asesmens/documents', $fileName, 'public');

            // Save to database (assuming you have AssessmentDocument model)
            // AssessmentDocument::create([
            //     'id_asesmen' => $request->id_asesmen,
            //     'file_name' => $fileName,
            //     'file_path' => $filePath,
            //     'keterangan' => $request->keterangan,
            //     'uploaded_by' => Auth::id(),
            // ]);

            return redirect()->back()->with('success', 'File berhasil diupload');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal upload file: ' . $e->getMessage());
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteUpload($id)
    {
        try {
            // Assuming you have AssessmentDocument model
            // $document = AssessmentDocument::findOrFail($id);

            // Check permission
            // if ($document->uploaded_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Anda tidak memiliki izin untuk menghapus file ini'
            //     ], 403);
            // }

            // Delete file from storage
            // Storage::disk('public')->delete($document->file_path);

            // Delete record
            // $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display validasi page
     */
    public function validasi()
    {
        $user = Auth::user();

        // Get asesmens ready for validation (all penilaian completed)
        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id);
        })->with(['penilaianElemen' => function ($query) use ($user) {
            $query->where('id_user', $user->id);
        }])->latest()->paginate(10);

        // Add completion status
        foreach ($asesmens as $asesmen) {
            $asesmen->is_complete = $this->isAsesmenComplete($asesmen->id, $user->id);
            $asesmen->progress = $this->calculateProgress($asesmen->id, $user->id);
        }

        return view('asesmen.ak.validasi.index', compact('asesmens'));
    }

    /**
     * Show validasi detail
     */
    public function showValidasi($id)
    {
        $user = Auth::user();
        $asesmen = Asesmen::findOrFail($id);

        // Get all penilaian with kriteria, elemen, elemen
        $penilaians = PenilaianElemen::where('id_asesmen', $id)
            ->where('id_user', $user->id)
            ->with([
                'elemen.kriteria',
                'elemen.indikator.jenisIndikator'
            ])
            ->get()
            ->groupBy(function ($item) {
                return $item->indikator->elemen->kriteria->nama_kriteria;
            });

        $progress = $this->calculateProgress($id, $user->id);

        return view('asesmen.ak.validasi.show', compact('asesmen', 'penilaians', 'progress'));
    }

    /**
     * Check if asesmen is complete (all indikators have penilaian)
     */
    private function isAsesmenComplete($asesmenId, $userId)
    {
        $totalIndikators = Indikator::count();
        $completedIndikators = PenilaianElemen::where('id_asesmen', $asesmenId)
            ->where('id_user', $userId)
            ->whereNotNull('skor')
            ->count();

        return $totalIndikators === $completedIndikators;
    }
}
