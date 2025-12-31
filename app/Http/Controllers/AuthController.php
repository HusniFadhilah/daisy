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

            // If user has multiple roles, redirect to role selection
            if ($user->hasMultipleRoles()) {
                return redirect()->route('select.role');
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

        if ($user->switchRole($request->role)) {
            return redirect()->intended('/dashboard')->with('success', 'Role aktif: ' . $user->role_alias);
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
}
