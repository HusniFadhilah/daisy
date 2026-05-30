@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan AL - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaksanaan-al') }}">Pelaksanaan AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-geo-alt"></i> Detail Pelaksanaan AL
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.pelaksanaan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ];

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp

            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Asesmen Lapangan Berlangsung</strong><br>
                Proses asesmen lapangan sedang berlangsung. Pantau progres penilaian asesor secara berkala
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Asesmen Lapangan Selesai</strong><br>
                Asesmen lapangan telah selesai dilaksanakan. <br>Menunggu validator untuk membuat rekap dan laporan
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Asesmen Lapangan Selesai</strong><br>
                Asesmen lapangan telah selesai dilaksanakan
            </div>
            @endif

            <!-- Progress Asesor AL -->
            {{-- <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Progress Asesor AL
                    </h5>
                    <button class="btn btn-sm btn-light" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Asesor</th>
                                    <th>Status</th>
                                    <th>Progres Penilaian</th>
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
            @if($asesor->urutan_asesor)
            <span class="badge bg-secondary">#{{ $asesor->urutan_asesor }}</span>
            @endif
            </td>
            <td>
                <span class="badge bg-{{ $asesor->status_penawaran === 'accepted' ? 'success' : ($asesor->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($asesor->status_penawaran) }}
                </span>
                <br>
                <small>
                    <span class="badge bg-{{ $asesor->status_pekerjaan === 'submitted' ? 'success' : ($asesor->status_pekerjaan === 'in_progress' ? 'info' : 'secondary') }}">
                        {{ ucfirst(str_replace('_', ' ', $asesor->status_pekerjaan ?? 'not_started')) }}
                    </span>
                </small>
            </td>
            <td>
                <div class="progress mb-1" style="height: 20px;">
                    <div class="progress-bar bg-{{ $progress['percentage'] == 100 ? 'success' : 'info' }}" style="width: {{ $progress['percentage'] }}%">
                        {{ $progress['percentage'] }}%
                    </div>
                </div>
                <small class="text-muted">{{ $progress['completed'] }}/{{ $progress['total'] }} elemen</small>
            </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center py-4 text-muted">
                    Belum ada asesor yang ditugaskan
                </td>
            </tr>
            @endforelse
            </tbody>
            </table>
        </div>
    </div>
</div> --}}

<!-- Validator untuk Pelaporan -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h5 class="mb-0">
            <i class="bi bi-person-check"></i> Validator untuk Rekap & Pelaporan
        </h5>
        <button class="btn btn-sm btn-dark" onclick="showAssignValidatorModal({{ $pengajuan->id }})">
            <i class="bi bi-plus-circle"></i> Tugaskan Validator
        </button>
    </div>
    <div class="card-body">
        @if(!$hasValidator)
        <div class="alert alert-warning alert-permanent mb-0">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Belum ada validator yang ditugaskan</strong>
            <p class="mb-0 mt-2">
                Validator diperlukan untuk membuat rekap laporan AL
            </p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama Validator</th>
                        <th>Status</th>
                        {{-- <th>Progress</th> --}}
                        <th width="120">Aksi</th>
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
                            {{-- <br>
                            <small>
                                <span class="badge bg-{{ $validator->status_pekerjaan === 'submitted' ? 'success' : ($validator->status_pekerjaan === 'in_progress' ? 'info' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $validator->status_pekerjaan ?? 'not_started')) }}
                            </span>
                            </small> --}}
                        </td>
                        {{-- <td>
                            @if($validatorProgress['percentage'] > 0)
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: {{ $validatorProgress['percentage'] }}%">
                        {{ $validatorProgress['percentage'] }}%
        </div>
    </div>
    @else
    <small class="text-muted">Belum dimulai</small>
    @endif
    </td> --}}
    <td>
        @if(!empty($assignmentReminders[$validator->id]))
        @php $reminder = $assignmentReminders[$validator->id]; @endphp

        <button type="button" class="btn {{ $reminder['btn_class'] }} btn-sm" title="{{ $reminder['label'] }}" onclick="kirimReminderAssignment({{ $validator->id }},@js($reminder['label']),@js($reminder['message']))">
            <i class="bi {{ $reminder['icon'] }}"></i>
        </button>
        @endif
        <button class="btn btn-sm btn-danger" onclick="removeValidator({{ $pengajuan->id }}, {{ $validator->id_user }}, '{{ $validator->user->name }}')" {{ ($validator->status_pekerjaan ?? 'not_started') !== 'not_started' ? 'disabled' : '' }}>
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


<!-- Berita Acara Asesmen Lapangan -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white d-flex flex-column flex-md-row
                justify-content-between align-items-start align-items-md-center gap-2">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Berita Acara Asesmen Lapangan
        </h5>

        {{-- Tombol reminder: tampil hanya jika BA belum ada --}}
        @if(!$beritaAcaraProgress)
        <button type="button" class="btn btn-sm btn-warning" onclick="showModalReminderBeritaAcara()">
            <i class="bi bi-bell"></i> Ingatkan Asesor Upload BA
        </button>
        @endif
    </div>

    <div class="card-body">
        @php
        $beritaAcaraList = $pengajuan->asesmen?->beritaAcaraAL ?? collect([]);
        @endphp

        @if($beritaAcaraList->count() > 0)
        <div class="alert alert-info alert-permanent mb-3">
            <i class="bi bi-info-circle"></i>
            Berikut adalah berita acara pelaksanaan asesmen lapangan
        </div>

        @foreach($beritaAcaraList as $index => $beritaAcara)
        <div class="card mb-3 border">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-start mb-2 gap-3">
                    <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 40px;"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <strong>{{ $beritaAcara->title }}</strong>
                        </h6>
                        <small class="text-muted">
                            Diupload: {{ $beritaAcara->uploaded_at ? $beritaAcara->uploaded_at->locale('id')->translatedFormat('d M Y H:i') : '-' }}
                        </small>
                        <br>
                        <small class="text-muted">
                            Oleh: {{ $beritaAcara->uploader->name ?? '-' }}
                        </small>
                    </div>
                    <div class="btn-group-vertical" role="group">
                        <a href="{{ route('al.berkas.documents.preview', ['id' => $pengajuan->asesmen->id, 'docId' => $beritaAcara->id]) }}" class="btn btn-success mb-2" target="_blank">
                            <i class="bi bi-eye"></i> Lihat File
                        </a>
                        <a href="{{ route('al.berkas.documents.download', ['id' => $pengajuan->asesmen->id, 'docId' => $beritaAcara->id]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @else
        <div class="text-center py-4">
            <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #dee2e6;"></i>
            <p class="text-muted mt-2 mb-0">Belum ada berita acara yang diupload</p>
            <small class="text-muted">Berita acara akan diupload oleh asesor setelah visitasi selesai</small>
        </div>
        @endif
    </div>
</div>

<!-- Laporan Hasil Asesmen (LHA) -->
<div class="card mb-4">
    <div class="card-header bg-info text-white d-flex flex-column flex-md-row
                justify-content-between align-items-start align-items-md-center gap-2">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-check"></i> Laporan Hasil Asesmen Lapangan (LHA)
        </h5>

        @php
        $lhaList = $pengajuan->asesmen?->documents()
        ->where('type', 'lha_asesor')
        ->where('is_active', true)
        ->latest('uploaded_at')
        ->get() ?? collect([]);

        $lhaPending = $lhaList->firstWhere('status_persetujuan_prodi', 'pending');
        $lhaRevisi = $lhaList->firstWhere('status_persetujuan_prodi', 'revision_required');
        $lhaApproved = $lhaList->firstWhere('status_persetujuan_prodi', 'approved');
        $lhaBelumAda = $lhaList->isEmpty();
        @endphp

        <div class="d-flex gap-2 flex-wrap">
            {{-- Reminder ke Asesor: jika LHA belum ada ATAU ada permintaan revisi --}}
            @if(($lhaBelumAda || $lhaRevisi) && !$lhaApproved)
            <button type="button" class="btn btn-sm btn-warning" onclick="showModalReminderAsesor()">
                <i class="bi bi-bell"></i>
                {{ $lhaRevisi ? 'Ingatkan Asesor (Revisi LHA)' : 'Ingatkan Asesor Finalisasi LHA' }}
            </button>
            @endif

            {{-- Reminder ke UPPS: jika LHA pending --}}
            @if($lhaPending && !$lhaApproved)
            <button type="button" class="btn btn-sm btn-primary" onclick="showModalReminderUPPS()">
                <i class="bi bi-bell"></i> Ingatkan UPPS Tinjau LHA
            </button>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if($lhaList->count() > 0)
        <div class="alert alert-info alert-permanent mb-3">
            <i class="bi bi-info-circle"></i>
            Berikut adalah laporan hasil asesmen lapangan yang telah diupload oleh asesor
        </div>

        @foreach($lhaList as $index => $lha)
        <div class="card mb-3 border-{{
            $lha->status_persetujuan_prodi === 'approved'           ? 'success' :
            ($lha->status_persetujuan_prodi === 'rejected'          ? 'danger'  :
            ($lha->status_persetujuan_prodi === 'revision_required'  ? 'warning' : 'secondary'))
        }}">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-start mb-2 gap-3">
                    <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 40px;"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <strong>{{ $lha->title }}</strong>
                        </h6>
                        <small class="text-muted">
                            Diupload: {{ $lha->uploaded_at ? $lha->uploaded_at->locale('id')->translatedFormat('d M Y H:i') : '-' }}
                        </small>
                        <br>
                        <small class="text-muted">
                            Oleh: {{ $lha->uploadedBy->name ?? '-' }}
                        </small>
                        <br>
                        <span class="badge {{ $lha->status_prodi_badge_class ?? 'bg-secondary' }} mt-1">
                            {{ $lha->status_prodi_label ?? 'Menunggu Peninjauan' }}
                        </span>
                    </div>
                    <a href="{{ route('al.berkas.documents.preview', ['id' => $pengajuan->asesmen->id, 'docId' => $lha->id]) }}" class="btn btn-sm btn-success" target="_blank">
                        <i class="bi bi-eye"></i> Lihat File
                    </a>
                </div>

                {{-- Catatan Prodi --}}
                @if($lha->catatan_prodi)
                <div class="alert alert-light alert-permanent border mt-3 mb-0">
                    <strong><i class="bi bi-chat-left-text"></i> Catatan Program Studi:</strong><br>
                    {{ $lha->catatan_prodi }}
                    @if($lha->approved_at_prodi)
                    <br><small class="text-muted">
                        <i class="bi bi-clock"></i>
                        {{ $lha->approved_at_prodi->locale('id')->translatedFormat('d M Y H:i') }}
                    </small>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endforeach

        @else
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-x" style="font-size: 64px; color: #dee2e6;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada laporan hasil asesmen yang diupload</p>
            <small class="text-muted">LHA akan diupload oleh asesor setelah visitasi selesai</small>
        </div>
        @endif
    </div>
</div>

<!-- Informasi Pelaksanaan AL -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">
            <i class="bi bi-info-circle"></i> Informasi Pelaksanaan AL
        </h5>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
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
                <th>Tanggal Selesai AL</th>
                <td>
                    : {{ $pengajuan->asesmen->asesmenLapangan->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                </td>
            </tr>
            @if($pengajuan->asesmen->asesmenLapangan->lokasi_visitasi)
            <tr>
                <th>Lokasi Visitasi</th>
                <td>: <i class="bi bi-geo-alt-fill text-danger"></i>
                    {{ $pengajuan->asesmen->asesmenLapangan->lokasi_visitasi }}
                </td>
            </tr>
            @endif
            @endif
            <tr>
                <th>Status Pelaksanaan AL</th>
                <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaksanaan_al', 'de', 'label_long_for') !!}</td>
            </tr>
        </table>
    </div>
</div>
</div>

<!-- Sidebar -->
<div class="col-lg-4">
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
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('changed_at')
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
                            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI
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
            <p class="text-muted text-center mb-0">Belum ada riwayat pelaksanaan</p>
            @endif
        </div>
    </div>

    <!-- Jadwal Visitasi -->
    @if($pengajuan->asesmen?->asesmenLapangan)
    <div class="card my-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-calendar-range"></i> Jadwal Visitasi
            </h5>
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
                    <th class="text-muted">Tanggal Selesai</th>
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
                @if($al->lokasi_visitasi)
                <tr>
                    <th class="text-muted">Lokasi</th>
                    <td>:
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        {{ $al->lokasi_visitasi }}
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    <!-- Status Berita Acara -->
    @if($beritaAcaraProgress)
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-check"></i> Status Berita Acara
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-check-circle-fill text-success me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Berita Acara Tersedia</strong>
                    <br>
                    <small class="text-muted">
                        Dibuat: {{ \Carbon\Carbon::parse($beritaAcaraProgress->created_at)->locale('id')->translatedFormat('d M Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif
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
                        Validator akan bertanggung jawab untuk membuat rekap laporan AL
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Pilih Validator <span class="text-danger">*</span>
                        </label>
                        <select id="validatorUserId" class="form-select" required data-no-select2>
                            <option value="">-- Pilih Validator --</option>
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

@include('de.pelaksanaan-al.components.modal-reminder')
@include('layouts.template.kirim-reminder')

@push('scripts')
<script>
    $(document).ready(function () {
        $('#validatorUserId').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih Validator --',
            allowClear: true,
            dropdownParent: $('#modalAssignValidator'),
            ajax: {
                url: '{{ route("ajax.users.search") }}',
                dataType: 'json',
                delay: 250,
                cache: true,
                data: function (params) {
                    return { q: params.term, page: params.page || 1, role: 'validator' };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return { results: data.results, pagination: data.pagination };
                },
            },
        });
    });

    const csrfToken = '{{ csrf_token() }}';
    let currentPengajuanId = null;

    // Show Assign Validator Modal
    function showAssignValidatorModal(pengajuanId) {
        currentPengajuanId = pengajuanId;
        $('#validatorUserId').val(null).trigger('change');
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
