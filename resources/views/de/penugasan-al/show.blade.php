@extends('layouts.template.app')

@section('title', 'Detail Penugasan AL - ' . $pengajuan->nomor_pengajuan)

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
                <i class="bi bi-geo-alt"></i> Detail Penugasan Asesmen Lapangan
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI && !$pengajuan->asesmen?->asesmenLapangan)
            <button class="btn btn-success" onclick="showMarkReadyModal({{ $pengajuan->id }})">
                <i class="bi bi-check-circle"></i> Tetapkan Siap untuk AL
            </button>
            @endif
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

            <!-- Jadwal Visitasi -->
            @if($pengajuan->asesmen?->asesmenLapangan)
            <div class="card mb-3">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-event"></i> Jadwal Visitasi
                    </h6>
                    <button class="btn btn-sm btn-light" onclick="showUpdateScheduleModal({{ $pengajuan->id }})">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
                <div class="card-body">
                    @php
                    $al = $pengajuan->asesmen->asesmenLapangan;
                    @endphp
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
                        @if($al->catatan)
                        <tr>
                            <td colspan="2" class="pt-2">
                                <small class="text-muted">
                                    📌 Catatan: {{ $al->catatan }}
                                </small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif

            <!-- Requirements Status -->
            @php
            $asesorCount = $pengajuan->asesmen?->asesmenUserRoles
            ->where('jenis_asesmen', 'al')
            ->where('role_selected', 'asesor')
            ->count() ?? 0;

            $requirementsMet = $asesorCount >= 2;
            $missing = [];
            if ($asesorCount < 2) { $missing[]='Minimal 2 asesor diperlukan' ; } @endphp <div class="card">
                <div class="card-header bg-{{ $requirementsMet ? 'success' : 'warning' }} text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-{{ $requirementsMet ? 'check-circle' : 'exclamation-triangle' }}"></i> Status Persyaratan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <i class="bi bi-person"></i> Asesor: <strong>{{ $asesorCount }}</strong> / 2
                    </div>
                    @if(!$requirementsMet)
                    <div class="alert alert-warning mt-3 mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ implode(', ', $missing) }}
                        </small>
                    </div>
                    @else
                    <div class="alert alert-success mt-3 mb-0">
                        <small>
                            <i class="bi bi-check-circle"></i>
                            Persyaratan terpenuhi!
                        </small>
                    </div>
                    @endif
                </div>
        </div>
    </div>

    <!-- Right: Assignments -->
    <div class="col-lg-8">
        <!-- Asesor Assignments -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-people"></i> Daftar Asesor AL
                </h6>
                @if($pengajuan->asesmen && in_array($pengajuan->status, [
                \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED
                ]))
                <button class="btn btn-sm btn-primary" onclick="showAssignAsesorModal({{ $pengajuan->id }})">
                    <i class="bi bi-plus-circle"></i> Tugaskan Asesor
                </button>
                @endif
            </div>
            <div class="card-body">
                @php
                $asesors = $pengajuan->asesmen?->asesmenUserRoles
                ->where('jenis_asesmen', 'al')
                ->where('role_selected', 'asesor')
                ->sortBy('urutan_asesor') ?? collect();
                @endphp

                @if($asesors->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th width="10%">Urutan</th>
                                <th>Nama Asesor</th>
                                <th width="20%">Status Penawaran</th>
                                <th width="20%">Status Pekerjaan</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesors as $asesor)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
                                </td>
                                <td>
                                    <strong>{{ $asesor->user->name }}</strong><br>
                                    <small class="text-muted">{{ $asesor->user->email }}</small>
                                </td>
                                <td>
                                    @php
                                    $penawaranBadge = match($asesor->status_penawaran) {
                                    'pending' => ['class' => 'warning', 'text' => 'Menunggu'],
                                    'accepted' => ['class' => 'success', 'text' => 'Diterima'],
                                    'rejected' => ['class' => 'danger', 'text' => 'Ditolak'],
                                    default => ['class' => 'secondary', 'text' => 'Unknown']
                                    };
                                    @endphp
                                    <span class="badge bg-{{ $penawaranBadge['class'] }}">
                                        {{ $penawaranBadge['text'] }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                    $pekerjaanBadge = match($asesor->status_pekerjaan) {
                                    'not_started' => ['class' => 'secondary', 'text' => 'Belum Mulai'],
                                    'in_progress' => ['class' => 'info', 'text' => 'Dalam Proses'],
                                    'submitted' => ['class' => 'primary', 'text' => 'Sudah Submit'],
                                    default => ['class' => 'secondary', 'text' => 'Unknown']
                                    };
                                    @endphp
                                    <span class="badge bg-{{ $pekerjaanBadge['class'] }}">
                                        {{ $pekerjaanBadge['text'] }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="removeAsesor({{ $pengajuan->id }}, {{ $asesor->id }}, '{{ $asesor->user->name }}')" {{ $asesor->status_pekerjaan !== 'not_started' ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-person-x" style="font-size: 3rem; color: #dee2e6;"></i>
                    <p class="text-muted mt-2">Belum ada asesor yang ditugaskan</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Status Log -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history"></i> Riwayat Status
                </h6>
            </div>
            <div class="card-body">
                @if($pengajuan->statusLog->count() > 0)
                <div class="timeline">
                    @foreach($pengajuan->statusLog->take(10) as $log)
                    <div class="timeline-item mb-3">
                        <div class="d-flex">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="ms-3 flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $log->changedBy->name ?? 'System' }}</strong>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($log->changed_at)->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="mb-1">{{ $log->keterangan }}</p>
                                @if($log->previous_status && $log->new_status)
                                <small class="text-muted">
                                    Status: <code>{{ $log->previous_status }}</code> → <code>{{ $log->new_status }}</code>
                                </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">Belum ada riwayat status</p>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal: Mark Ready for AL -->
<div class="modal fade" id="modalMarkReadyAL" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Tetapkan Siap untuk AL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMarkReadyAL" onsubmit="submitMarkReadyAL(event)">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Tentukan jadwal visitasi lapangan dan lokasi untuk pengajuan ini.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai Visitasi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggalMulaiAL" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai Visitasi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggalSelesaiAL" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Visitasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lokasiVisitasiAL" maxlength="500" placeholder="Contoh: Kampus Universitas XYZ, Jl. Raya No. 123" required>
                        <small class="text-muted">Alamat lengkap lokasi visitasi lapangan</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="catatanAL" rows="3" maxlength="500" placeholder="Catatan tambahan tentang visitasi lapangan..."></textarea>
                        <small class="text-muted"><span id="charCountAL">0</span>/500 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Tetapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Assign Asesor -->
<div class="modal fade" id="modalAssignAsesor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Tugaskan Asesor AL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAssignAsesor" onsubmit="submitAssignAsesor(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Asesor <span class="text-danger">*</span></label>
                        <select class="form-select" id="asesorSelect" required>
                            <option value="">-- Pilih Asesor --</option>
                            @foreach($availableAsesors as $asesor)
                            <option value="{{ $asesor->id }}">{{ $asesor->name }} ({{ $asesor->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Urutan Asesor <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="urutanAsesor" min="1" value="1" required>
                        <small class="text-muted">Urutan penilaian asesor</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Tugaskan
                    </button>
                </div>
            </form>
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
                        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="updateTanggalSelesai" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Visitasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="updateLokasiVisitasi" maxlength="500" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" id="updateCatatan" rows="3" maxlength="500"></textarea>
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

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 20px;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-marker {
        position: absolute;
        left: -20px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

</style>
@endpush

@php
$hasAsesmenLapangan = $pengajuan->asesmen?->asesmenLapangan;
@endphp

@push('scripts')
<script>
    let currentPengajuanId = null;

    // Character counter for catatan
    const catatanAL = document.getElementById('catatanAL')
    if (catatanAL) catatanAL.addEventListener('input', function() {
        document.getElementById('charCountAL').textContent = this.value.length;
    });

    // Show Mark Ready Modal
    function showMarkReadyModal(pengajuanId) {
        currentPengajuanId = pengajuanId;

        // Set default dates
        const today = new Date();
        const nextMonth = new Date(today);
        nextMonth.setMonth(nextMonth.getMonth() + 1);

        document.getElementById('tanggalMulaiAL').value = today.toISOString().split('T')[0];
        document.getElementById('tanggalSelesaiAL').value = nextMonth.toISOString().split('T')[0];
        document.getElementById('lokasiVisitasiAL').value = '';
        document.getElementById('catatanAL').value = '';
        document.getElementById('charCountAL').textContent = '0';

        const modal = new bootstrap.Modal(document.getElementById('modalMarkReadyAL'));
        modal.show();
    }

    // Submit Mark Ready
    async function submitMarkReadyAL(event) {
        event.preventDefault();

        const tanggalMulai = document.getElementById('tanggalMulaiAL').value;
        const tanggalSelesai = document.getElementById('tanggalSelesaiAL').value;
        const lokasiVisitasi = document.getElementById('lokasiVisitasiAL').value;
        const catatan = document.getElementById('catatanAL').value;

        // Validate dates
        if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai!'
            });
            return;
        }

        try {
            const response = await fetch(`/de/penugasan-al/${currentPengajuanId}/mark-ready`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                , body: JSON.stringify({
                    tanggal_mulai: tanggalMulai
                    , tanggal_selesai: tanggalSelesai
                    , lokasi_visitasi: lokasiVisitasi
                    , catatan: catatan
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

    // Show Assign Asesor Modal
    function showAssignAsesorModal(pengajuanId) {
        currentPengajuanId = pengajuanId;
        document.getElementById('formAssignAsesor').reset();
        const modal = new bootstrap.Modal(document.getElementById('modalAssignAsesor'));
        modal.show();
    }

    // Submit Assign Asesor
    async function submitAssignAsesor(event) {
        event.preventDefault();

        const userId = document.getElementById('asesorSelect').value;
        const urutan = document.getElementById('urutanAsesor').value;

        try {
            const response = await fetch(`/de/penugasan-al/${currentPengajuanId}/assign-asesor`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                , body: JSON.stringify({
                    user_id: userId
                    , urutan_asesor: urutan
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

    // Remove Asesor
    async function removeAsesor(pengajuanId, assignmentId, userName) {
        const result = await Swal.fire({
            title: 'Konfirmasi'
            , text: `Hapus ${userName} dari penugasan AL?`
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#d33'
            , cancelButtonColor: '#3085d6'
            , confirmButtonText: 'Ya, Hapus'
            , cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/de/penugasan-al/${pengajuanId}/remove-asesor`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                , body: JSON.stringify({
                    assignment_id: assignmentId
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

    // Show Update Schedule Modal
    function showUpdateScheduleModal(pengajuanId) {
        currentPengajuanId = pengajuanId;

        // Load current schedule data
        @if($hasAsesmenLapangan)
        document.getElementById('updateTanggalMulai').value = '{{ $pengajuan->asesmen->asesmenLapangan->tanggal_mulai }}';
        document.getElementById('updateTanggalSelesai').value = '{{ $pengajuan->asesmen->asesmenLapangan->tanggal_selesai }}';
        document.getElementById('updateLokasiVisitasi').value = '{{ $pengajuan->asesmen->asesmenLapangan->lokasi_visitasi }}';
        document.getElementById('updateCatatan').value = '{{ $pengajuan->asesmen->asesmenLapangan->catatan ?? '
        ' }}';
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
        const catatan = document.getElementById('updateCatatan').value;

        try {
            const response = await fetch(`/de/penugasan-al/${currentPengajuanId}/update-schedule`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                , body: JSON.stringify({
                    tanggal_mulai: tanggalMulai
                    , tanggal_selesai: tanggalSelesai
                    , lokasi_visitasi: lokasiVisitasi
                    , catatan: catatan
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
