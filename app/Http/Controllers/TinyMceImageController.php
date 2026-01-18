<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TinyMceImageController extends Controller
{
    public function upload(Request $request, $folder, $id)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:5120', // 5MB
        ]);

        $file = $request->file('image');

        // contoh path: borang/1234/xxxx.webp
        $path = $file->store("tinymce/{$folder}/{$id}", 'public');

        $url = Storage::disk('public')->url($path);

        // (opsional) simpan ke DB: pengajuan_id, path, url, dll

        return response()->json([
            'location' => $url, // ini wajib key "location" untuk TinyMCE
        ]);
    }

    public function delete(Request $request, $folder, $id)
    {
        $request->validate([
            'src' => 'required|string',
        ]);

        $src = $request->input('src');

        // pastikan ini URL milik storage public kamu
        $publicBase = Storage::disk('public')->url('');
        if (!Str::startsWith($src, $publicBase)) {
            return response()->json(['message' => 'Invalid src'], 422);
        }

        // convert URL -> relative path di disk public
        $relative = Str::after($src, $publicBase); // mis: borang/1234/abc.png

        // keamanan tambahan: pastikan path sesuai pengajuanId
        if (!Str::startsWith($relative, "tinymce/{$folder}/{$id}/")) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Storage::disk('public')->delete($relative);

        // (opsional) hapus record DB juga

        return response()->json(['success' => true]);
    }
}
