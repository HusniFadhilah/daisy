<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PengajuanPembayaran;
use App\Support\PanduanLinks;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->role_selected === 'super_admin', 403);

        return view('admin.settings', [
            'panduanLinks' => PanduanLinks::all(),
            'paymentSettings' => [
                'biaya_akreditasi' => PengajuanPembayaran::biayaAkreditasi(),
                'biaya_banding' => PengajuanPembayaran::biayaBanding(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()?->role_selected === 'super_admin', 403);

        $rules = collect(config('panduan.roles', []))
            ->keys()
            ->mapWithKeys(fn (string $key) => ["panduan_links.{$key}" => ['required', 'url', 'max:2048']])
            ->all();

        $rules['biaya.akreditasi'] = ['required', 'integer', 'min:0'];
        $rules['biaya.banding'] = ['required', 'integer', 'min:0'];

        $validated = $request->validate($rules);

        foreach ($validated['panduan_links'] as $key => $url) {
            AppSetting::updateOrCreate(
                ['key' => PanduanLinks::settingKey($key)],
                ['value' => $url]
            );
        }

        AppSetting::updateOrCreate(
            ['key' => PengajuanPembayaran::SETTING_BIAYA_AKREDITASI],
            ['value' => (string) $validated['biaya']['akreditasi']]
        );

        AppSetting::updateOrCreate(
            ['key' => PengajuanPembayaran::SETTING_BIAYA_BANDING],
            ['value' => (string) $validated['biaya']['banding']]
        );

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
