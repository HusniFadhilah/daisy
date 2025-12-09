<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asesmen;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            ->limit(10)
            ->get();

        return view('asesmen.ak.penawaran.index', compact('penawarans', 'riwayat'));
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
