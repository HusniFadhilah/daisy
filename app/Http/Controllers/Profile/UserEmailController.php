<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserEmailController extends Controller
{

    public function emailsIndex()
    {
        $user = Auth::user();

        $emails = $user->emails()
            ->orderByDesc('is_active')
            ->orderBy('email')
            ->get(['id', 'email', 'is_active', 'is_primary', 'verified_at', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'primary_email' => $user->email,
                'emails' => $emails,
            ],
        ]);
    }

    public function emailsStore(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        // Cegah email tambahan sama dengan email utama
        if ($email === strtolower($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ini sudah menjadi email utama.',
                'errors' => ['email' => ['Email ini sudah menjadi email utama.']],
            ], 422);
        }

        // Cegah duplikasi pada user yang sama
        if ($user->emails()->where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar.',
                'errors' => ['email' => ['Email sudah terdaftar.']],
            ], 422);
        }

        $row = $user->emails()->create([
            'email' => $email,
            'is_active' => true,
            'is_primary' => false,
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email tambahan berhasil ditambahkan.',
            'data' => $row->only(['id', 'email', 'is_active', 'is_primary', 'verified_at']),
        ]);
    }

    public function emailsToggle($id)
    {
        $user = Auth::user();

        $row = $user->emails()->findOrFail($id);

        // kalau Anda tidak ingin primary bisa dinonaktifkan (opsional)
        if ($row->is_primary) {
            return response()->json([
                'success' => false,
                'message' => 'Email primary tidak dapat diubah statusnya.',
            ], 422);
        }

        $row->is_active = !$row->is_active;
        $row->save();

        return response()->json([
            'success' => true,
            'message' => 'Status email diperbarui.',
            'data' => $row->only(['id', 'email', 'is_active', 'is_primary', 'verified_at']),
        ]);
    }

    public function emailsDestroy($id)
    {
        $user = Auth::user();

        $row = $user->emails()->findOrFail($id);

        if ($row->is_primary) {
            return response()->json([
                'success' => false,
                'message' => 'Email primary tidak dapat dihapus.',
            ], 422);
        }

        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil dihapus.',
        ]);
    }
}
