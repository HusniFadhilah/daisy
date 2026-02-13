<?php
// app/Http/Controllers/Master/JenjangPenilaianController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\JenjangPenilaian;
use App\Services\HasilAkreditasiSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenjangPenilaianController extends Controller
{
    protected $syncService;

    public function __construct(HasilAkreditasiSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    // ... existing CRUD methods ...

    /**
     * ✅ Update jenjang penilaian
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'skor' => 'required|integer|min:0|max:4',
        ]);

        DB::beginTransaction();
        try {
            $jenjang = JenjangPenilaian::findOrFail($id);

            // Check if color or name changed
            $colorChanged = $jenjang->color !== $request->color;
            $nameChanged = $jenjang->name !== $request->name;

            $jenjang->update($request->only(['name', 'color', 'skor']));

            DB::commit();

            // If color or name changed, show option to sync
            if ($colorChanged || $nameChanged) {
                return redirect()
                    ->route('master-data.jenjang-penilaian.index')
                    ->with('success', 'Jenjang penilaian berhasil diupdate!')
                    ->with('show_sync_option', true)
                    ->with('jenjang_id', $id);
            }

            return redirect()
                ->route('master-data.jenjang-penilaian.index')
                ->with('success', 'Jenjang penilaian berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Trigger sync semua hasil akreditasi
     */
    public function syncAllHasilAkreditasi()
    {
        try {
            $result = $this->syncService->syncAllSkorKategori();

            $message = "Sync selesai! Updated: {$result['updated']}, Failed: {$result['failed']}";

            if ($result['failed'] > 0) {
                return back()->with('warning', $message);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal sync: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Show sync confirmation page
     */
    public function showSyncConfirmation()
    {
        // Get stats
        $totalHasil = \App\Models\HasilAkreditasi::whereNotNull('detail_skor_ak')
            ->orWhereNotNull('detail_skor_al')
            ->count();

        return view('master-data.jenjang-penilaian.sync-confirmation', compact('totalHasil'));
    }
}
