<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // FORM FORGOT PASSWORD
    public function showForgot()
    {
        return view('auth.forgot');
    }

    // KIRIM LINK RESET PASSWORD
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan']);
        }

        $token = Str::random(64);

        // Simpan token
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Kirim email
        Mail::send('emails.password_reset', ['token' => $token], function ($m) use ($request) {
            $m->to($request->email)->subject('Reset Password');
        });

        return back()->with('success', 'Link reset password sudah dikirim ke email.');
    }

    // FORM RESET PASSWORD
    public function showReset($token)
    {
        return view('auth.reset', ['token' => $token]);
    }

    // PROSES RESET PASSWORD
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => 'Token tidak valid atau kadaluarsa']);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password berhasil direset!');
    }
}
