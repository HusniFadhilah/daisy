<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserEmail;
use App\Models\StudyProgramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with(['university', 'studyProgram'])->select('users.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('role', function ($row) {
                    $class = $row->role === 'admin' ? 'danger' : 'primary';
                    return '<span class="badge bg-' . $class . '">' . ucfirst($row->role) . '</span>';
                })
                ->addColumn('role_alias', function ($row) {
                    return $row->role_alias;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->locale('id')->translatedFormat('d M Y') : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('users.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>';
                    if ($row->id !== auth()->id()) {
                        $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id . ')" title="Hapus"><i class="bi bi-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['role', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $universities = \App\Models\University::orderBy('name')->get();
        $studyPrograms = \App\Models\StudyProgram::with(['degreeLevel', 'category'])->orderBy('name')->get();
        return view('admin.users.create', compact('universities', 'studyPrograms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'role_selected' => 'required|in:super_admin,sekretariat,keuangan_lamdepilar,asesor,asesor_banding,validator,admin_prodi',
            'roles' => 'nullable|array',
            'roles.*' => 'in:super_admin,sekretariat,keuangan_lamdepilar,asesor,asesor_banding,validator,admin_prodi',
            'notification_emails' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'id_university' => 'nullable|exists:universities,id',
            'id_study_programs' => 'nullable|array',
            'id_study_programs.*' => 'exists:study_programs,id',
            'position' => 'nullable|string|max:255',
        ]);

        // Set roles - jika tidak diisi, gunakan role_selected sebagai default
        $roles = $request->roles ?? [$validated['role_selected']];

        // Pastikan role_selected ada di dalam roles
        if (!in_array($validated['role_selected'], $roles)) {
            $roles[] = $validated['role_selected'];
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['roles'] = array_values(array_unique($roles));
        $validated['is_multiple_role'] = count($validated['roles']) > 1;
        $validated['must_change_password'] = true; // Admin create user, set true agar user ganti password

        $notificationEmails = $this->parseNotificationEmails($request->input('notification_emails'), $validated['email']);
        unset($validated['notification_emails']);
        $studyProgramIds = $this->normalizeStudyProgramIds($request->input('id_study_programs', []));
        unset($validated['id_study_programs']);
        $this->validateAdminProdiStudyPrograms($validated['roles'], $studyProgramIds);
        $validated['id_study_program'] = $studyProgramIds[0] ?? null;

        $user = User::create($validated);
        $this->syncNotificationEmails($user, $validated['email'], $notificationEmails);
        $this->syncAdminProdiStudyPrograms($user, $studyProgramIds, in_array('admin_prodi', $validated['roles'], true));

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with([
            'university',
            'studyProgram',
            'emails',
            'studyPrograms' => fn($query) => $query->wherePivot('is_active', true)->with('degreeLevel'),
        ])->findOrFail($id);
        $universities = \App\Models\University::orderBy('name')->get();
        $studyPrograms = \App\Models\StudyProgram::with(['degreeLevel', 'category'])->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'universities', 'studyPrograms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,user',
            'role_selected' => 'required|in:super_admin,sekretariat,keuangan_lamdepilar,asesor,asesor_banding,validator,admin_prodi',
            'roles' => 'nullable|array',
            'roles.*' => 'in:super_admin,sekretariat,keuangan_lamdepilar,asesor,asesor_banding,validator,admin_prodi',
            'notification_emails' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'id_university' => 'nullable|exists:universities,id',
            'id_study_programs' => 'nullable|array',
            'id_study_programs.*' => 'exists:study_programs,id',
            'position' => 'nullable|string|max:255',
        ]);

        // Set roles - jika tidak diisi, gunakan role_selected sebagai default
        $roles = $request->roles ?? [$validated['role_selected']];

        // Pastikan role_selected ada di dalam roles
        if (!in_array($validated['role_selected'], $roles)) {
            $roles[] = $validated['role_selected'];
        }

        $notificationEmails = $this->parseNotificationEmails($request->input('notification_emails'), $validated['email']);
        unset($validated['notification_emails']);
        $studyProgramIds = $this->normalizeStudyProgramIds($request->input('id_study_programs', []));
        unset($validated['id_study_programs']);
        $this->validateAdminProdiStudyPrograms($validated['roles'], $studyProgramIds);
        $validated['id_study_program'] = $studyProgramIds[0] ?? null;

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['roles'] = array_values(array_unique($roles));
        $validated['is_multiple_role'] = count($validated['roles']) > 1;

        $user->update($validated);
        $this->syncNotificationEmails($user, $validated['email'], $notificationEmails);
        $this->syncAdminProdiStudyPrograms($user, $studyProgramIds, in_array('admin_prodi', $validated['roles'], true));

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Export users to Excel
     */
    public function export()
    {
        return Excel::download(new UsersExport, 'users_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Download Excel templat for import
     */
    public function downloadTemplate()
    {
        $template = [
            ['nama', 'email', 'email_universitas', 'password', 'role', 'role_aktif', 'semua_roles', 'no_telepon', 'alamat', 'institusi', 'id_universitas', 'id_program_studi', 'id_program_studi_list', 'email_prodi_list', 'jabatan'],
            ['John Doe', 'john@example.com', '', 'password123', 'user', 'sekretariat', 'sekretariat,asesor', '081234567890', 'Jl. Contoh No. 123', 'Universitas Contoh', '', '', '', '', 'Dosen'],
            ['Admin Universitas Contoh', '', 'info@universitascontoh.ac.id', 'password123', 'user', 'admin_univ', 'admin_univ,admin_prodi', '081234567891', 'Jl. Contoh No. 456', 'Universitas Contoh', '1', '', '', '', 'Admin Universitas'],
            ['Admin Prodi Contoh', '', 'info@universitascontoh.ac.id', 'password123', 'user', 'admin_prodi', 'admin_prodi', '081234567892', 'Jl. Contoh No. 789', 'Universitas Contoh', '1', '', '1,2,3', 'arsitektur@contoh.ac.id;dkv@contoh.ac.id', 'Admin Prodi'],
        ];

        return Excel::download(new class($template) implements FromArray, WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return array_slice($this->data, 1); // Skip headers
            }

            public function headings(): array
            {
                return $this->data[0];
            }
        }, 'templat_users.xlsx');
    }

    /**
     * Import users from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new UsersImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();

            if ($failures->isNotEmpty()) {
                $errors = [];
                foreach ($failures as $failure) {
                    $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                }

                return redirect()->route('users.index')
                    ->with('warning', 'Import selesai dengan beberapa error: ' . implode(' | ', $errors));
            }

            return redirect()->route('users.index')
                ->with('success', 'Data pengguna berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function searchForSelect2(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $page   = max(1, (int) $request->get('page', 1));
        $role   = $request->get('role');
        $perPage = 20;

        $query = User::notAdmin()->orderBy('name');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($role) {
            $query->whereJsonContains('roles', $role);
        }

        $paginator = $query->paginate($perPage, ['id', 'name', 'email'], 'page', $page);

        $results = $paginator->getCollection()->map(fn ($u) => [
            'id'   => $u->id,
            'text' => $u->name . ' (' . $u->email . ')',
        ]);

        return response()->json([
            'results'    => $results,
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    private function parseNotificationEmails(?string $value, string $primaryEmail): array
    {
        $emails = collect(preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($email) => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values();

        $invalid = $emails->filter(fn($email) => !filter_var($email, FILTER_VALIDATE_EMAIL))->values();
        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'notification_emails' => 'Format email notifikasi tidak valid: ' . $invalid->implode(', '),
            ]);
        }

        $primaryEmail = strtolower(trim($primaryEmail));
        if ($emails->contains($primaryEmail)) {
            throw ValidationException::withMessages([
                'notification_emails' => 'Email notifikasi tambahan tidak boleh sama dengan email utama.',
            ]);
        }

        return $emails->all();
    }

    private function syncNotificationEmails(User $user, string $primaryEmail, array $additionalEmails): void
    {
        $primaryEmail = strtolower(trim($primaryEmail));

        UserEmail::where('user_id', $user->id)->update(['is_primary' => false]);

        UserEmail::updateOrCreate(
            ['user_id' => $user->id, 'email' => $primaryEmail],
            ['is_primary' => true, 'is_active' => true]
        );

        UserEmail::where('user_id', $user->id)
            ->where('is_primary', false)
            ->whereNotIn('email', $additionalEmails)
            ->update(['is_active' => false]);

        foreach ($additionalEmails as $email) {
            UserEmail::updateOrCreate(
                ['user_id' => $user->id, 'email' => $email],
                ['is_primary' => false, 'is_active' => true]
            );
        }
    }

    private function normalizeStudyProgramIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function syncAdminProdiStudyPrograms(User $user, array $studyProgramIds, bool $hasAdminProdiRole): void
    {
        if (!$hasAdminProdiRole) {
            StudyProgramUser::where('id_user', $user->id)
                ->where('role_in_prodi', 'admin_prodi')
                ->update(['is_active' => false, 'end_date' => now()]);

            return;
        }

        if (empty($studyProgramIds)) {
            StudyProgramUser::where('id_user', $user->id)
                ->where('role_in_prodi', 'admin_prodi')
                ->update(['is_active' => false, 'end_date' => now()]);

            return;
        }

        StudyProgramUser::where('id_user', $user->id)
            ->where('role_in_prodi', 'admin_prodi')
            ->whereNotIn('id_study_program', $studyProgramIds)
            ->update(['is_active' => false, 'end_date' => now()]);

        foreach ($studyProgramIds as $studyProgramId) {
            StudyProgramUser::updateOrCreate(
                [
                    'id_user' => $user->id,
                    'id_study_program' => $studyProgramId,
                    'role_in_prodi' => 'admin_prodi',
                ],
                [
                    'is_active' => true,
                    'start_date' => now(),
                    'end_date' => null,
                ]
            );
        }
    }

    private function validateAdminProdiStudyPrograms(array $roles, array $studyProgramIds): void
    {
        if (in_array('admin_prodi', $roles, true) && empty($studyProgramIds)) {
            throw ValidationException::withMessages([
                'id_study_programs' => 'Pilih minimal satu program studi untuk role PT/UPPS/PS.',
            ]);
        }
    }
}
