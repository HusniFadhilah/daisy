<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Support\PanduanLinks;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->role_selected === 'super_admin', 403);

        return view('admin.settings', [
            'panduanLinks' => PanduanLinks::all(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()?->role_selected === 'super_admin', 403);

        $rules = collect(config('panduan.roles', []))
            ->keys()
            ->mapWithKeys(fn (string $key) => ["panduan_links.{$key}" => ['required', 'url', 'max:2048']])
            ->all();

        $validated = $request->validate($rules);

        foreach ($validated['panduan_links'] as $key => $url) {
            AppSetting::updateOrCreate(
                ['key' => PanduanLinks::settingKey($key)],
                ['value' => $url]
            );
        }

        return back()->with('success', 'Link panduan berhasil disimpan.');
    }
}
