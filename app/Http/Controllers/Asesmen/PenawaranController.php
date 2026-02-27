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

        // Get penawaran yang telah direspon
        $assignments = AsesmenUserRole::where('id_user', $user->id)
            ->whereIn('status_penawaran', ['accepted', 'rejected'])
            ->with(['asesmen', 'role'])
            ->orderBy('responded_at', 'desc')
            ->get();

        return view('asesmen.penawaran.index', compact('penawarans', 'assignments'));
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
            if ($assignment->jenis_asesmen === 'dokumen') {
                $route = 'validator.borang.show';
                $param = $assignment->id;
            } elseif ($assignment->jenis_asesmen === 'ak') {
                $route = 'ak.berkas.show';
                $param = $asesmen->id;
            } else {
                $route = 'al.berkas.show';
                $param = $asesmen->id;
            }

            return redirect()->route($route, $param)
                ->with('info', 'Penawaran telah diterima. Silakan lanjutkan ' . ($assignment->jenis_asesmen === 'dokumen' ? 'penilaian' : 'validasi'));
        }

        // ✅ If rejected, show with info
        if ($assignment->status_penawaran === 'rejected') {
            return view('asesmen.penawaran.detail', compact('asesmen', 'assignment'))
                ->with('info', 'Penawaran ini telah ditolak sebelumnya.');
        }

        // ✅ Pending: show detail for response
        return view('asesmen.penawaran.detail', compact('asesmen', 'assignment'));
    }

    public function cekPenawaran($idAsesmen, $jenisAsesmen)
    {
        $user = Auth::user();

        $asesmen = Asesmen::findOrFail($idAsesmen);

        $penawaran = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->where('id_user', $user->id)
            ->with('role', 'asesmen')
            ->firstOrFail();

        // Kalau telah accepted, langsung redirect ke berkas (biar tidak bolak-balik ke sini)
        if ($penawaran->status_penawaran === 'accepted') {
            return redirect()->route($jenisAsesmen . '.berkas.show', $idAsesmen);
        }

        // status: pending / rejected → tampilkan halaman "detail penawaran"
        return view('asesmen.' . $jenisAsesmen . '.penawaran.detail', compact('asesmen', 'penawaran'));
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

            if ($assignment->jenis_asesmen == 'dokumen') {
                $pengajuan = $assignment->pengajuan;
                $pengajuan->setValidatorAssigned($user->id);
            }

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
                'message' => 'Penawaran berhasil diterima. Anda dapat mulai melakukan ' . ($assignment->jenis_asesmen === 'dokumen' ? 'penilaian' : 'validasi'),
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
