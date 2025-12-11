<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenUserRole;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsesmenController extends Controller
{
    /**
     * Display a listing of asesmen (Admin view)
     */
    public function index()
    {
        $asesmens = Asesmen::with(['userRoles.user', 'userRoles.role'])
            ->withCount('userRoles')
            ->latest()
            ->paginate(15);

        return view('asesmen.index', compact('asesmens'));
    }

    /**
     * Show the form for creating a new asesmen
     */
    public function create()
    {
        return view('asesmen.form');
    }

    /**
     * Store a newly created asesmen
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'perguruan_tinggi' => 'nullable|string|max:255',
            'bentuk_pt' => 'nullable|string|max:100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $asesmen = Asesmen::create($request->all());

            return redirect()
                ->route('asesmen.show', $asesmen->id)
                ->with('success', 'Asesmen berhasil dibuat. Silakan assign asesor.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified asesmen with assigned users
     */
    public function show($id)
    {
        $asesmen = Asesmen::with([
            'userRoles.user',
            'userRoles.role',
            'penilaianElemen'
        ])->findOrFail($id);

        // Get users who are NOT assigned yet
        $assignedUserIds = $asesmen->userRoles->pluck('id_user')->toArray();
        $availableUsers = User::whereNotIn('id', $assignedUserIds)
            ->where('role', '!=', 'admin') // Exclude admin from asesor list
            ->orderBy('name')
            ->get();

        // Get all roles
        $roles = Role::orderBy('name')->get();

        // Calculate completion stats per user
        $userStats = [];
        foreach ($asesmen->userRoles as $userRole) {
            $stats = $this->calculateUserProgress($asesmen->id, $userRole->id_user);
            $userStats[$userRole->id_user] = $stats;
        }

        return view('asesmen.show', compact('asesmen', 'availableUsers', 'roles', 'userStats'));
    }

    /**
     * Show the form for editing asesmen
     */
    public function edit($id)
    {
        $asesmen = Asesmen::findOrFail($id);
        return view('asesmen.form', compact('asesmen'));
    }

    /**
     * Update the specified asesmen
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'perguruan_tinggi' => 'nullable|string|max:255',
            'bentuk_pt' => 'nullable|string|max:100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $asesmen = Asesmen::findOrFail($id);
            $asesmen->update($request->all());

            return redirect()
                ->route('asesmen.show', $asesmen->id)
                ->with('success', 'Asesmen berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal update asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Assign user (asesor) to asesmen
     */
    public function assignUser(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_role' => 'required|exists:roles,id',
        ]);

        try {
            $asesmen = Asesmen::findOrFail($id);

            // Check if user already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $id)
                ->where('id_user', $request->id_user)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User sudah di-assign ke asesmen ini'
                ], 422);
            }

            // Create assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $id,
                'id_user' => $request->id_user,
                'id_role' => $request->id_role,
            ]);

            $user = User::find($request->id_user);
            $role = Role::find($request->id_role);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil di-assign sebagai {$role->name}",
                'data' => [
                    'assignment' => $assignment,
                    'user' => $user,
                    'role' => $role,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user role in asesmen
     */
    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'assignment_id' => 'required|exists:asesmen_user_roles,id',
            'id_role' => 'required|exists:roles,id',
        ]);

        try {
            $assignment = AsesmenUserRole::findOrFail($request->assignment_id);

            // Verify assignment belongs to this asesmen
            if ($assignment->id_asesmen != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment tidak valid'
                ], 422);
            }

            $assignment->update(['id_role' => $request->id_role]);

            $role = Role::find($request->id_role);

            return response()->json([
                'success' => true,
                'message' => "Role berhasil diupdate menjadi {$role->name}",
                'data' => [
                    'assignment' => $assignment,
                    'role' => $role,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user from asesmen
     */
    public function removeUser($id, $userId)
    {
        try {
            $asesmen = Asesmen::findOrFail($id);

            // Check if user has any penilaian
            $hasPenilaian = DB::table('penilaian_elemen')
                ->where('id_asesmen', $id)
                ->where('id_asesor', $userId)
                ->exists();

            if ($hasPenilaian) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak bisa dihapus karena sudah melakukan penilaian. Hapus penilaian terlebih dahulu.'
                ], 422);
            }

            // Delete assignment
            $deleted = AsesmenUserRole::where('id_asesmen', $id)
                ->where('id_user', $userId)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dihapus dari asesmen'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete asesmen (with confirmation)
     */
    public function destroy($id)
    {
        try {
            $asesmen = Asesmen::findOrFail($id);

            // Check if has penilaian
            $hasPenilaian = $asesmen->penilaianElemen()->exists();

            if ($hasPenilaian) {
                return redirect()
                    ->back()
                    ->with('error', 'Asesmen tidak bisa dihapus karena sudah ada penilaian.');
            }

            $asesmen->delete();

            return redirect()
                ->route('asesmen.index')
                ->with('success', 'Asesmen berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal hapus asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign users to asesmen
     */
    public function bulkAssign(Request $request, $id)
    {
        $request->validate([
            'id_users' => 'required|array',
            'id_users.*' => 'exists:users,id',
            'id_role' => 'required|exists:roles,id',
        ]);

        try {
            $asesmen = Asesmen::findOrFail($id);
            $assignedCount = 0;
            $skippedCount = 0;

            DB::beginTransaction();

            foreach ($request->id_users as $userId) {
                // Check if already assigned
                $exists = AsesmenUserRole::where('id_asesmen', $id)
                    ->where('id_user', $userId)
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                AsesmenUserRole::create([
                    'id_asesmen' => $id,
                    'id_user' => $userId,
                    'id_role' => $request->id_role,
                ]);

                $assignedCount++;
            }

            DB::commit();

            $message = "Berhasil assign {$assignedCount} user.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} user di-skip (sudah di-assign).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'assigned' => $assignedCount,
                    'skipped' => $skippedCount,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk assign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate progress for specific user
     */
    private function calculateUserProgress($idAsesmen, $userId)
    {
        $totalElemens = DB::table('elemen_standar')->count();

        $completedElemens = DB::table('penilaian_elemen')
            ->where('id_asesmen', $idAsesmen)
            ->where('id_asesor', $userId)
            ->whereNotNull('skor')
            ->count();

        $percentage = $totalElemens > 0
            ? round(($completedElemens / $totalElemens) * 100, 1)
            : 0;

        return [
            'total' => $totalElemens,
            'completed' => $completedElemens,
            'percentage' => $percentage,
            'remaining' => $totalElemens - $completedElemens,
        ];
    }

    /**
     * Dashboard - Overview semua asesmen
     */
    public function dashboard()
    {
        $stats = [
            'total_asesmens' => Asesmen::count(),
            'active_asesmens' => Asesmen::whereHas('userRoles')->count(),
            'total_asesor' => User::where('role', 'asesor')->count(),
            'completed_asesmens' => 0, // Will calculate based on completion
        ];

        // Recent asesmen
        $recentAsesmens = Asesmen::with(['userRoles.user'])
            ->withCount('userRoles')
            ->latest()
            ->take(5)
            ->get();

        // Most active asesor
        $activeAsesor = User::select('users.*', DB::raw('COUNT(asesmen_user_roles.id) as asesmen_count'))
            ->join('asesmen_user_roles', 'users.id', '=', 'asesmen_user_roles.id_user')
            ->groupBy('users.id')
            ->orderByDesc('asesmen_count')
            ->take(5)
            ->get();

        return view('asesmen.dashboard', compact('stats', 'recentAsesmens', 'activeAsesor'));
    }

    /**
     * Search users for assignment (AJAX)
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $idAsesmen = $request->get('id_asesmen');

        // Get already assigned users
        $assignedUserIds = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->pluck('id_user')
            ->toArray();

        // Search available users
        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->whereNotIn('id', $assignedUserIds)
            ->where('role', '!=', 'admin')
            ->limit(10)
            ->get(['id', 'name', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
