<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['university', 'studyProgram.degreeLevel', 'studyProgram.category']);
        
        // Load list untuk dropdown
        $universities = \App\Models\University::orderBy('name')->get();
        $studyPrograms = \App\Models\StudyProgram::with(['university', 'degreeLevel'])->orderBy('name')->get();
        
        return view('profile.index', compact('user', 'universities', 'studyPrograms'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'id_university' => 'nullable|exists:universities,id',
            'id_study_program' => 'nullable|exists:study_programs,id',
            'position' => 'nullable|string|max:255',
        ]);

        // Update fields
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->institution = $request->institution;
        $user->id_university = $request->id_university;
        $user->id_study_program = $request->id_study_program;
        $user->position = $request->position;

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function passwordForm()
    {
        return view('profile.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * Switch user role
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $user = Auth::user();

        // Sync roles first
        $user->syncRolesFromAssignments();

        // Switch role
        if ($user->switchRole($request->role)) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diubah ke: ' . $user->role_alias,
                'role' => $user->role_selected,
                'role_alias' => $user->role_alias,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Role tidak valid atau Anda tidak memiliki akses ke role ini',
        ], 403);
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles()
    {
        $user = Auth::user();
        $user->syncRolesFromAssignments();

        return response()->json([
            'success' => true,
            'roles' => $user->available_roles,
            'current_role' => $user->role_selected,
            'current_role_alias' => $user->role_alias,
        ]);
    }

    /**
     * Upload avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048', // 2MB max
        ]);

        try {
            $user = Auth::user();

            // Delete old avatar if exists
            if ($user->avatar && \Storage::exists('public/' . $user->avatar)) {
                \Storage::delete('public/' . $user->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            
            $user->avatar = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar berhasil diupdate',
                'avatar_url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload avatar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
