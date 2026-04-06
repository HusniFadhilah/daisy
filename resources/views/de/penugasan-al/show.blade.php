@extends('layouts.template.app')

@section('title', 'Detail Penugasan AL - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penugasan-al') }}">Penugasan AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-building"></i> Detail Penugasan AL
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.penugasan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            ];

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp

            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AL</strong><br>
                Penugasan asesor AL telah dilakukan. Mohon memastikan asesor telah menyetujui penawaran asesmen lapangan
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Asesmen Lapangan Berlangsung</strong><br>
                Asesor AL telah ditugaskan dan proses asesmen lapangan sedang berlangsung
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Asesmen Lapangan Telah Selesai</strong><br>
                Asesor AL telah ditugaskan dan proses asesmen lapangan telah berlangsung
            </div>
            @endif

            <!-- Surat Tugas Asesor AL -->
            @php
            $suratTugasAsesor = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_asesor_al')
            ->where('is_latest', true)
            ->first();
            @endphp

            @if($suratTugasAsesor)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Surat Tugas Asesor AL
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $suratTugasAsesor->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    @if($suratTugasAsesor->file_size)
                                    {{ number_format($suratTugasAsesor->file_size / 1024, 2) }} KB
                                    @endif
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> Dibuat: {{ $suratTugasAsesor->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                <br>
                                <span class="badge bg-success">Surat Tugas Asesor AL</span>
                                <span class="badge bg-secondary">Versi {{ $suratTugasAsesor->versi }}</span>
                            </div>
                        </div>
                        <div class="btn-group-vertical">
                            @if($suratTugasAsesor->path_file || $suratTugasAsesor->template_link)
                            <a href="{{ route('de.penugasan-al.download-surat-tugas', [$pengajuan->id, 'surat_tugas_asesor_al']) }}" class="btn btn-success mb-2" target="_blank">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                            @endif
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showUploadSuratTugasModal()">
                                <i class="bi bi-upload"></i> Upload Ulang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($pengajuan->asesmen && $pengajuan->asesmen->asesmenUserRoles->where('jenis_asesmen', 'al')->count() > 0)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Surat Tugas Belum Tersedia</strong>
                <br>
                <small>Surat tugas akan dibuat otomatis saat menugaskan asesor pertama, atau klik tombol upload untuk upload manual.</small>
                <button type="button" class="btn btn-sm btn-warning mt-2" onclick="showUploadSuratTugasModal()">
                    <i class="bi bi-upload"></i> Upload Surat Tugas
                </button>
            </div>
            @endif

            <!-- Form Penugasan -->
            {{-- @if(!$requirementsStatus['met']) --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus"></i> Tugaskan Asesor AL
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assignForm" onsubmit="assignAsesor(event, {{ $pengajuan->id }})">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pilih Asesor: <span class="text-danger">*</span></label>
                                <select id="userId" class="form-select" required>
                                    <option value="">-- Pilih Asesor --</option>
                                    @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Surat Tugas <span class="text-danger">*</span></label>
                                <input type="file" id="fileSuratTugas" class="form-control" accept=".pdf">
                                <small class="text-muted">PDF, max 5MB</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Lokasi Visitasi: <span class="text-danger">*</span></label>
                                <input type="text" id="lokasi" class="form-control" maxlength="500" placeholder="Alamat lengkap lokasi visitasi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Mulai: <span class="text-danger">*</span></label>
                                <input type="date" id="tanggalMulai" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estimasi Tanggal Selesai: <span class="text-danger">*</span></label>
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
            {{-- @endif --}}

            <!-- Daftar Penugasan -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Penugasan AL</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    {{-- <th>Progress</th> --}}
                                    <th width="120">Aksi</th>
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
                                        @if($assignment->urutan_asesor)
                                        <span class="badge bg-secondary">#{{ $assignment->urutan_asesor }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $assignment->status_penawaran === 'accepted' ? 'success' : ($assignment->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($assignment->status_penawaran) }}
                                        </span>
                                        {{-- <br>
                                        <small>
                                            <span class="badge bg-{{ $assignment->status_pekerjaan === 'submitted' ? 'success' : ($assignment->status_pekerjaan === 'in_progress' ? 'info' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status_pekerjaan ?? 'not_started')) }}
                                        </span>
                                        </small> --}}
                                    </td>
                                    {{-- <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $progress['percentage'] == 100 ? 'success' : 'info' }}" style="width: {{ $progress['percentage'] }}%">
                                    {{ $progress['percentage'] }}%
                    </div>
                </div>
                <small class="text-muted">{{ $progress['completed'] }}/{{ $progress['total'] }}</small>
                </td> --}}
                <td>
                    @if(!empty($assignmentReminders[$assignment->id]))
                    @php $reminder = $assignmentReminders[$assignment->id]; @endphp

                    <button type="button" class="btn {{ $reminder['btn_class'] }} btn-sm" title="{{ $reminder['label'] }}" onclick="kirimReminderAssignment({{ $assignment->id }},@js($reminder['label']),@js($reminder['message']))">
                        <i class="bi {{ $reminder['icon'] }}"></i>
                    </button>
                    @endif

                    <button class="btn btn-sm btn-danger" onclick="removeAsesor({{ $pengajuan->id }}, {{ $assignment->id_user }}, '{{ $assignment->user->name }}')" {{ ($assignment->status_pekerjaan ?? 'not_started') !== 'not_started' ? 'disabled' : '' }}>
                        <i class="bi bi-trash"></i>
                    </button>
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
    </div>

    <!-- Informasi Penugasan AL -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-info-circle"></i> Informasi Penugasan AL
            </h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th>Tanggal Penugasan Asesor AL</th>
                    <td>
                        : {{ $pengajuan->tanggal_penugasan_asesor_al
                                    ? $pengajuan->tanggal_penugasan_asesor_al->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                    </td>
                </tr>
                @if($pengajuan->asesmen?->asesmenLapangan)
                <tr>
                    <th>Tanggal Mulai AL</th>
                    <td>
                        : {{ $pengajuan->asesmen->asesmenLapangan->tanggal_mulai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_mulai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                    </td>
                </tr>
                <tr>
                    <th>Estimasi Tanggal Selesai AL</th>
                    <td>
                        : {{ $pengajuan->asesmen->asesmenLapangan->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                    </td>
                </tr>
                @if($pengajuan->asesmen->asesmenLapangan->lokasi)
                <tr>
                    <th>Lokasi Visitasi</th>
                    <td>: <i class="bi bi-geo-alt-fill text-danger"></i>
                        {{ $pengajuan->asesmen->asesmenLapangan->lokasi }}
                    </td>
                </tr>
                @endif
                @endif
                <tr>
                    <th>Status Penugasan Asesor AL</th>
                    <td>: {!! $pengajuan->getCustomBadgeLastStatus('penugasan_asesor_al', 'de', 'label_long_for') !!}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Sidebar -->
<div class="col-lg-4">
    <!-- Status Persyaratan -->
    <div class="card mb-4">
        <div class="card-header bg-{{ $requirementsStatus['met'] ? 'success' : 'warning' }} text-white">
            <h5 class="mb-0">
                <i class="bi bi-{{ $requirementsStatus['met'] ? 'check-circle' : 'exclamation-triangle' }}"></i> Status Persyaratan
            </h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless mb-3">
                <tr>
                    <th style="width:60%"><i class="bi bi-person"></i> Asesor</th>
                    <td>: <strong>{{ $requirementsStatus['asesor_count'] }}</strong> / 2</td>
                </tr>
            </table>

            @if(!$requirementsStatus['met'])
            <div class="alert alert-warning alert-permanent mb-0">
                <small>
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ implode(', ', $requirementsStatus['missing']) }} menyetujui penawaran
                </small>
            </div>
            @else
            <div class="alert alert-success alert-permanent mb-0">
                <small>
                    <i class="bi bi-check-circle"></i>
                    Persyaratan terpenuhi!
                </small>
            </div>
            @endif
        </div>
    </div>

    <!-- Riwayat Status -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Riwayat Status
            </h5>
        </div>
        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('created_at')
            ->unique('status_to')
            ->values();
            @endphp

            @if($logs->count() > 0)
            <div class="timeline">
                @foreach($logs as $log)
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            @php
                            $iconColor = match($log->status_to) {
                            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED
                            => 'text-success',
                            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS
                            => 'text-info',
                            default => 'text-secondary',
                            };
                            @endphp
                            <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                            </strong>
                            <br>
                            <small class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>

                            {{-- @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                            @endif --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-muted text-center mb-0">Belum ada riwayat penugasan</p>
            @endif
        </div>
    </div>

    <!-- Jadwal Visitasi AL -->
    @if($pengajuan->asesmen?->asesmenLapangan)
    <div class="card mt-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-range"></i> Jadwal Visitasi AL
            </h5>
            <button class="btn btn-sm btn-light" onclick="showUpdateScheduleModal({{ $pengajuan->id }})">
                <i class="bi bi-pencil"></i>
            </button>
        </div>
        <div class="card-body">
            @php $al = $pengajuan->asesmen->asesmenLapangan; @endphp
            <table class="table table-sm table-borderless mb-0">
                @if($al->tanggal_mulai)
                <tr>
                    <th class="text-muted" width="45%">Tanggal Mulai</th>
                    <td>: <strong>{{ \Carbon\Carbon::parse($al->tanggal_mulai)->locale('id')->translatedFormat('d M Y') }}</strong></td>
                </tr>
                @endif
                @if($al->tanggal_selesai)
                <tr>
                    <th class="text-muted">Estimasi Selesai</th>
                    <td>: <strong>{{ \Carbon\Carbon::parse($al->tanggal_selesai)->locale('id')->translatedFormat('d M Y') }}</strong></td>
                </tr>
                @endif
                @if($al->tanggal_mulai && $al->tanggal_selesai)
                <tr>
                    <th class="text-muted">Durasi</th>
                    <td>:
                        @php
                        $start = \Carbon\Carbon::parse($al->tanggal_mulai);
                        $end = \Carbon\Carbon::parse($al->tanggal_selesai);
                        $days = $start->diffInDays($end);
                        @endphp
                        <span class="badge bg-primary">{{ $days }} hari</span>
                    </td>
                </tr>
                @endif
                @if($al->lokasi)
                <tr>
                    <th class="text-muted">Lokasi Visitasi</th>
                    <td>:
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        {{ $al->lokasi }}
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif
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
                        <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="updateTanggalMulai" value="{{ old('updateTanggalMulai',optional($pengajuan->asesmen->asesmenLapangan?->tanggal_mulai)?->format('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estimasi Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="updateTanggalSelesai" value="{{ old('updateTanggalSelesai',optional($pengajuan->asesmen->asesmenLapangan?->tanggal_selesai)?->format('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lokasi Visitasi <span class="text-danger">*</span></label>
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

<!-- Modal: Upload Surat Tugas -->
<div class="modal fade" id="modalUploadSuratTugas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-upload"></i> Upload Surat Tugas Asesor AL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUploadSuratTugas" method="POST" action="{{ route('de.penugasan-al.upload-surat-tugas', [$pengajuan->id, 'surat_tugas_asesor_al']) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            File Surat Tugas (PDF) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_surat_tugas" id="modalFileSuratTugas" class="form-control" accept=".pdf" required>
                        <small class="text-muted">Format: PDF | Maksimal: 5MB</small>
                        <div id="modalSuratTugasPreview" class="mt-2"></div>
                    </div>

                    <div class="alert alert-info alert-permanent mb-0">
                        <i class="bi bi-info-circle"></i>
                        File yang diupload akan menggantikan surat tugas sebelumnya (jika ada).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
$hasAsesmenLapangan = $pengajuan->asesmen?->asesmenLapangan;
@endphp

@include('layouts.template.kirim-reminder')
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
        const lokasi = document.getElementById('lokasi').value;
        const fileSuratTugas = document.getElementById('fileSuratTugas').files[0];

        // Validate dates
        if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Estimasi tanggal selesai tidak boleh lebih awal dari tanggal mulai!'
            });
            return;
        }

        const formData = new FormData();
        formData.append('id_user', userId);
        formData.append('tanggal_mulai', tanggalMulai);
        formData.append('tanggal_selesai', tanggalSelesai);
        formData.append('lokasi', lokasi);

        if (fileSuratTugas) {
            formData.append('file_surat_tugas', fileSuratTugas);
        }

        try {
            const response = await fetch(`/de/penugasan-al/${pengajuanId}/assign-asesor`, {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
                , body: formData
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

    function showUploadSuratTugasModal() {
        document.getElementById('modalFileSuratTugas').value = '';
        document.getElementById('modalSuratTugasPreview').innerHTML = '';

        const modal = new bootstrap.Modal(document.getElementById('modalUploadSuratTugas'));
        modal.show();
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
        document.getElementById('updateTanggalMulai').value = "{{ optional($pengajuan->asesmen->asesmenLapangan->tanggal_mulai)?->format('Y-m-d') }}";
        document.getElementById('updateTanggalSelesai').value = "{{ optional($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)?->format('Y-m-d') }}";
        document.getElementById('updateLokasiVisitasi').value = '{{ $pengajuan->asesmen->asesmenLapangan->lokasi }}';
        @endif

        const modal = new bootstrap.Modal(document.getElementById('modalUpdateSchedule'));
        modal.show();
    }

    // Submit Update Schedule
    async function submitUpdateSchedule(event) {
        event.preventDefault();

        const tanggalMulai = document.getElementById('updateTanggalMulai').value;
        const tanggalSelesai = document.getElementById('updateTanggalSelesai').value;
        const lokasi = document.getElementById('updateLokasiVisitasi').value;

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
                    , lokasi: lokasi
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

    const modalFileSuratTugas = document.getElementById('modalFileSuratTugas');
    if (modalFileSuratTugas) {
        modalFileSuratTugas.addEventListener('change', function(e) {
            const preview = document.getElementById('modalSuratTugasPreview');
            const file = e.target.files[0];

            if (!file) {
                preview.innerHTML = '';
                return;
            }

            const fileSize = file.size / 1024 / 1024;

            if (file.type !== 'application/pdf') {
                preview.innerHTML =
                    '<div class="alert alert-danger alert-dismissible fade show">' +
                    '<i class="bi bi-x-circle"></i> File harus berformat PDF' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                e.target.value = '';
                return;
            }

            if (fileSize > 5) {
                preview.innerHTML =
                    '<div class="alert alert-danger alert-dismissible fade show">' +
                    '<i class="bi bi-x-circle"></i> Ukuran file terlalu besar. Maksimal 5 MB' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                e.target.value = '';
                return;
            }

            preview.innerHTML =
                '<div class="alert alert-success alert-dismissible fade show">' +
                '<i class="bi bi-check-circle"></i> ' +
                '<strong>' + file.name + '</strong> (' + fileSize.toFixed(2) + ' MB)' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
        });
    }

</script>
@endpush
@endsection
