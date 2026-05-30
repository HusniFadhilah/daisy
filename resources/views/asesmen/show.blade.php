@extends('layouts.template.app')

@section('title', 'Detail Asesmen - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row align-items-center mb-3">
        <div class="col">
            <nav class="d-none d-md-block mb-1">
                <ol class="breadcrumb mb-0">
                    @if($asesmen->pengajuan)
                    <li class="breadcrumb-item"><a href="{{ route('de.pengajuan.show',$asesmen->pengajuan->id) }}">Pengajuan</a></li>
                    @endif
                    <li class="breadcrumb-item"><a href="{{ route('asesmen.index') }}">Asesmen</a></li>
                    <li class="breadcrumb-item active">{{ $asesmen->name }}</li>
                </ol>
            </nav>
            <h4 class="mb-0 text-wrap">{{ $asesmen->name }}</h4>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('asesmen.edit',$asesmen->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil"></i><span class="d-none d-md-inline"> Edit</span>
                </a>
                <a href="{{ route('asesmen.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i><span class="d-none d-md-inline"> Kembali</span>
                </a>
            </div>
        </div>
    </div>

    @if(in_array($asesmen->status, ['completed', 'archived']))
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-calculator"></i> Hasil Akreditasi
            </h5>
        </div>
        <div class="card-body">
            <a href="{{ route('hasil-akreditasi.show', $asesmen->id) }}" class="btn btn-primary">
                <i class="bi bi-eye"></i> Lihat Hasil & Perhitungan Skor
            </a>
        </div>
    </div>
    @endif

    <div class="card mb-4 border-warning" id="requirementsPanel">
        <div class="card-header bg-warning">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle"></i>
                Panduan Penugasan & Persyaratan
            </h5>
        </div>
        <div class="card-body">
            {{-- Tab navigation --}}
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#akRequirements" type="button">
                        Asesmen Kecukupan (AK)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#alRequirements" type="button">
                        Asesmen Lapangan (AL)
                    </button>
                </li>
            </ul>

            {{-- Tab content --}}
            <div class="tab-content">
                {{-- AK Requirements --}}
                <div class="tab-pane fade show active" id="akRequirements">
                    <div class="row">
                        <div class="col-md-6 my-2">
                            <h6 class="fw-bold mb-3">Persyaratan Minimum:</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person"></i> Asesor</span>
                                    <span class="badge bg-primary rounded-pill">
                                        Minimal 2 orang
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person-check"></i> Validator</span>
                                    <span class="badge bg-success rounded-pill">
                                        Minimal 1 orang
                                    </span>
                                </li>
                            </ul>

                            <div id="akStatus" class="status-container">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                Loading status...
                            </div>
                        </div>

                        <div class="col-md-6 my-2">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-3">Yang Telah Ditugaskan (AK):</h6>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" id="btnReorderAK" title="Reorder Asesor" onclick="reorderAsesor({ asesmenId: {{ $asesmen->id }}, jenisAsesmen: 'ak' })">
                                        <i class="bi bi-arrow-down-up"></i> Urutkan Ulang Asesor
                                    </button>
                                </div>
                            </div>

                            <div id="akAssignments" class="assignments-list">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div id="akRejected" class="rejected-list mt-3" style="display: none;">
                                <h6 class="text-danger fw-bold mb-2">
                                    <i class="bi bi-x-circle"></i> Penawaran Ditolak:
                                </h6>
                                <div id="akRejectedList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AL Requirements --}}
                <div class="tab-pane fade" id="alRequirements">
                    <div class="row">
                        <div class="col-md-6 my-2">
                            <h6 class="fw-bold mb-3">Persyaratan Minimum:</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person"></i> Asesor</span>
                                    <span class="badge bg-primary rounded-pill">
                                        Minimal 2 orang
                                    </span>
                                </li>
                            </ul>

                            <div id="alStatus" class="status-container">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                Loading status...
                            </div>
                        </div>

                        <div class="col-md-6 my-2">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-3">Yang Telah Ditugaskan (AL):</h6>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" id="btnReorderAL" title="Reorder Asesor" onclick="reorderAsesor({ asesmenId: {{ $asesmen->id }}, jenisAsesmen: 'al' })">
                                        <i class="bi bi-arrow-down-up"></i> Reorder Asesor
                                    </button>
                                </div>
                            </div>
                            <div id="alAssignments" class="assignments-list">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div id="alRejected" class="rejected-list mt-3" style="display: none;">
                                <h6 class="text-danger fw-bold mb-2">
                                    <i class="bi bi-x-circle"></i> Penawaran Ditolak:
                                </h6>
                                <div id="alRejectedList"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($asesmen->userRoles->where('status_penawaran', 'rejected')->count() > 0)
    <div class="alert alert-danger alert-permanent" role="alert">
        <h5 class="alert-heading">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Perhatian: Ada Penawaran yang Ditolak
        </h5>
        <p class="mb-0">
            Terdapat <strong>{{ $asesmen->userRoles->where('status_penawaran', 'rejected')->count() }}</strong> user yang menolak penawaran.
            Silakan tugaskan pengganti atau hapus penugasan yang ditolak.
        </p>
        <hr>
        <div class="mb-0">
            @foreach($asesmen->userRoles->where('status_penawaran', 'rejected') as $rejected)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>{{ $rejected->user->name }}</strong> -
                    {{ $rejected->role->alias }} ({{ strtoupper($rejected->jenis_asesmen) }})
                    @if($rejected->response_note)
                    <br><small class="text-muted">"{{ $rejected->response_note }}"</small>
                    @endif
                </div>
                <button class="btn btn-sm btn-warning" onclick="reassignUser({{ $rejected->id }}, '{{ $rejected->user->name }}', '{{ $rejected->jenis_asesmen }}')">
                    <i class="bi bi-arrow-repeat"></i> Tugaskan ulang
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4 col-lg-3 my-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $asesmen->userRoles->count() }}</h3>
                    <small class="text-muted">Peran Ditugaskan</small>
                </div>
            </div>
        </div>
        @foreach($statusPekerjaan as $key => $status)
        <div class="col-md-4 col-lg-3 my-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">
                        {{ optional($asesmenStats['ak'] ?? collect())
                    ->firstWhere('status_pekerjaan', $key)
                    ->total ?? 0 }}
                    </h3>
                    <small class="text-muted">AK - {{ $status['label'] }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="row">
        <!-- Asesmen Info Card -->
        <div class="col-md-12 col-lg-3 mb-4">
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

                    @if($asesmen->studyProgram)
                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Program Studi:</label>
                        <div class="fw-semibold">{{ $asesmen->studyProgram->full_name ?? '-' }}</div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="text-muted small mb-1">Perguruan Tinggi:</label>
                        <div class="fw-semibold">{{ $asesmen->studyProgram->university->name ?? '-' }}</div>
                    </div>
                    @endif

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
                            {{ \App\Libraries\Date::tglIndo($asesmen->tanggal_mulai) }} -
                            {{ \App\Libraries\Date::tglIndo($asesmen->tanggal_selesai) }}
                            @else
                            -
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <label class="text-muted small mb-1">Dibuat:</label>
                        <div>{{ \App\Libraries\Date::tglIndo($asesmen->created_at) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Section -->
        <div class="col-md-12 col-lg-9 mb-4">
            <!-- Assign User Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Tugaskan Role Baru</h5>
                </div>
                <div class="card-body">
                    <form id="assignForm" onsubmit="assignUser(event)">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Jenis Asesmen:</label>
                                <select id="jenisAsesmen" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="ak">Asesmen Kecukupan (AK)</option>
                                    <option value="al">Asesmen Lapangan (AL)</option>
                                    <option value="dokumen">Asesmen Dokumen</option>
                                    <option value="rekap">Rekap Asesmen</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pilih User:</label>
                                <select id="userId" class="form-select" required data-no-select2>
                                    <option value="">-- Pilih User --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
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
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Tugaskan
                                </button>
                            </div>
                        </div>
                    </form>
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
                                    <th style="max-width: 150px;">Email</th>
                                    <th style="width: 150px;">Jenis Asesmen</th>
                                    <th style="min-width: 120px;">Role</th>
                                    <th style="width: 150px;">Status</th>
                                    <th style="width: 150px;">Progress</th>
                                    <th style="width: 120px;">Ditugaskan</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($asesmen->userRoles ->sortBy([ ['jenis_asesmen', 'asc'], ['id_role', 'asc'] ]) as $index => $userRole)
                                @php
                                $stats = $userStats[$userRole->id_user] ?? ['completed' => 0, 'total' => 0, 'percentage' => 0];
                                $statusPenawaran = $userRole->status_penawaran;
                                $statusBadge = match($statusPenawaran) {
                                'accepted' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Diterima'],
                                'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                                'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu'],
                                default => ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'],
                                };
                                @endphp
                                <tr id="assignment-row-{{ $userRole->id }}" class="{{ $statusPenawaran === 'rejected' ? 'table-danger' : '' }}">
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
                                    <td>
                                        <span class="badge bg-light text-dark d-inline-block text-wrap" style="min-width: 120px; white-space: normal; word-break: break-word;">
                                            {{ $userRole->user->email }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $userRole->jenis_asesmen === 'ak' ? 'primary' : 'info' }}">
                                            {{ strtoupper($userRole->jenis_asesmen) }}
                                        </span>
                                        @if($userRole->urutan_asesor)
                                        <small class="text-muted">#{{ $userRole->urutan_asesor }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($statusPenawaran === 'accepted')
                                        <select class="form-select form-select-sm" onchange="updateUserRole({{ $userRole->id }}, this.value)" disabled>
                                            @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ $userRole->id_role == $role->id ? 'selected' : '' }}>
                                                {{ $role->alias }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @else
                                        <span class="badge bg-secondary">{{ $userRole->role->alias }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <small>Status penawaran:</small>
                                            {{-- Badge Status Penawaran --}}
                                            <span class="badge bg-{{ $statusBadge['class'] }} d-inline-flex align-items-center">
                                                <i class="bi bi-{{ $statusBadge['icon'] }} me-1"></i>
                                                {{ $statusBadge['text'] }}
                                            </span>
                                        </div>

                                        {{-- Badge Status Pekerjaan --}}
                                        @if($statusPenawaran === 'accepted' && $userRole->status_pekerjaan)
                                        <div class="mt-2">
                                            <small>Status pekerjaan:</small>
                                            <span class="badge badge-outline-{{ $userRole->status_badge }}">
                                                <i class="bi bi-{{ $userRole->status_icon }}"></i>
                                                {{ $userRole->status_label }}
                                            </span>
                                        </div>
                                        @endif

                                        {{-- Tooltip jika ditolak --}}
                                        @if($statusPenawaran === 'rejected' && $userRole->response_note)
                                        <div class="mt-2">
                                            <small>Alasan penolakan:</small>
                                            <button class="btn btn-sm btn-link p-0 ms-1" data-bs-toggle="tooltip" title="{{ $userRole->response_note }}">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($statusPenawaran === 'accepted')
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $stats['percentage'] == 100 ? 'success' : ($stats['percentage'] > 0 ? 'warning' : 'secondary') }}" role="progressbar" style="width: {{ $stats['percentage'] }}%">
                                                {{ $stats['percentage'] }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $stats['completed'] }}/{{ $stats['total'] }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ \App\Libraries\Date::tglIndo($userRole->created_at) }}</small>
                                        @if($userRole->responded_at)
                                        <br>
                                        <small class="text-muted">Respon: {{ \App\Libraries\Date::tglIndo($userRole->responded_at) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($statusPenawaran === 'rejected')
                                        <button type="button" class="btn btn-sm btn-warning" onclick="reassignUser({{ $userRole->id }}, '{{ $userRole->user->name }}', '{{ $userRole->jenis_asesmen }}')" data-bs-toggle="tooltip" title="Tugaskan ulang">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUser({{ $userRole->id }}, {{ $userRole->id_user }}, '{{ $userRole->user->name }}', '{{ $userRole->jenis_asesmen }}')" data-bs-toggle="tooltip" title="Hapus" {{ $statusPenawaran === 'accepted' && $stats['completed'] > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada user ditugaskan. Tugaskan user di atas.</p>
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-people-fill"></i> Bulk Assign Users</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkAssignForm" onsubmit="bulkAssignUsers(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Multiple Users:</label>
                        <select id="bulkUserIds" class="form-select" multiple required data-no-select2>
                        </select>
                        <small class="text-muted">Ketik untuk mencari, klik untuk memilih beberapa user</small>
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
                        <i class="bi bi-check-circle"></i> Tugaskan Semua
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
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
                    <div class="alert alert-info alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Kirim link kertas kerja dan panduan penilaian kepada <strong id="recipientName"></strong> sebagai <strong id="recipientRole"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Link Kertas Kerja <span class="text-danger">*</span>
                        </label>
                        <input type="url" class="form-control @error('kertas_kerja_link') is-invalid @enderror" id="kertasKerjaLink" name="kertas_kerja_link" placeholder="https://docs.google.com/..." required>
                        <small class="text-muted">
                            Masukkan link Google Docs, Excel Online, atau platform lainnya
                        </small>
                        @error('kertas_kerja_link')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Link Panduan Penilaian
                        </label>
                        <input type="url" class="form-control @error('panduan_link') is-invalid @enderror" id="panduanLink" name="panduan_link" placeholder="https://docs.google.com/...">
                        <small class="text-muted">
                            Opsional: Link ke panduan atau petunjuk penilaian
                        </small>
                        @error('panduan_link')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Tambahan:</label>
                        <textarea class="form-control @error('send_note') is-invalid @enderror" id="sendNote" name="send_note" rows="3" placeholder="Tambahkan catatan atau instruksi khusus..."></textarea>
                        @error('send_note')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="alert alert-warning alert-permanent">
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

    .status-container {
        padding: 15px;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
    }

    .status-container.complete {
        background: #d4edda;
        border-color: #28a745;
    }

    .status-container.incomplete {
        background: #fff3cd;
        border-color: #ffc107;
    }

    .assignments-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .assignment-item {
        padding: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        margin-bottom: 8px;
        background: #f8f9fa;
    }

    .assignment-item.pending-assignment {
        border: 2px dashed #ffc107;
        background: #fff9e6;
    }

    .rejected-item {
        padding: 10px;
        border: 1px solid #dc3545;
        border-radius: 6px;
        margin-bottom: 8px;
        background: #f8d7da;
    }

</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        var userAjaxConfig = {
            url: '{{ route("ajax.users.search") }}',
            dataType: 'json',
            delay: 250,
            cache: true,
            data: function (params) {
                return { q: params.term, page: params.page || 1 };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return { results: data.results, pagination: data.pagination };
            },
        };

        $('#userId').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih User --',
            allowClear: true,
            ajax: userAjaxConfig,
        });

        $('#bulkUserIds').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Cari dan pilih users --',
            allowClear: true,
            dropdownParent: $('#bulkAssignModal'),
            ajax: userAjaxConfig,
        });
    });

    const idAsesmen = "{{ $asesmen->id }}";
    const csrfToken = '{{ csrf_token() }}';

    document.addEventListener('DOMContentLoaded', function() {
        loadRequirementsStatus('ak');
        loadRequirementsStatus('al');
    });

    /**
     * ============================================
     * LOAD REQUIREMENTS STATUS
     * ============================================
     */
    async function loadRequirementsStatus(jenisAsesmen) {
        try {
            // Fetch all data in parallel
            const [statusResponse, rejectedResponse, assignmentsResponse] = await Promise.all([
                fetch(`/asesmen/${idAsesmen}/requirements/${jenisAsesmen}`)
                , fetch(`/asesmen/${idAsesmen}/rejected/${jenisAsesmen}`)
                , fetch(`/asesmen/${idAsesmen}/assignments/${jenisAsesmen}`)
            ]);

            const statusData = await statusResponse.json();
            const rejectedData = await rejectedResponse.json();
            const assignmentsData = await assignmentsResponse.json();

            // Update UI
            updateRequirementsUI(
                jenisAsesmen
                , statusData.data
                , rejectedData.data
                , assignmentsData.data
            );
        } catch (error) {
            console.error(`Error loading ${jenisAsesmen} requirements:`, error);

            // Show error in UI
            const statusContainer = document.getElementById(`${jenisAsesmen}Status`);
            if (statusContainer) {
                statusContainer.className = 'status-container';
                statusContainer.innerHTML = `
                    <div class="text-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        Gagal memuat data
                    </div>
                `;
            }
        }
    }

    /**
     * ============================================
     * UPDATE REQUIREMENTS UI
     * ============================================
     */
    function updateRequirementsUI(jenisAsesmen, status, rejected, assignments) {
        const prefix = jenisAsesmen.toUpperCase();
        const statusContainer = document.getElementById(`${jenisAsesmen}Status`);
        const assignmentsContainer = document.getElementById(`${jenisAsesmen}Assignments`);
        const rejectedContainer = document.getElementById(`${jenisAsesmen}Rejected`);
        const rejectedList = document.getElementById(`${jenisAsesmen}RejectedList`);

        // ============================================
        // 1. UPDATE STATUS CONTAINER
        // ============================================
        if (status && status.requirements_met) {
            statusContainer.className = 'status-container complete';
            statusContainer.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                    <div>
                        <div class="fw-bold">✅ Persyaratan Terpenuhi</div>
                        <small class="text-muted">
                            Asesor: ${status.current.asesor} ${jenisAsesmen == 'ak' ? '| Validator: '+status.current.validator:''}
                        </small>
                    </div>
                </div>
            `;
        } else {
            statusContainer.className = 'status-container incomplete';
            statusContainer.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-2"></i>
                    <div>
                        <div class="fw-bold">Persyaratan Belum Terpenuhi</div>
                        <small class="text-danger">
                            ${status.missing.join(', ')}
                        </small>
                        <br>
                        <small>
                            Saat ini: Asesor ${status.current.asesor} ${jenisAsesmen == 'ak' ? '| Validator: '+status.current.validator:''}
                        </small>
                        <br><small class="text-muted">Semua role yang ditugaskan harus menyetujuinya, atau cari user lain</small>
                    </div>
                </div>
            `;
        }

        // ============================================
        // 2. UPDATE ASSIGNMENTS LIST
        // ============================================
        if (assignments && Object.keys(assignments).length > 0) {
            let assignmentsHtml = '';

            // Iterate through roles (asesor, validator, etc)
            for (const [roleName, users] of Object.entries(assignments)) {
                const roleIcon = roleName === 'asesor' ? 'person' : 'person-check';
                const roleBadge = roleName === 'asesor' ? 'primary' : 'success';
                const roleLabel = roleName === 'asesor' ? 'Asesor' : 'Validator';

                assignmentsHtml += `
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-${roleIcon} me-2"></i>
                            <strong>${roleLabel} (${users.length})</strong>
                        </div>
                `;

                users.forEach(user => {
                    const statusBadge = getStatusPekerjaanBadge(user.status_pekerjaan);
                    const urutanText = user.urutan_asesor ? ` #${user.urutan_asesor}` : '';

                    // ✅ ADD: Status penawaran indicator
                    const penawaranBadge = user.status_penawaran === 'pending' ?
                        '<span class="badge bg-warning ms-2">Menunggu Konfirmasi</span>' :
                        '<span class="badge bg-success ms-2">Diterima</span>';

                    assignmentsHtml += `
                        <div class="assignment-item ${user.status_penawaran === 'pending' ? 'pending-assignment' : ''}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${user.user.name}${urutanText}</strong>
                                    <small class="d-block text-muted">${user.user.email}</small>
                                    ${penawaranBadge}
                                </div>
                                <span class="badge bg-${statusBadge.color}">${statusBadge.text}</span>
                            </div>
                            <small class="text-muted">Ditugaskan: ${user.created_at}</small>
                        </div>
                    `;
                });

                assignmentsHtml += '</div>';
            }

            assignmentsContainer.innerHTML = assignmentsHtml;
        } else {
            assignmentsContainer.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mb-0 mt-2">Belum ada yang ditugaskan untuk ${prefix}</p>
                </div>
            `;
        }

        // ============================================
        // 3. UPDATE REJECTED LIST
        // ============================================
        if (rejected && rejected.length > 0) {
            rejectedContainer.style.display = 'block';
            rejectedList.innerHTML = rejected.map(item => `
                <div class="rejected-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.user.name}</strong>
                            <small class="d-block text-muted">${item.role.alias}</small>
                        </div>
                        <span class="badge bg-danger">Ditolak</span>
                    </div>
                    ${item.response_note ? `<small class="text-muted mt-1 d-block"><i class="bi bi-chat-quote"></i> "${item.response_note}"</small>` : ''}
                </div>
            `).join('');
        } else {
            rejectedContainer.style.display = 'none';
        }
    }

    /**
     * ============================================
     * GET STATUS BADGE
     * ============================================
     */
    function getStatusPekerjaanBadge(status) {
        const badges = {
            'not_started': {
                color: 'secondary'
                , text: 'Belum Mulai'
            }
            , 'in_progress': {
                color: 'info'
                , text: 'Sedang Dikerjakan'
            }
            , 'submitted': {
                color: 'warning'
                , text: 'Submitted'
            }
            , 'revision_required': {
                color: 'danger'
                , text: 'Revisi'
            }
            , 'approved': {
                color: 'success'
                , text: 'Approved'
            }
        };

        return badges[status] || {
            color: 'secondary'
            , text: 'Unknown'
        };
    }

    /**
     * ============================================
     * SEND DOCUMENTS
     * ============================================
     */
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
     * ============================================
     * ASSIGN USER
     * ============================================
     */
    async function assignUser(event) {
        event.preventDefault();

        const userId = document.getElementById('userId').value;
        const roleId = document.getElementById('roleId').value;
        const jenisAsesmen = document.getElementById('jenisAsesmen').value;

        if (!userId || !roleId || !jenisAsesmen) {
            Swal.fire({
                icon: 'warning'
                , title: 'Perhatian'
                , text: 'Mohon lengkapi semua field'
            });
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
                    , jenis_asesmen: jenisAsesmen
                })
            });

            const data = await response.json();

            if (data.success) {
                let message = data.message;

                if (!data.data.requirements_met) {
                    message += '\n\n⚠️ ' + data.data.missing_requirements.join(', ');
                } else {
                    message += '\n\n✅ Persyaratan telah terpenuhi!';
                }

                await Swal.fire({
                    icon: data.data.requirements_met ? 'success' : 'info'
                    , title: 'Berhasil!'
                    , text: message
                });

                location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
        }
    }

    /**
     * ============================================
     * BULK ASSIGN USERS
     * ============================================
     */
    async function bulkAssignUsers(event) {
        event.preventDefault();

        const select = document.getElementById('bulkUserIds');
        const userIds = Array.from(select.selectedOptions).map(option => option.value);
        const roleId = document.getElementById('bulkRoleId').value;

        if (userIds.length === 0) {
            Swal.fire({
                icon: 'warning'
                , title: 'Perhatian'
                , text: 'Mohon pilih minimal 1 user'
            });
            return;
        }

        if (!roleId) {
            Swal.fire({
                icon: 'warning'
                , title: 'Perhatian'
                , text: 'Mohon pilih role'
            });
            return;
        }

        const confirmed = await Swal.fire({
            icon: 'question'
            , title: 'Konfirmasi'
            , text: `Tugaskan ${userIds.length} user(s) ke asesmen ini?`
            , showCancelButton: true
            , confirmButtonText: 'Ya, Tugaskan'
            , cancelButtonText: 'Batal'
        });

        if (!confirmed.isConfirmed) return;

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
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                });
                location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
        }
    }

    async function reorderAsesor({
        jenisAsesmen
        , orderBy = 'created_at'
        , asesmenId
    }) {
        const result = await Swal.fire({
            icon: 'question'
            , title: `Reorder Asesor ${jenisAsesmen.toUpperCase()}?`
            , text: 'Urutan asesor akan diatur ulang berdasarkan waktu penugasan (yang paling awal = Asesor 1)'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Reorder'
            , cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/asesmen/${asesmenId}/reorder-asesor`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify({
                    jenis_asesmen: jenisAsesmen
                    , order_by: orderBy
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Gagal reorder asesor');
            }

            await Swal.fire({
                icon: 'success'
                , title: 'Berhasil!'
                , text: data.message
                , timer: 2000
            });

            location.reload();

        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Gagal'
                , text: error.message
            });
        }
    }

    /**
     * ============================================
     * UPDATE USER ROLE
     * ============================================
     */
    async function updateUserRole(assignmentId, roleId) {
        const confirmed = await Swal.fire({
            icon: 'question'
            , title: 'Konfirmasi'
            , text: 'Update role user ini?'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Update'
            , cancelButtonText: 'Batal'
        });

        if (!confirmed.isConfirmed) {
            location.reload();
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
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                    , timer: 1500
                    , showConfirmButton: false
                });

                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
            location.reload();
        }
    }

    /**
     * ============================================
     * REMOVE USER
     * ============================================
     */
    async function removeUser(assignmentId, userId, userName, jenisAsesmen) {
        const confirmed = await Swal.fire({
            icon: 'warning'
            , title: 'Konfirmasi Hapus'
            , html: `Hapus <strong>"${userName}"</strong> dari ${jenisAsesmen.toUpperCase()}?<br><br><small class="text-danger">Perhatian: Sistem akan cek apakah persyaratan minimum masih terpenuhi.</small>`
            , showCancelButton: true
            , confirmButtonText: 'Ya, Hapus'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#dc3545'
        });

        if (!confirmed.isConfirmed) return;

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/remove-user/${userId}`, {
                method: 'DELETE'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                const row = document.getElementById(`assignment-row-${assignmentId}`);
                if (row) {
                    row.remove();
                }

                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                    , timer: 1500
                    , showConfirmButton: false
                });

                // Reload requirements for affected jenis_asesmen
                loadRequirementsStatus(data.jenis_asesmen || jenisAsesmen);

                setTimeout(() => location.reload(), 1000);
            } else {
                // Check if it's a validation error
                if (data.validation_error) {
                    await Swal.fire({
                        icon: 'error'
                        , title: 'Tidak Bisa Menghapus'
                        , html: data.message
                        , confirmButtonColor: '#dc3545'
                    });
                } else {
                    throw new Error(data.message);
                }
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
        }
    }

    /**
     * ============================================
     * REASSIGN USER
     * ============================================
     */
    async function reassignUser(assignmentId, userName, jenisAsesmen) {
        const {
            value: newUserId
        } = await Swal.fire({
            title: 'Tugaskan Ulang User'
            , html: `
            <p>Pilih pengganti untuk <strong>${userName}</strong> (${jenisAsesmen.toUpperCase()}):</p>
            <select id="newUserId" class="form-select">
                <option value="">-- Pilih User --</option>
                @foreach($availableUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        `
            , showCancelButton: true
            , confirmButtonText: 'Tugaskan ulang'
            , cancelButtonText: 'Batal'
            , preConfirm: () => {
                const select = document.getElementById('newUserId');
                if (!select.value) {
                    Swal.showValidationMessage('Pilih user terlebih dahulu');
                    return false;
                }
                return select.value;
            }
        });

        if (!newUserId) return;

        try {
            const response = await fetch(`/asesmen/${idAsesmen}/reassign-user`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    assignment_id: assignmentId
                    , new_user_id: newUserId
                , })
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                , });

                location.reload();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
        }
    }

    /**
     * ============================================
     * REFRESH ASSIGNMENTS
     * ============================================
     */
    function refreshAssignments() {
        location.reload();
    }

    /**
     * ============================================
     * INITIALIZE TOOLTIPS
     * ============================================
     */
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

</script>
@endpush

@endsection
