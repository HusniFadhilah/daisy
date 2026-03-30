<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if (app()->environment('production')) {
            $rules['captcha'] = 'required|captcha';
        }

        $request->validate($rules);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Sync roles from assignments after login
            $user = Auth::user();
            $user->syncRolesFromAssignments();
            $user = $user->fresh();

            // If user has multiple roles, redirect to role selection
            if ($user->hasMultipleRoles()) {
                return redirect()->route('select.role')
                    ->with('success', 'Login berhasil! Silakan pilih role Anda.');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ])->withInput($request->only('email'));
    }

    /**
     * Show role selection page
     */
    public function showRoleSelection()
    {
        $user = Auth::user();
        $user->syncRolesFromAssignments();

        // If only one role, redirect to dashboard
        if (!$user->hasMultipleRoles()) {
            return redirect('/dashboard');
        }
        $user->syncRolesFromAssignments();
        $user = $user->fresh();
        return view('auth.select-role', [
            'roles' => $user->available_roles,
            'current_role' => $user->role_selected,
        ]);
    }

    /**
     * Select role and redirect
     */
    public function selectRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user->hasMultipleRoles()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda hanya memiliki 1 role, tidak dapat memilih role lain.');
        }
        $roleName = $request->role;

        if ($user->switchRole($roleName)) {
            return redirect()->route('dashboard')
                ->with('success', 'Role berhasil diubah ke: ' . $user->role_alias);
        }

        return back()->with('error', 'Role tidak valid');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'default',
            'role_selected' => 'default',
            'roles' => ['default'],
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silahkan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Show change password form for first login
     */
    public function showChangePasswordFirst()
    {
        return view('auth.change-password-first');
    }

    /**
     * Update password on first login
     */
    public function changePasswordFirst(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Password lama harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai']);
        }

        // Update password and remove must_change_password flag
        $user->must_change_password = false;
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Force flush Auth dan reload user dari database
        Auth::logout();
        Auth::loginUsingId($user->id);

        // Redirect based on user roles
        $user = Auth::user(); // Ambil user fresh setelah login
        if ($user->hasMultipleRoles()) {
            return redirect()->route('select.role')
                ->with('success', 'Password berhasil diubah! Silakan pilih role Anda.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Password berhasil diubah!');
    }

    /**
     * Skip change password (optional)
     */
    public function skipChangePassword(Request $request)
    {
        $user = Auth::user();

        // Set flag to false to skip next time
        $user->must_change_password = false;
        $user->save();

        // Force flush Auth dan reload user dari database
        Auth::logout();
        Auth::loginUsingId($user->id);

        // Redirect based on user roles
        $user = Auth::user(); // Ambil user fresh setelah login
        if ($user->hasMultipleRoles()) {
            return redirect()->route('select.role')
                ->with('info', 'Anda dapat mengganti password nanti dari menu profil.');
        }

        return redirect()->route('dashboard')
            ->with('info', 'Anda dapat mengganti password nanti dari menu profil.');
    }
}
