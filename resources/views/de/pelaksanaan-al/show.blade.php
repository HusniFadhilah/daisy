@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan AL - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('de.pelaksanaan-al') }}">
                    <i class="bi bi-arrow-left"></i> Pelaksanaan AL
                </a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Detail Pelaksanaan AL
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
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

            <!-- Jadwal Visitasi Info -->
            @if($pengajuan->asesmen?->asesmenLapangan)
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-range"></i> Jadwal Visitasi
                    </h6>
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
                            <td class="text-muted">Tanggal Selesai</td>
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
                            <td class="text-muted">Lokasi</td>
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

            <!-- Berita Acara Status -->
            @if($beritaAcaraProgress)
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-file-earmark-check"></i> Status Berita Acara
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="bi bi-check-circle text-success"></i>
                        <strong>Berita Acara tersedia</strong>
                    </p>
                    <small class="text-muted">
                        Dibuat: {{ \Carbon\Carbon::parse($beritaAcaraProgress->created_at)->format('d M Y H:i') }}
                    </small>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Progress & Assignment -->
        <div class="col-lg-8">
            <!-- Progress Asesor -->
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-people"></i> Progress Asesor AL
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Asesor</th>
                                <th>Status Penawaran</th>
                                <th>Status Pekerjaan</th>
                                <th>Progress Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $asesors = $pengajuan->asesmen?->asesmenUserRoles->filter(function($aur) {
                            return $aur->role_selected->name === 'asesor';
                            });
                            @endphp
                            @forelse($asesors ?? [] as $asesor)
                            @php
                            $progress = $userProgress[$asesor->id_user] ?? ['percentage' => 0, 'completed' => 0, 'total' => 0];
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $asesor->user->name }}</strong>
                                    <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $asesor->status_penawaran === 'accepted' ? 'success' : ($asesor->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($asesor->status_penawaran) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $asesor->status_pekerjaan === 'submitted' ? 'success' : ($asesor->status_pekerjaan === 'in_progress' ? 'info' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $asesor->status_pekerjaan)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $progress['percentage'] == 100 ? 'success' : 'info' }}" style="width: {{ $progress['percentage'] }}%">
                                            {{ $progress['percentage'] }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $progress['completed'] }}/{{ $progress['total'] }} elemen</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Belum ada asesor yang ditugaskan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Validator untuk Pelaporan -->
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-person-check"></i> Validator untuk Rekap & Pelaporan
                    </h6>
                    @if(in_array($pengajuan->status, [
                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                    ]))
                    <button class="btn btn-sm btn-dark" onclick="showAssignValidatorModal({{ $pengajuan->id }})">
                        <i class="bi bi-plus-circle"></i> Tugaskan Validator
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if(!$hasValidator)
                    <div class="alert alert-warning alert-permanent mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Belum ada validator yang ditugaskan</strong>
                        <p class="mb-0 mt-2">
                            Validator diperlukan untuk membuat rekap berita acara dan laporan AL.
                            Silakan tugaskan validator setelah asesor menyelesaikan visitasi.
                        </p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Validator</th>
                                    <th>Status Penawaran</th>
                                    <th>Status Pekerjaan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $validators = $pengajuan->asesmen?->asesmenUserRoles->filter(function($aur) {
                                return $aur->role_selected->name === 'validator';
                                });
                                @endphp
                                @foreach($validators ?? [] as $validator)
                                @php
                                $validatorProgress = $userProgress[$validator->id_user] ?? ['percentage' => 0];
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $validator->user->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $validator->status_penawaran === 'accepted' ? 'success' : ($validator->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($validator->status_penawaran) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $validator->status_pekerjaan === 'submitted' ? 'success' : ($validator->status_pekerjaan === 'in_progress' ? 'info' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $validator->status_pekerjaan)) }}
                                        </span>
                                        @if($validatorProgress['percentage'] > 0)
                                        <div class="progress mt-2" style="height: 15px;">
                                            <div class="progress-bar bg-success" style="width: {{ $validatorProgress['percentage'] }}%">
                                                {{ $validatorProgress['percentage'] }}%
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="removeValidator({{ $pengajuan->id }}, {{ $validator->id_user }}, '{{ $validator->user->name }}')" {{ $validator->status_pekerjaan !== 'not_started' ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Assign Validator -->
<div class="modal fade" id="modalAssignValidator" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-person-check"></i> Tugaskan Validator untuk Pelaporan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAssignValidator" onsubmit="submitAssignValidator(event)">
                <div class="modal-body">
                    <div class="alert alert-info alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        Validator akan bertanggung jawab untuk membuat rekap berita acara dan laporan AL
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Pilih Validator <span class="text-danger">*</span>
                        </label>
                        <select id="validatorUserId" class="form-select" required>
                            <option value="">-- Pilih Validator --</option>
                            @foreach($availableValidators as $validator)
                            <option value="{{ $validator->id }}">{{ $validator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    let currentPengajuanId = null;

    // Show Assign Validator Modal
    function showAssignValidatorModal(pengajuanId) {
        currentPengajuanId = pengajuanId;
        document.getElementById('validatorUserId').value = '';
        const modal = new bootstrap.Modal(document.getElementById('modalAssignValidator'));
        modal.show();
    }

    // Submit Assign Validator
    async function submitAssignValidator(event) {
        event.preventDefault();

        const userId = document.getElementById('validatorUserId').value;

        if (!userId) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Silakan pilih validator terlebih dahulu!'
            });
            return;
        }

        // Close modal
        const modalEl = document.getElementById('modalAssignValidator');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        try {
            const response = await fetch(`/de/pelaksanaan-al/${currentPengajuanId}/assign-validator`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    id_user: userId
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

    // Remove Validator
    async function removeValidator(pengajuanId, userId, userName) {
        const result = await Swal.fire({
            title: 'Konfirmasi Hapus'
            , html: `Hapus <strong>${userName}</strong> dari penugasan validator?`
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Hapus'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/de/pelaksanaan-al/${pengajuanId}/remove-validator/${userId}`, {
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

</script>
@endpush
@endsection
