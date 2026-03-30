<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AsesmenUserRole;
use App\Models\Role;

class CheckAlFirstOpenerMiddleware
{
    /**
     * Pakai parameter:
     * - al
     * - al_banding
     *
     * Contoh:
     *   ->middleware('al.first.opener:al')
     *   ->middleware('al.first.opener:al_banding')
     */
    public function handle(Request $request, Closure $next, string $jenisAsesmen = 'al')
    {
        $user      = Auth::user();
        $idAsesmen = $request->route('idAsesmen') ?? $request->route('id');

        if (!$idAsesmen || !$user) {
            return $next($request);
        }

        // Mapping konfigurasi per jenis asesmen
        $config = match ($jenisAsesmen) {
            'al_banding' => [
                'session_key'   => "al_banding_opener_confirmed_{$idAsesmen}",
                'role_id'       => Role::ID_ROLE_ASESOR_BANDING,
                'role_name'     => 'asesor_banding',
                'confirm_route' => 'al_banding.berkas.confirm-opener',
                'warning_view'  => 'asesmen.banding.al-banding.components.first-opener-warning',
            ],
            'al' => [
                'session_key'   => "al_opener_confirmed_{$idAsesmen}",
                'role_id'       => Role::ID_ROLE_ASESOR,
                'role_name'     => 'asesor',
                'confirm_route' => 'al.berkas.confirm-opener',
                'warning_view'  => 'asesmen.al.components.first-opener-warning',
            ],
            default => abort(500, "Jenis asesmen first opener tidak dikenali: {$jenisAsesmen}"),
        };

        $sessionKey = $config['session_key'];

        // Sudah pernah klik "Lanjutkan" → lewat langsung
        if ($request->session()->has($sessionKey)) {
            return $next($request);
        }

        // Ambil assignment user ini
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->where('id_role', $config['role_id'])
            ->first();

        if (!$assignment) {
            return $next($request);
        }

        // Sudah pernah mulai sebelumnya → lewat langsung
        if ($assignment->status_pekerjaan !== 'not_started') {
            return $next($request);
        }

        // Cek apakah ada asesor lain yang sudah lebih dulu membuka
        $firstStartedByOther = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->where('id_role', $config['role_id'])
            ->where('id_user', '!=', $user->id)
            ->where('status_pekerjaan', '!=', 'not_started')
            ->with('user')
            ->orderBy('updated_at')
            ->first();

        return response()->view($config['warning_view'], [
            'idAsesmen'       => $idAsesmen,
            'isFirstOpener'   => is_null($firstStartedByOther),
            'firstOpenerUser' => $firstStartedByOther?->user,
            'continueUrl'     => $request->fullUrl(),
            'confirmRoute'    => route($config['confirm_route'], ['idAsesmen' => $idAsesmen]),
            'jenisAsesmen'    => $jenisAsesmen,
        ]);
    }
}
