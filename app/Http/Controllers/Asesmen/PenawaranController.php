<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PenawaranController extends Controller
{
    /**
     * ============================================
     * WORKFLOW METHODS - PENAWARAN & APPROVAL
     * ============================================
     */

    /**
     * Daftar penawaran untuk asesor (pending acceptance)
     */
    public function index()
    {
        $user = Auth::user();

        // Get penawaran yang belum direspon
        $penawarans = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_penawaran', 'pending')
            ->with(['asesmen', 'role'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get penawaran yang sudah direspon
        $riwayat = AsesmenUserRole::where('id_user', $user->id)
            ->whereIn('status_penawaran', ['accepted', 'rejected'])
            ->with(['asesmen', 'role'])
            ->orderBy('responded_at', 'desc')
            ->get();

        return view('asesmen.ak.penawaran.index', compact('penawarans', 'riwayat'));
    }

    public function cekPenawaran($id)
    {
        $user = Auth::user();

        $asesmen = Asesmen::findOrFail($id);

        $penawaran = AsesmenUserRole::where('id_asesmen', $id)
            ->where('id_user', $user->id)
            ->with('role', 'asesmen')
            ->firstOrFail();

        // Kalau sudah accepted, langsung redirect ke berkas (biar tidak bolak-balik ke sini)
        if ($penawaran->status_penawaran === 'accepted') {
            return redirect()->route('ak.berkas.show', $id);
        }

        // status: pending / rejected → tampilkan halaman "detail penawaran"
        return view('asesmen.ak.penawaran.detail', compact('asesmen', 'penawaran'));
    }

    /**
     * Terima penawaran asesmen
     */
    public function acceptPenawaran(Request $request, $assignmentId)
    {
        $request->validate([
            'response_note' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id', $assignmentId)
                ->where('id_user', $user->id)
                ->where('status_penawaran', 'pending')
                ->firstOrFail();

            $assignment->update([
                'status_penawaran' => 'accepted',
                'responded_at' => now(),
                'response_note' => $request->response_note,
                'status_pekerjaan' => 'not_started',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penawaran berhasil diterima. Anda dapat mulai melakukan penilaian.',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerima penawaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tolak penawaran asesmen
     */
    public function rejectPenawaran(Request $request, $assignmentId)
    {
        $request->validate([
            'response_note' => 'required|string|max:1000',
        ]);

        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id', $assignmentId)
                ->where('id_user', $user->id)
                ->where('status_penawaran', 'pending')
                ->firstOrFail();

            $assignment->update([
                'status_penawaran' => 'rejected',
                'responded_at' => now(),
                'response_note' => $request->response_note,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penawaran ditolak.',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak penawaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}
