<?php
// app/Http/Middleware/EnsurePenawaranAcceptedMiddleware.php

namespace App\Http\Middleware;

use App\Models\AsesmenUserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsurePenawaranAcceptedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Supports 3 types of validation:
     * - AK: /ak/berkas/{idAsesmen} → jenis_asesmen = 'ak'
     * - AL: /al/berkas/{idAsesmen} → jenis_asesmen = 'al'
     * - Dokumen: /validator/dokumen/{assignment} → jenis_asesmen = 'dokumen'
     */
    public function handle(Request $request, Closure $next, $jenisAsesmen = 'ak')
    {
        // Skip if route is penawaran.* (avoid redirect loop)
        if ($request->routeIs('penawaran.*')) {
            return $next($request);
        }

        // $previousUrl = url()->previous();
        // Log::info('Previous URL: ' . $previousUrl);
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Tidak ada akses ke asesmen ini.');
        }

        // ========================================
        // DETERMINE: Asesmen ID vs Assignment ID
        // ========================================
        $assignment = null;

        // Case 1: Borang Validation (uses assignment ID directly)
        if ($jenisAsesmen === 'dokumen') {
            $assignmentId = $request->route('assignment');

            if (!$assignmentId) {
                abort(403, 'Parameter penugasan tidak ditemukan.');
            }

            // Find assignment by ID
            $assignment = AsesmenUserRole::where('id', $assignmentId)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'dokumen')
                ->first();

            if (!$assignment) {
                abort(403, 'Anda tidak memiliki akses ke validasi Dokumen ini.');
            }
        } else {
            // Case 2: AK/AL Validation (uses asesmen ID)
            $idAsesmen = $request->route('idAsesmen')
                ?? $request->route('asesmen')
                ?? $request->route('id');

            if (!$idAsesmen) {
                abort(403, 'Parameter asesmen tidak ditemukan.');
            }

            // Find assignment by asesmen ID
            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', $jenisAsesmen)
                ->where('id_user', $user->id)
                ->first();

            if (!$assignment) {
                abort(403, "Anda tidak memiliki penawaran untuk asesmen " . strtoupper($jenisAsesmen) . " ini.");
            }
        }

        // ========================================
        // VERIFY: User matches
        // ========================================
        if ($assignment->id_user != $user->id) {
            abort(403, 'Mohon maaf, Anda tidak diizinkan membuka halaman berikut');
        }

        // ========================================
        // CHECK: Penawaran Status
        // ========================================
        // enum: 'pending', 'accepted', 'rejected'
        if ($assignment->status_penawaran !== 'accepted') {
            // Redirect based on jenis_asesmen
            if ($jenisAsesmen === 'dokumen') {
                // For borang, use token-based penawaran route
                return redirect()
                    ->route('penawaran.show', ['token' => $assignment->token])
                    ->with('warning', 'Silakan terima penawaran validasi Dokumen terlebih dahulu.');
            } else {
                // For AK/AL, use existing cek penawaran route
                return redirect()
                    ->route('penawaran.berkas.cekPenawaran', ['idAsesmen' => $assignment->id_asesmen, 'jenisAsesmen' => 'dokumen'])
                    ->with('warning', 'Silakan respon penawaran asesmen ini terlebih dahulu.');
            }
        }

        // ========================================
        // PASS: Assignment to Controller
        // ========================================
        // Controller can access: $request->attributes->get('asesmen_assignment')
        $request->attributes->set('asesmen_assignment', $assignment);

        return $next($request);
    }
}
