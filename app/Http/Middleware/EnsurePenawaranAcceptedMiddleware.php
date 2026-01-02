<?php

namespace App\Http\Middleware;

use App\Models\AsesmenUserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePenawaranAcceptedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $idAsesmen = $request->route('idAsesmen') ?? $request->route('asesmen') ?? $request->route('id'); // dari /berkas/{id}

        if (!$user || !$idAsesmen) {
            abort(403, 'Tidak ada akses ke asesmen ini.');
        }

        // Cari penawaran / assignment user untuk asesmen ini
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->firstOrFail();

        if (!$assignment) {
            // Tidak ada penawaran sama sekali
            abort(403, 'Anda tidak memiliki penawaran untuk asesmen ini.');
        }

        if ($assignment->id_user != $user->id) abort(403, 'Mohon maaf, Anda tidak diizinkan membuka halaman berikut');

        // enum: 'pending', 'accepted', 'rejected'
        if ($assignment->status_penawaran !== 'accepted') {
            // Belum accept (pending / rejected) → arahkan ke halaman cek penawaran
            return redirect()
                ->route('ak.berkas.penawaran', $idAsesmen)
                ->with('warning', 'Silakan respon penawaran asesmen ini terlebih dahulu.');
        }

        // Opsional: kirim assignment ke controller biar bisa dipakai
        $request->attributes->set('asesmen_assignment', $assignment);

        return $next($request);
    }
}
