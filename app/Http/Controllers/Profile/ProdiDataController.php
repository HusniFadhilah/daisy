<?php

namespace App\Http\Controllers\Profile;

use App\Models\University;
use Illuminate\Support\Str;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProdiDataController extends Controller
{
    /**
     * Display prodi data form
     */
    public function index()
    {
        $user = Auth::user();

        // Check if user is admin_prodi or admin_univ
        if (!in_array($user->role_selected, ['admin_prodi', 'admin_univ'])) {
            abort(403, 'Unauthorized access');
        }

        $university = $user->university;

        // Get study programs based on role
        if ($user->role_selected === 'admin_prodi') {
            // UPPS: Get all study programs they manage
            $studyPrograms = $user->studyPrograms()
                ->with(['degreeLevel', 'university'])
                ->orderBy('id_degree_level')
                ->get();
        } else {
            // Admin Univ: Get all study programs in their university
            $studyPrograms = StudyProgram::where('id_university', $user->id_university)
                ->with(['degreeLevel'])
                ->orderBy('id_degree_level')
                ->get();
        }

        return view('profile.prodi-data', compact('user', 'university', 'studyPrograms'));
    }

    /**
     * Update university logo
     */
    public function updateLogo(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->id_university) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak terikat dengan universitas manapun.',
                ], 403);
            }

            $request->validate([
                'logo' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048', // 2MB max
            ]);

            $university = University::findOrFail($user->id_university);

            // Delete old logo if exists
            if ($university->logo_path && Storage::disk('public')->exists($university->logo_path)) {
                Storage::disk('public')->delete($university->logo_path);
            }

            // Store new logo
            $fileName = $university->id . '_' . Str::slug($university->name) . '_' . time() . '.' . $request->file('logo')->getClientOriginalExtension();
            $path = $request->file('logo')->storeAs('universities/logo', $fileName, 'public');

            $university->update([
                'logo_path' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logo universitas berhasil diperbarui!',
                'logo_url' => asset('storage/' . $path),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Logo validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Pastikan file adalah gambar dengan format JPG, PNG, atau GIF (max 2MB).',
            ], 422);
        } catch (\Exception $e) {
            Log::error('Logo upload error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'university_id' => Auth::user()->id_university ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupload logo. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Update university LPM data
     */
    public function updateUniversity(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->id_university) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak terikat dengan universitas manapun.',
                ], 403);
            }

            $request->validate([
                'lpm_name' => 'nullable|string|max:255',
                'lpm_phone' => 'nullable|string|max:20',
                'lpm_mobile' => 'nullable|string|max:20',
                'lpm_email' => 'nullable|email|max:255',
            ]);

            $university = University::findOrFail($user->id_university);

            $university->update([
                'lpm_name' => $request->lpm_name,
                'lpm_phone' => $request->lpm_phone,
                'lpm_mobile' => $request->lpm_mobile,
                'lpm_email' => $request->lpm_email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Lembaga Penjaminan Mutu berhasil diperbarui!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('LPM validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa kembali data yang diisi.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('LPM update error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'university_id' => $user->id_university ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Update study program data
     */
    public function updateStudyProgram(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'ketua_prodi_name' => 'nullable|string|max:255',
                'ketua_prodi_nip' => 'nullable|string|max:50',
                'ketua_tim_akreditasi' => 'nullable|string|max:255',
                'akreditasi_phone' => 'nullable|string|max:20',
                'akreditasi_mobile' => 'nullable|string|max:20',
                'akreditasi_email' => 'nullable|email|max:255',
            ]);

            // Check authorization
            if ($user->role_selected === 'admin_prodi') {
                // Check if this study program is managed by this UPPS
                $studyProgram = $user->studyPrograms()->where('study_programs.id', $id)->first();

                if (!$studyProgram) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke program studi ini.',
                    ], 403);
                }
            } else {
                // Admin Univ: Check if study program belongs to their university
                $studyProgram = StudyProgram::where('id', $id)
                    ->where('id_university', $user->id_university)
                    ->first();

                if (!$studyProgram) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program studi tidak ditemukan atau bukan bagian dari universitas Anda.',
                    ], 404);
                }
            }

            $studyProgram->update([
                'ketua_prodi_name' => $request->ketua_prodi_name,
                'ketua_prodi_nip' => $request->ketua_prodi_nip,
                'ketua_tim_akreditasi' => $request->ketua_tim_akreditasi,
                'akreditasi_phone' => $request->akreditasi_phone,
                'akreditasi_mobile' => $request->akreditasi_mobile,
                'akreditasi_email' => $request->akreditasi_email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data program studi ' . $studyProgram->name . ' berhasil diperbarui!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Study program validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa kembali data yang diisi.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('Study program update error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'study_program_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.',
            ], 500);
        }
    }
}
