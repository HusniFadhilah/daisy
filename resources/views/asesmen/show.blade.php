@extends('layouts.template.app')

@section('title', 'Detail Asesmen - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('asesmen.index') }}">Asesmen</a></li>
                    <li class="breadcrumb-item active">{{ $asesmen->name }}</li>
                </ol>
            </nav>
            <h2 class="mb-0">{{ $asesmen->name }}</h2>
        </div>
        <div class="btn-group">
            <a href="{{ route('asesmen.edit', $asesmen->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('asesmen.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Asesmen Info Card -->
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Asesmen</h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Nama Asesmen:</label>
                        <div class="fw-semibold">{{ $asesmen->name }}</div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Deskripsi:</label>
                        <div>{{ $asesmen->description ?? '-' }}</div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Perguruan Tinggi:</label>
                        <div class="fw-semibold">{{ $asesmen->perguruan_tinggi ?? '-' }}</div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Bentuk PT:</label>
                        <div>{{ $asesmen->bentuk_pt ?? '-' }}</div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Kode Panel:</label>
                        <div>
                            <span class="badge bg-secondary">{{ $asesmen->kode_panel ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Periode:</label>
                        <div>
                            @if($asesmen->tanggal_mulai && $asesmen->tanggal_selesai)
                            {{ \Carbon\Carbon::parse($asesmen->tanggal_mulai)->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($asesmen->tanggal_selesai)->format('d M Y') }}
                            @else
                            -
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="text-muted small mb-1">Dibuat:</label>
                        <div>{{ $asesmen->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Section -->
        <div class="col-md-9 mb-4">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary mb-0">{{ $asesmen->userRoles->count() }}</h3>
                            <small class="text-muted">Asesor Ditugaskan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-0">{{ $asesmen->penilaianElemen->where('status', 'submitted')->count() }}</h3>
                            <small class="text-muted">Penilaian Submitted</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-0">{{ $asesmen->penilaianElemen->where('status', 'draft')->count() }}</h3>
                            <small class="text-muted">Penilaian Draft</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign User Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Assign Role Baru</h5>
                </div>
                <div class="card-body">
                    <form id="assignForm" onsubmit="assignUser(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih User:</label>
                                <select id="userId" class="form-select" required>
                                    <option value="">-- Pilih User --</option>
                                    @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                    @endforeach
                                </select>
                                @if($availableUsers->count() == 0)
                                <small class="text-warning">
                                    <i class="bi bi-info-circle"></i> Semua user sudah di-assign
                                </small>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Role:</label>
                                <select id="roleId" class="form-select" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->alias }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-success w-100" {{ $availableUsers->count() == 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-plus-circle"></i> Assign
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Assign (Optional) -->
                    <div class="mt-3 pt-3 border-top">
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                            <i class="bi bi-people-fill"></i> Bulk Assign Multiple Users
                        </button>
                    </div>
                </div>
            </div>

            <!-- Assigned Users List -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Role Ditugaskan ({{ $asesmen->userRoles->count() }})</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshAssignments()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="assignedUsersTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th style="width: 300px;">Role</th>
                                    <th style="width: 150px;">Progress</th>
                                    <th style="width: 120px;">Ditugaskan</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($asesmen->userRoles as $index => $userRole)
                                @php
                                $stats = $userStats[$userRole->id_user] ?? ['completed' => 0, 'total' => 0, 'percentage' => 0];
                                @endphp
                                <tr id="assignment-row-{{ $userRole->id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                {{ substr($userRole->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $userRole->user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $userRole->user->email }}</td>
                                    <td>
                                        <select class="form-select form-select-sm" onchange="updateUserRole({{ $userRole->id }}, this.value)">
                                            @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ $userRole->role->name == $role->name ? 'selected' : '' }}>
                                                {{ $role->alias }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $stats['percentage'] == 100 ? 'success' : ($stats['percentage'] > 0 ? 'warning' : 'secondary') }}" role="progressbar" style="width: {{ $stats['percentage'] }}%">
                                                {{ $stats['percentage'] }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $stats['completed'] }}/{{ $stats['total'] }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $userRole->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUser({{ $userRole->id }}, {{ $userRole->id_user }}, '{{ $userRole->user->name }}')" data-bs-toggle="tooltip" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada asesor ditugaskan. Assign asesor di atas.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-people-fill"></i> Bulk Assign Users</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAssignForm" onsubmit="bulkAssignUsers(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Multiple Users:</label>
                        <select id="bulkUserIds" class="form-select" multiple size="10" required>
                            @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Cmd di Mac) untuk pilih multiple users</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role untuk Semua User:</label>
                        <select id="bulkRoleId" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->alias }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Assign Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Modal untuk Admin/DE mengirim kertas kerja dan panduan --}}
{{-- Include di show.blade.php --}}

<!-- Send Documents Modal -->
<div class="modal fade" id="sendDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-send"></i> Kirim Kertas Kerja & Panduan Penilaian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendDocumentsForm">
                <input type="hidden" id="assignmentId" name="assignment_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Kirim link kertas kerja dan panduan penilaian kepada <strong id="recipientName"></strong> sebagai <strong id="recipientRole"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Link Kertas Kerja <span class="text-danger">*</span>
                        </label>
                        <input type="url" class="form-control" id="kertasKerjaLink" name="kertas_kerja_link" placeholder="https://docs.google.com/..." required>
                        <small class="text-muted">
                            Masukkan link Google Docs, Excel Online, atau platform lainnya
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Link Panduan Penilaian
                        </label>
                        <input type="url" class="form-control" id="panduanLink" name="panduan_link" placeholder="https://docs.google.com/...">
                        <small class="text-muted">
                            Opsional: Link ke panduan atau petunjuk penilaian
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Tambahan:</label>
                        <textarea class="form-control" id="sendNote" name="send_note" rows="3" placeholder="Tambahkan catatan atau instruksi khusus..."></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Penting:</strong> Pastikan link dapat diakses oleh penerima (bukan private/restricted)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #932136, #870820);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

    .info-item label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress {
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }

</style>
@endpush

@push('scripts')
<script>
    const idAsesmen = "{{ $asesmen->id }}";
    const csrfToken = '{{ csrf_token() }}';

    function sendDocuments(assignmentId, userName, roleName) {
        document.getElementById('assignmentId').value = assignmentId;
        document.getElementById('recipientName').textContent = userName;
        document.getElementById('recipientRole').textContent = roleName;
        document.getElementById('kertasKerjaLink').value = '';
        document.getElementById('panduanLink').value = '';
        document.getElementById('sendNote').value = '';

        new bootstrap.Modal(document.getElementById('sendDocumentsModal')).show();
    }

    document.getElementById('sendDocumentsForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/send-documents`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: result.message
                    , confirmButtonColor: '#28a745'
                });

                bootstrap.Modal.getInstance(document.getElementById('sendDocumentsModal')).hide();
                location.reload();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i> Kirim Dokumen';
        }
    });

    /**
     * Assign single user
     */
    async function assignUser(event) {
        event.preventDefault();

        const userId = document.getElementById('userId').value;
        const roleId = document.getElementById('roleId').value;

        if (!userId || !roleId) {
            alert('Mohon pilih user dan role');
            return;
        }

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/assign-user`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    id_user: userId
                    , id_role: roleId
                , })
            , });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                location.reload(); // Reload untuk update list
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat assign user');
        }
    }

    /**
     * Bulk assign users
     */
    async function bulkAssignUsers(event) {
        event.preventDefault();

        const select = document.getElementById('bulkUserIds');
        const userIds = Array.from(select.selectedOptions).map(option => option.value);
        const roleId = document.getElementById('bulkRoleId').value;

        if (userIds.length === 0) {
            alert('Mohon pilih minimal 1 user');
            return;
        }

        if (!roleId) {
            alert('Mohon pilih role');
            return;
        }

        if (!confirm(`Assign ${userIds.length} user(s) ke asesmen ini?`)) {
            return;
        }

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/bulk-assign`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    id_users: userIds
                    , id_role: roleId
                , })
            , });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat bulk assign');
        }
    }

    /**
     * Update user role
     */
    async function updateUserRole(assignmentId, roleId) {
        if (!confirm('Update role user ini?')) {
            location.reload(); // Reload untuk reset select
            return;
        }

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/update-role`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    assignment_id: assignmentId
                    , id_role: roleId
                , })
            , });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
            } else {
                alert('Error: ' + data.message);
                location.reload();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat update role');
            location.reload();
        }
    }

    /**
     * Remove user from asesmen
     */
    async function removeUser(assignmentId, userId, userName) {
        if (!confirm(`Hapus "${userName}" dari asesmen ini?\n\nPerhatian: User yang sudah melakukan penilaian tidak bisa dihapus.`)) {
            return;
        }

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/remove-user/${userId}`, {
                method: 'DELETE'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
            , });

            const data = await response.json();

            if (data.success) {
                // Remove row from table
                const row = document.getElementById(`assignment-row-${assignmentId}`);
                if (row) {
                    row.remove();
                }
                showToast(data.message, 'success');

                // Reload after 1 second to update available users
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat hapus user');
        }
    }

    /**
     * Refresh assignments
     */
    function refreshAssignments() {
        location.reload();
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        // Simple alert for now, can be replaced with Bootstrap toast
        const icon = type === 'success' ? '✅' : '❌';
        alert(`${icon} ${message}`);
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

</script>
@endpush

@endsection
