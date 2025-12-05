<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        // Tampilkan halaman settings
        return view('admin.settings');
    }

    public function update(Request $request)
    {
        // Contoh: simpan pengaturan
        $request->validate([
            'site_name' => 'required|string|max:255',
            'email_notifications' => 'nullable|boolean',
        ]);

        // Simulasi update settings (bisa diganti sesuai kebutuhan)
        // Misal simpan ke DB atau config
        // Setting::updateOrCreate(...)

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}
