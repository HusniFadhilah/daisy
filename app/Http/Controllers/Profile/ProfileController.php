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
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6'
        ]);

        // Update default field
        $user->name  = $request->name;
        $user->email = $request->email;

        // Jika password diisi, update
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

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
}
