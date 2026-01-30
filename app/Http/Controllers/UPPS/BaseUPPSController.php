<?php
// app/Http/Controllers/UPPS/BaseUPPSController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

abstract class BaseUPPSController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get study program IDs for current user
     */
    protected function getStudyProgramIds()
    {
        return Auth::user()->studyPrograms()->pluck('study_programs.id');
    }

    /**
     * Check if user has access to pengajuan
     */
    protected function checkAccess(PengajuanAkreditasi $pengajuan): bool
    {
        $studyProgramIds = $this->getStudyProgramIds();

        return $studyProgramIds->contains($pengajuan->id_program_studi)
            || $pengajuan->id_user_pengaju === Auth::id()
            || Auth::user()->hasRole('super_admin');
    }

    /**
     * Authorize pengajuan access
     */
    protected function authorizeAccess(PengajuanAkreditasi $pengajuan): void
    {
        if (!$this->checkAccess($pengajuan)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan akreditasi ini.');
        }
    }

    /**
     * Get breadcrumb for step
     */
    protected function getBreadcrumb(string $stepName, PengajuanAkreditasi $pengajuan): array
    {
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Permohonan Akreditasi', 'url' => route('pengajuan.index')],
        ];

        if ($pengajuan) {
            $breadcrumb[] = [
                'label' => $pengajuan->nomor_pengajuan,
                'url' => route('pengajuan.show', $pengajuan->id)
            ];
        }

        $breadcrumb[] = ['label' => $stepName, 'url' => null];

        return $breadcrumb;
    }

    /**
     * Log status change
     */
    protected function logStatus(
        PengajuanAkreditasi $pengajuan,
        string $oldStatus,
        string $newStatus,
        ?string $keterangan = null
    ): void {
        $pengajuan->statusLog()->create([
            'status_from' => $oldStatus,
            'status_to' => $newStatus,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
            'keterangan' => $keterangan,
        ]);
    }
}
