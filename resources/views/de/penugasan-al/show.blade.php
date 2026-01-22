@extends('layouts.template.app')

@section('title', 'Detail Penugasan AL - ' . $pengajuan->nomor_permohonan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('de.penugasan-al') }}">
                    <i class="bi bi-arrow-left"></i> Penugasan AL
                </a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-geo-alt"></i> Detail Penugasan AL
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_permohonan }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Left: Info -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Program Studi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Program Studi</td>
                            <td><strong>{{ $pengajuan->studyProgram->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Universitas</td>
                            <td>{{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenjang</td>
                            <td>{{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ str_replace('_', ' ', strtoupper($pengajuan->status)) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Requirements Status -->
            <div class="card mb-3">
                <div class="card-header bg-{{ $requirementsStatus['met'] ? 'success' : 'warning' }} text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-{{ $requirementsStatus['met'] ? 'check-circle' : 'exclamation-triangle' }}"></i> Status Persyaratan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <i class="bi bi-person"></i> Asesor: <strong>{{ $requirementsStatus['asesor_count'] }}</strong> / 2
                    </div>
                    @if(!$requirementsStatus['met'])
                    <div class="alert alert-warning alert-permanent mt-3 mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ implode(', ', $requirementsStatus['missing']) }}
                        </small>
                    </div>
                    @else
                    <div class="alert alert-success alert-permanent mt-3 mb-0">
                        <small>
                            <i class="bi bi-check-circle"></i>
                            Persyaratan terpenuhi!
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- AL Schedule Info -->
            @if($pengajuan->asesmen?->asesmenLapangan)
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-range"></i> Jadwal Visitasi AL
                    </h6>
                    <button class="btn btn-sm btn-light" onclick="showUpdateScheduleModal({{ $pengajuan->id }})">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
                <div class="card-body">
                    @php $al = $pengajuan->asesmen->asesmenLapangan; @endphp
                    <table class="table table-sm table-borderless mb-0">
                        @if($al->tanggal_mulai)
                        <tr>
                            <td class="text-muted" width="40%">Tanggal Mulai</td>
                            <td><strong>{{ \Carbon\Carbon::parse($al->tanggal_mulai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($al->tanggal_selesai)
                        <tr>
                            <td class="text-muted">Estimasi Tanggal Selesai</td>
                            <td><strong>{{ \Carbon\Carbon::parse($al->tanggal_selesai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($al->tanggal_mulai && $al->tanggal_selesai)
                        <tr>
                            <td class="text-muted">Durasi</td>
                            <td>
                                @php
                                $start = \Carbon\Carbon::parse($al->tanggal_mulai);
                                $end = \Carbon\Carbon::parse($al->tanggal_selesai);
                                $days = $start->diffInDays($end);
                                @endphp
                                <span class="badge bg-primary">{{ $days }} hari</span>
                            </td>
                        </tr>
                        @endif
                        @if($al->lokasi_visitasi)
                        <tr>
                            <td class="text-muted">Lokasi Visitasi</td>
                            <td>
                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                {{ $al->lokasi_visitasi }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Assignment -->
        <div class="col-lg-8">
            <!-- Assign Form -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-person-plus"></i> Tugaskan Asesor AL
                    </h6>
                </div>
                <div class="card-body">
                    <form id="assignForm" onsubmit="assignAsesor(event, {{ $pengajuan->id }})">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Asesor: <span class="text-danger">*</span></label>
                                <select id="userId" class="form-select" required>
                                    <option value="">-- Pilih Asesor --</option>
                                    @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi Visitasi: <span class="text-danger">*</span></label>
                                <input type="text" id="lokasiVisitasi" class="form-control" maxlength="500" placeholder="Alamat lengkap lokasi visitasi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai: <span class="text-danger">*</span></label>
                                <input type="date" id="tanggalMulai" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estimasi Tanggal Selesai: <span class="text-danger">*</span></label>
                                <input type="date" id="tanggalSelesai" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> Tugaskan Asesor
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assigned List -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Daftar Asesor AL</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Status Penawaran</th>
                                <th>Status Pekerjaan</th>
                                <th>Progress</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuan->asesmen?->asesmenUserRoles ?? [] as $assignment)
                            @php
                            $progress = $userProgress[$assignment->id_user] ?? ['percentage' => 0, 'completed' => 0, 'total' => 0];
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $assignment->user->name }}</strong>
                                    <span class="badge bg-secondary">#{{ $assignment->urutan_asesor }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $assignment->status_penawaran === 'accepted' ? 'success' : ($assignment->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($assignment->status_penawaran) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $assignment->status_pekerjaan === 'submitted' ? 'success' : ($assignment->status_pekerjaan === 'in_progress' ? 'info' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status_pekerjaan)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $progress['percentage'] == 100 ? 'success' : 'info' }}" style="width: {{ $progress['percentage'] }}%">
                                            {{ $progress['percentage'] }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $progress['completed'] }}/{{ $progress['total'] }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="removeAsesor({{ $pengajuan->id }}, {{ $assignment->id_user }}, '{{ $assignment->user->name }}')" {{ $assignment->status_pekerjaan !== 'not_started' ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Belum ada asesor yang ditugaskan
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

<!-- Modal: Update Schedule -->
<div class="modal fade" id="modalUpdateSchedule" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-event"></i> Perbarui Jadwal Visitasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUpdateSchedule" onsubmit="submitUpdateSchedule(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="updateTanggalMulai" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estimasi Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="updateTanggalSelesai" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Visitasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="updateLokasiVisitasi" maxlength="500" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-check"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
$hasAsesmenLapangan = $pengajuan->asesmen?->asesmenLapangan;
@endphp

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    let currentPengajuanId = null;

    // Assign Asesor
    async function assignAsesor(event, pengajuanId) {
        event.preventDefault();

        const userId = document.getElementById('userId').value;
        const tanggalMulai = document.getElementById('tanggalMulai').value;
        const tanggalSelesai = document.getElementById('tanggalSelesai').value;
        const lokasiVisitasi = document.getElementById('lokasiVisitasi').value;

        // Validate dates
        if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Estimasi tanggal selesai tidak boleh lebih awal dari tanggal mulai!'
            });
            return;
        }

        try {
            const response = await fetch(`/de/penugasan-al/${pengajuanId}/assign-asesor`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    id_user: userId
                    , tanggal_mulai: tanggalMulai
                    , tanggal_selesai: tanggalSelesai
                    , lokasi_visitasi: lokasiVisitasi
                })
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

    // Remove Asesor
    async function removeAsesor(pengajuanId, userId, userName) {
        const result = await Swal.fire({
            title: 'Konfirmasi Hapus'
            , html: `Hapus <strong>${userName}</strong> dari penugasan AL?`
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Hapus'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/de/penugasan-al/${pengajuanId}/remove-asesor/${userId}`, {
                method: 'DELETE'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                    , timer: 1500
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

    // Show Update Schedule Modal
    function showUpdateScheduleModal(pengajuanId) {
        currentPengajuanId = pengajuanId;

        // Load current schedule data
        @if($hasAsesmenLapangan)
        document.getElementById('updateTanggalMulai').value = '{{ $pengajuan->asesmen->asesmenLapangan->tanggal_mulai }}';
        document.getElementById('updateTanggalSelesai').value = '{{ $pengajuan->asesmen->asesmenLapangan->tanggal_selesai }}';
        document.getElementById('updateLokasiVisitasi').value = '{{ $pengajuan->asesmen->asesmenLapangan->lokasi_visitasi }}';
        @endif

        const modal = new bootstrap.Modal(document.getElementById('modalUpdateSchedule'));
        modal.show();
    }

    // Submit Update Schedule
    async function submitUpdateSchedule(event) {
        event.preventDefault();

        const tanggalMulai = document.getElementById('updateTanggalMulai').value;
        const tanggalSelesai = document.getElementById('updateTanggalSelesai').value;
        const lokasiVisitasi = document.getElementById('updateLokasiVisitasi').value;

        try {
            const response = await fetch(`/de/penugasan-al/${currentPengajuanId}/update-schedule`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    tanggal_mulai: tanggalMulai
                    , tanggal_selesai: tanggalSelesai
                    , lokasi_visitasi: lokasiVisitasi
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                    , timer: 2000
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: data.message
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Terjadi kesalahan: ' + error.message
            });
        }
    }

</script>
@endpush
@endsection
