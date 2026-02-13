<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'role_selected' => 'required|in:super_admin,sekretariat,asesor,validator,verifikator,admin_univ,admin_prodi,default',
            'roles' => 'nullable|array',
            'roles.*' => 'in:super_admin,sekretariat,asesor,validator,verifikator,admin_univ,admin_prodi,default',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'id_university' => 'nullable|exists:universities,id',
            'id_study_program' => 'nullable|exists:study_programs,id',
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

        User::create($validated);

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
        $user = User::with(['university', 'studyProgram'])->findOrFail($id);
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
            'role_selected' => 'required|in:super_admin,sekretariat,asesor,validator,verifikator,admin_univ,admin_prodi,default',
            'roles' => 'nullable|array',
            'roles.*' => 'in:super_admin,sekretariat,asesor,validator,verifikator,admin_univ,admin_prodi,default',
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'institution' => 'nullable|string|max:255',
            'id_university' => 'nullable|exists:universities,id',
            'id_study_program' => 'nullable|exists:study_programs,id',
            'position' => 'nullable|string|max:255',
        ]);

        // Set roles - jika tidak diisi, gunakan role_selected sebagai default
        $roles = $request->roles ?? [$validated['role_selected']];

        // Pastikan role_selected ada di dalam roles
        if (!in_array($validated['role_selected'], $roles)) {
            $roles[] = $validated['role_selected'];
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['roles'] = array_values(array_unique($roles));
        $validated['is_multiple_role'] = count($validated['roles']) > 1;

        $user->update($validated);

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
            ['nama', 'email', 'password', 'role', 'role_aktif', 'semua_roles', 'no_telepon', 'alamat', 'institusi', 'id_universitas', 'id_program_studi', 'jabatan'],
            ['John Doe', 'john@example.com', 'password123', 'user', 'sekretariat', 'sekretariat,asesor', '081234567890', 'Jl. Contoh No. 123', 'Universitas Contoh', '1', '1', 'Dosen'],
            ['Jane Smith', 'jane@example.com', 'password123', 'admin', 'admin_univ', 'admin_univ,validator', '081234567891', 'Jl. Contoh No. 456', 'Universitas Contoh', '1', '2', 'Kaprodi'],
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
}
