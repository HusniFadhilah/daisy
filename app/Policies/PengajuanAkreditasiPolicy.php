<?php

namespace App\Policies;

use App\Models\PengajuanAkreditasi;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengajuanAkreditasiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view any pengajuan
     */
    public function viewAny(User $user)
    {
        // Prodi can view their own, DE can view assigned
        return $user->hasRole(['admin_prodi', 'asesi', 'super_admin']);
    }

    /**
     * Determine if user can view the pengajuan
     */
    public function view(User $user, PengajuanAkreditasi $pengajuan)
    {
        // Prodi can view if it's their program studi
        if ($user->hasRole('admin_prodi')) {
            return $pengajuan->studyProgram->users->contains($user->id); // ✅ FIX
        }

        // DE can view if assigned
        // if ($user->hasRole('asesi')) {
        //     return $pengajuan->id_de_assigned === $user->id ||$pengajuan->id_validator_assigned === $user->id;
        // }

        // Admin can view all
        return $user->hasRole(['asesi', 'super_admin']);
    }

    /**
     * Determine if user can create pengajuan
     */
    public function create(User $user)
    {
        return $user->hasRole('admin_prodi');
    }

    /**
     * Determine if user can update the pengajuan
     */
    public function update(User $user, PengajuanAkreditasi $pengajuan)
    {
        // Only prodi from the same program studi can update
        if ($user->hasRole('admin_prodi')) {
            return $pengajuan->studyProgram->users->contains($user->id); // ✅ FIX
        }

        return false;
    }

    /**
     * Determine if user can delete the pengajuan
     */
    public function delete(User $user, PengajuanAkreditasi $pengajuan)
    {
        // Only admin or prodi (if status still draft) can delete
        if ($user->hasRole('admin_prodi')) {
            return true;
        }

        if ($user->hasRole('admin_prodi')) {
            return $pengajuan->studyProgram->users->contains($user->id) // ✅ FIX
                && in_array($pengajuan->status, ['pengingat_dikirim', 'surat_permohonan_diterima']);
        }

        return false;
    }

    /**
     * Determine if user can review (DE only)
     */
    public function review(User $user, PengajuanAkreditasi $pengajuan)
    {
        return $user->hasRole(['asesi', 'super_admin', 'validator']) && $pengajuan->id_de_assigned === $user->id || $pengajuan->id_validator_assigned === $user->id;
    }

    /**
     * Determine if user can verify payment (DE only)
     */
    public function verifyPayment(User $user, PengajuanAkreditasi $pengajuan)
    {
        return $user->hasRole(['asesi', 'super_admin', 'validator', 'keuangan_lamdepilar']) && $pengajuan->id_de_assigned === $user->id || $pengajuan->id_validator_assigned === $user->id;
    }

    /**
     * Determine if user can approve to AK (DE only)
     */
    public function approveToAK(User $user, PengajuanAkreditasi $pengajuan)
    {
        return $user->hasRole(['asesi', 'super_admin', 'validator']) && $pengajuan->id_de_assigned === $user->id || $pengajuan->id_validator_assigned === $user->id;
    }

    /**
     * Determine if user can reset borang
     */
    public function resetBorang(User $user, PengajuanAkreditasi $pengajuan)
    {
        // Only prodi who owns this Permohonan akreditasican reset
        if (in_array(!$user->role_selected, ['admin_prodi', 'admin_univ'])) {
            return false;
        }

        $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();

        return in_array($pengajuan->id_program_studi, $userStudyProgramIds);
    }
}
