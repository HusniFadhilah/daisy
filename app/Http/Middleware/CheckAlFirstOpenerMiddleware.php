<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AsesmenUserRole;

class CheckAlFirstOpenerMiddleware
{
    /**
     * Session key pattern: al_opener_confirmed_{idAsesmen}
     * Di-set setelah user klik "Lanjutkan" di halaman peringatan.
     */
    public function handle(Request $request, Closure $next)
    {
        $user      = Auth::user();
        $idAsesmen = $request->route('idAsesmen') ?? $request->route('id');

        if (!$idAsesmen || !$user) {
            return $next($request);
        }

        $sessionKey = "al_opener_confirmed_{$idAsesmen}";

        // Sudah pernah klik "Lanjutkan" → lewat langsung
        if ($request->session()->has($sessionKey)) {
            return $next($request);
        }

        // Ambil assignment user ini
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->first();

        if (!$assignment) {
            return $next($request);
        }

        // Sudah pernah masuk sebelumnya (status bukan not_started) → lewat langsung
        if ($assignment->status_pekerjaan !== 'not_started') {
            return $next($request);
        }

        // ── Ini kunjungan pertama user ini ──────────────────────────────────
        // Cek apakah ada asesor lain yang sudah lebih dulu membuka
        $firstStartedByOther = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->where('id_user', '!=', $user->id)
            ->where('status_pekerjaan', '!=', 'not_started')
            ->with('user')
            ->orderBy('updated_at')
            ->first();

        // Tampilkan halaman peringatan sebelum masuk
        return response()->view('asesmen.al.components.first-opener-warning', [
            'idAsesmen'          => $idAsesmen,
            'isFirstOpener'      => is_null($firstStartedByOther),
            'firstOpenerUser'    => $firstStartedByOther?->user,
            'continueUrl'        => $request->fullUrl(), // URL asli yang dituju
            'confirmRoute'       => route('al.berkas.confirm-opener', ['idAsesmen' => $idAsesmen]),
        ]);
    }
}
