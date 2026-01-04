<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\Asesmen;
use App\Helpers\RouteHelper;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPenawaranResponseEmail;

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

    /**
     * Show penawaran detail (generic untuk AK & AL)
     */
    public function show($token)
    {
        $user = Auth::user();
        // Get assignment
        $assignmentId = RouteHelper::decryptId($token);

        // Get assignment
        $assignment = AsesmenUserRole::where('id', $assignmentId)
            ->where('id_user', $user->id)
            ->with(['role', 'asesmen.studyProgram'])
            ->firstOrFail();

        $asesmen = $assignment->asesmen;

        // ✅ If already accepted, redirect ke berkas
        if ($assignment->status_penawaran === 'accepted') {
            $route = $assignment->jenis_asesmen === 'ak'
                ? 'ak.berkas.show'
                : 'al.berkas.show';

            return redirect()->route($route, $asesmen->id)
                ->with('info', 'Penawaran sudah diterima. Silakan lanjutkan penilaian.');
        }

        // ✅ If rejected, show with info
        if ($assignment->status_penawaran === 'rejected') {
            return view('asesmen.penawaran.detail', compact('asesmen', 'assignment'))
                ->with('info', 'Penawaran ini sudah ditolak sebelumnya.');
        }

        // ✅ Pending: show detail for response
        return view('asesmen.penawaran.detail', compact('asesmen', 'assignment'));
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
    public function acceptPenawaran(Request $request, $token)
    {
        $request->validate([
            'response_note' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            $assignmentId = RouteHelper::decryptId($token);
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

            try {
                SendPenawaranResponseEmail::dispatch($assignment, 'accepted');
            } catch (\Exception $e) {
                Log::error("Gagal dispatch email job accepted", [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
            }

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
    public function rejectPenawaran(Request $request, $token)
    {
        $request->validate([
            'response_note' => 'required|string|max:1000',
        ]);

        try {
            $user = Auth::user();
            $assignmentId = RouteHelper::decryptId($token);
            $assignment = AsesmenUserRole::where('id', $assignmentId)
                ->where('id_user', $user->id)
                ->where('status_penawaran', 'pending')
                ->firstOrFail();

            $assignment->update([
                'status_penawaran' => 'rejected',
                'responded_at' => now(),
                'response_note' => $request->response_note,
            ]);

            try {
                SendPenawaranResponseEmail::dispatch($assignment, 'rejected');
            } catch (\Exception $e) {
                Log::error("Gagal dispatch email job rejected", [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
            }

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
