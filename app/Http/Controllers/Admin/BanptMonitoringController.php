<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BanptAccreditationChange;
use App\Models\BanptSyncRun;
use App\Services\Banpt\BanptAccreditationSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanptMonitoringController extends Controller
{
    public function __construct(private readonly BanptAccreditationSyncService $syncService) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $changesQuery = BanptAccreditationChange::with(['studyProgram.university', 'studyProgram.degreeLevel', 'appliedBy', 'ignoredBy'])
            ->orderByDesc('detected_at');

        $pendingCount  = (clone $changesQuery)->where('status', BanptAccreditationChange::STATUS_PENDING)->count();
        $conflictCount = (clone $changesQuery)->where('status', BanptAccreditationChange::STATUS_CONFLICT)->count();

        $changes = match ($tab) {
            'conflict' => (clone $changesQuery)->where('status', BanptAccreditationChange::STATUS_CONFLICT)->paginate(20),
            'applied'  => (clone $changesQuery)->where('status', BanptAccreditationChange::STATUS_APPLIED)->paginate(20),
            'ignored'  => (clone $changesQuery)->where('status', BanptAccreditationChange::STATUS_IGNORED)->paginate(20),
            'runs'     => null,
            default    => (clone $changesQuery)->where('status', BanptAccreditationChange::STATUS_PENDING)->paginate(20),
        };

        $syncRuns = null;
        if ($tab === 'runs') {
            $syncRuns = BanptSyncRun::orderByDesc('started_at')->paginate(20);
        }

        $lastRun = BanptSyncRun::orderByDesc('started_at')->first();

        return view('admin.monitoring-banpt.index', compact(
            'tab', 'changes', 'syncRuns', 'lastRun', 'pendingCount', 'conflictCount'
        ));
    }

    public function show(BanptAccreditationChange $change)
    {
        $change->load(['studyProgram.university', 'studyProgram.degreeLevel', 'syncRun', 'appliedBy', 'ignoredBy']);

        $studyProgram  = $change->studyProgram;
        $blockReason   = $studyProgram ? $this->syncService->hasBlockingDaisyProcess($studyProgram) : null;

        return view('admin.monitoring-banpt.show', compact('change', 'studyProgram', 'blockReason'));
    }

    public function apply(BanptAccreditationChange $change)
    {
        try {
            $this->syncService->applyChange($change, Auth::user());

            $change->refresh();
            if ($change->isConflict()) {
                return back()->with('warning', 'Tidak dapat diterapkan: ' . $change->conflict_reason);
            }

            return back()->with('success', 'Perubahan berhasil diterapkan ke data program studi.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function ignore(BanptAccreditationChange $change)
    {
        try {
            $this->syncService->ignoreChange($change, Auth::user());
            return back()->with('success', 'Perubahan diabaikan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function conflict(Request $request, BanptAccreditationChange $change)
    {
        $request->validate([
            'conflict_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->syncService->markConflict($change, Auth::user(), $request->conflict_reason);
            return back()->with('success', 'Perubahan ditandai sebagai konflik.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function syncNow()
    {
        // Cegah jalankan jika ada sync sedang running
        $running = BanptSyncRun::where('status', BanptSyncRun::STATUS_RUNNING)->exists();
        if ($running) {
            return back()->with('warning', 'Sinkronisasi sedang berjalan. Silakan tunggu hingga selesai.');
        }

        try {
            $run = $this->syncService->syncAll();
            return back()->with('success', "Sinkronisasi selesai. Diperiksa: {$run->checked_count}, perubahan: {$run->changed_count}, error: {$run->error_count}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }
    }
}
