@extends('layouts.template.app')

@section('title', 'Detail Penugasan AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penugasan-ak') }}">Penugasan AK</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-person-check"></i> Detail Penugasan AK
            </h5>
            <small class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED
            ]) && !$pengajuan->asesmen?->asesmenKecukupan)
            <button class="btn btn-success" onclick="showMarkReadyModal({{ $pengajuan->id }})">
                <i class="bi bi-check-circle"></i> Tetapkan Siap untuk AK
            </button>
            @endif
            <a href="{{ route('de.penugasan-ak') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi & Form -->
        <div class="col-lg-8 mb-4">
            @if($pengajuan->asesmen?->asesmenKecukupan)
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AK</strong><br>
                Penugasan asesor AK telah dilakukan<br>
                Mohon memastikan asesor dan validator telah menyetujui penawaran asesmen
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AK</strong><br>
                Asesor AK telah ditugaskan untuk melakukan penilaian
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Penugasan Asesor AK</strong><br>
                Asesor AK telah ditugaskan dan telah menyelesaikan penilaian
            </div>
            @endif

            <!-- Form Penugasan -->
            @if(!$requirementsStatus['met'])
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus"></i> Tugaskan Asesor / Validator
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assignForm" onsubmit="assignUser(event, {{ $pengajuan->id }})">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Role: <span class="text-danger">*</span></label>
                                <select id="roleId" class="form-select" required onchange="handleRoleChange()">
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-name="{{ $role->name }}">{{ $role->alias }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8" id="validatorOptionsContainer" style="display: none;">
                                {{-- Alert Info Validator Dokumen --}}
                                @if($validatorDokumen)
                                <div class="alert alert-info alert-permanent mb-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Validator Dokumen:</strong> {{ $validatorDokumen->user->name }}
                                            <br>
                                            <small class="text-muted">
                                                Gunakan validator yang sama atau pilih validator lain untuk AK
                                            </small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="showValidatorInfo()">
                                            <i class="bi bi-person-check"></i> Lihat Detail
                                        </button>
                                    </div>
                                </div>
                                @endif

                                @if($validatorDokumen)
                                <label class="form-label fw-bold">Pilih Validator:</label>
                                <div class="btn-group btn-sm w-100" role="group">
                                    <input type="radio" class="btn-check" name="validator_option" id="useValidatorDokumen" value="use_existing" checked autocomplete="off">
                                    <label class="btn btn-outline-primary" for="useValidatorDokumen">
                                        <i class="bi bi-person-check"></i>
                                        Gunakan Validator Dokumen
                                        <br>
                                        <small>{{ $validatorDokumen->user->name }}</small>
                                    </label>

                                    <input type="radio" class="btn-check" name="validator_option" id="useNewValidator" value="new" autocomplete="off">
                                    <label class="btn btn-outline-success" for="useNewValidator">
                                        <i class="bi bi-person-plus"></i>
                                        Pilih Validator Lain
                                    </label>
                                </div>
                                @else
                                <div class="alert alert-warning alert-permanent mb-0">
                                    <small><i class="bi bi-exclamation-triangle"></i> Belum ada validator dokumen. Silakan pilih validator baru.</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mt-2" id="userSelectionContainer">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Pilih User: <span class="text-danger">*</span></label>
                                <select id="userId" class="form-select" required>
                                    <option value="">-- Pilih User --</option>
                                    @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Surat Tugas <span class="text-danger">*</span></label>
                                <input type="file" id="fileSuratTugas" name="file_surat_tugas" class="form-control" accept=".pdf">
                                <small class="text-muted">PDF, max 5MB</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Tugaskan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Daftar Penugasan -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Penugasan AK</h5>
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
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Surat Tugas</th>
                                    <th width="80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $suratTugasAsesor = $pengajuan->dokumen
                                ->where('jenis_dokumen', 'surat_tugas_asesor_ak')
                                ->where('is_latest', true)
                                ->first();

                                $suratTugasValidator = $pengajuan->dokumen
                                ->where('jenis_dokumen', 'surat_tugas_validator_ak')
                                ->where('is_latest', true)
                                ->first();
                                @endphp

                                @forelse($pengajuan->asesmen->asesmenUserRoles as $assignment)
                                @php
                                $progress = $userProgress[$assignment->id_user] ?? ['percentage' => 0, 'completed' => 0, 'total' => 0];

                                $suratTugas = $assignment->role_selected->name === 'asesor'
                                ? $suratTugasAsesor
                                : $suratTugasValidator;

                                $jenisDokumen = $assignment->role_selected->name === 'asesor'
                                ? 'surat_tugas_asesor_ak'
                                : 'surat_tugas_validator_ak';
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->user->name }}</strong>
                                        @if($assignment->urutan_asesor)
                                        <span class="badge bg-secondary">#{{ $assignment->urutan_asesor }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->role_selected->alias }}</td>
                                    <td>
                                        <span class="badge bg-{{ $assignment->status_penawaran === 'accepted' ? 'success' : ($assignment->status_penawaran === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($assignment->status_penawaran) }}
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
                                        @if($suratTugas)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-danger"></i>
                                            <div class="flex-grow-1">
                                                <small class="d-block">{{ Str::limit($suratTugas->original_filename, 20) }}</small>
                                                <small class="text-muted">Versi {{ $suratTugas->versi }}</small>
                                            </div>

                                            @php
                                            $showActions = false;
                                            if ($assignment->role_selected->name === 'asesor') {
                                            $showActions = $assignment->urutan_asesor === 1;
                                            } else {
                                            $showActions = true;
                                            }
                                            @endphp

                                            @if($showActions)
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('de.penugasan-ak.download-surat-tugas', [$pengajuan->id, $jenisDokumen]) }}" class="btn btn-sm btn-success" target="_blank" title="Download">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                {{-- <button type="button" class="btn btn-sm btn-outline-primary" onclick="showUploadSuratTugasModal('{{ $jenisDokumen }}')" title="Upload Ulang">
                                                <i class="bi bi-upload"></i> Upload Ulang
                                                </button> --}}
                                            </div>
                                            @else
                                            <a href="{{ route('de.penugasan-ak.download-surat-tugas', [$pengajuan->id, $jenisDokumen]) }}" class="btn btn-sm btn-success" target="_blank" title="Download">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif
                                        </div>
                                        @else
                                        @php
                                        $showUpload = false;
                                        if ($assignment->role_selected->name === 'asesor') {
                                        $showUpload = $assignment->urutan_asesor === 1;
                                        } else {
                                        $showUpload = true;
                                        }
                                        @endphp

                                        @if($showUpload)
                                        <button type="button" class="btn btn-sm btn-warning" onclick="showUploadSuratTugasModal('{{ $jenisDokumen }}')">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                        @else
                                        <small class="text-muted">-</small>
                                        @endif
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="removeUser({{ $pengajuan->id }}, {{ $assignment->id_user }}, '{{ $assignment->user->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada yang ditugaskan
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @else
            <!-- Belum Siap -->
            <div class="alert alert-warning alert-permanent">
                <h5><i class="bi bi-exclamation-triangle"></i> Belum Siap untuk AK</h5>
                <p class="mb-0">Silakan tetapkan status "Siap untuk AK" terlebih dahulu dengan mengklik tombol di kanan atas.</p>
            </div>
            @endif

            <!-- Informasi Program Studi -->
            <div class="card my-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Penugasan Asesor AK</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penugasan Asesor AK</th>
                            <td>
                                : {{ $pengajuan->tanggal_penugasan_asesor_ak
                                    ? $pengajuan->tanggal_penugasan_asesor_ak->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai AK</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenKecukupan?->tanggal_mulai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal AK Selesai</th>
                            <td>
                                : {{ $pengajuan->asesmen?->asesmenKecukupan?->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penugasan Asesor AK</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penugasan_asesor_ak', 'de', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
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
                        <tr>
                            <th><i class="bi bi-person-check"></i> Validator</th>
                            <td>: <strong>{{ $requirementsStatus['validator_count'] }}</strong> / 1</td>
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

            <!-- Timeline Penugasan -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
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
                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                                    => 'text-success',
                                    default => 'text-info',
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
        </div>
    </div>
</div>

<!-- Modal: Mark Ready for AK -->
<div class="modal fade" id="modalMarkReadyAK" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Tetapkan Siap untuk AK
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMarkReadyAK" onsubmit="submitMarkReadyAK(event)">
                <div class="modal-body">
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Tentukan periode pelaksanaan Asesmen Kecukupan (AK)
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Tanggal Mulai AK <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="tanggalMulaiAK" name="tanggal_mulai" required>
                        <small class="text-muted">Tanggal mulai pelaksanaan asesmen kecukupan</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Estimasi Tanggal Selesai AK <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="tanggalSelesaiAK" name="tanggal_selesai" required>
                        <small class="text-muted">Estimasi target selesai asesmen kecukupan</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Catatan <span class="text-muted">(Opsional)</span>
                        </label>
                        <textarea class="form-control" id="catatanAK" name="catatan" rows="3" maxlength="500" placeholder="Catatan tambahan..."></textarea>
                        <small class="text-muted">Maksimal 500 karakter</small>
                    </div>

                    <div class="alert alert-warning alert-permanent mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong> Pastikan periode yang ditentukan cukup untuk proses asesmen
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Tetapkan
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
                    <i class="bi bi-upload"></i> Upload Surat Tugas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUploadSuratTugas" method="POST" enctype="multipart/form-data">
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

{{-- ✅ NEW: Add Modal Validator Dokumen Info --}}
@if(isset($validatorDokumen) && $validatorDokumen)
<div class="modal fade" id="modalValidatorInfo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-check"></i> Info Validator Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Nama</th>
                        <td>: {{ $validatorDokumen->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>: {{ $validatorDokumen->user->email }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>:
                            <span class="badge bg-{{ $validatorDokumen->status_penawaran === 'accepted' ? 'success' : 'warning' }}">
                                {{ ucfirst($validatorDokumen->status_penawaran) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Ditugaskan</th>
                        <td>: {{ $validatorDokumen->created_at->locale('id')->translatedFormat('d M Y H:i') }}</td>
                    </tr>
                </table>

                @php
                $suratTugasValDok = $pengajuan->dokumen
                ->where('jenis_dokumen', 'surat_tugas_validator_dokumen')
                ->where('is_latest', true)
                ->first();
                @endphp

                @if($suratTugasValDok)
                <div class="alert alert-success alert-permanent">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <strong>Surat Tugas Validator:</strong><br>
                    {{ $suratTugasValDok->original_filename }}
                    <br>
                    <a href="{{ route('de.penerimaan-dokumen.download-surat-tugas-validator', $pengajuan->id) }}" class="btn btn-sm btn-success mt-2" target="_blank">
                        <i class="bi bi-eye"></i> Lihat
                    </a>
                </div>
                @endif

                <div class="alert alert-info alert-permanent mb-0">
                    <i class="bi bi-info-circle"></i>
                    <strong>Catatan:</strong><br>
                    Jika Anda menggunakan validator yang sama, penugasan akan langsung diterima (accepted) tanpa penawaran,
                    dan surat tugas akan otomatis sama dengan surat tugas validator dokumen.
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    const pengajuanId = "{{ $pengajuan->id }}";
    let currentPengajuanId = pengajuanId;
    let issetValidatorDokumen = "{{ isset($validatorDokumen) && $validatorDokumen }}"

    @if(isset($validatorDokumen) && $validatorDokumen)
    const validatorDokumenId = "{{ $validatorDokumen->id_user }}";
    const validatorDokumenName = '{{ $validatorDokumen->user->name }}';
    @else
    const validatorDokumenId = null;
    const validatorDokumenName = null;
    @endif

    function showMarkReadyModal(pengajuanId) {
        currentPengajuanId = pengajuanId;
        const today = new Date();
        const nextMonth = new Date(today);
        nextMonth.setMonth(nextMonth.getMonth() + 1);

        document.getElementById('tanggalMulaiAK').value = today.toISOString().split('T')[0];
        document.getElementById('tanggalSelesaiAK').value = nextMonth.toISOString().split('T')[0];
        document.getElementById('catatanAK').value = '';

        const modal = new bootstrap.Modal(document.getElementById('modalMarkReadyAK'));
        modal.show();
    }

    async function submitMarkReadyAK(event) {
        event.preventDefault();

        const tanggalMulai = document.getElementById('tanggalMulaiAK').value;
        const tanggalSelesai = document.getElementById('tanggalSelesaiAK').value;
        const catatan = document.getElementById('catatanAK').value;

        if (!tanggalMulai || !tanggalSelesai) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Tanggal mulai dan selesai harus diisi!'
            });
            return;
        }

        if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Estimasi tanggal selesai tidak boleh lebih awal dari tanggal mulai!'
            });
            return;
        }

        const modalEl = document.getElementById('modalMarkReadyAK');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        Swal.fire({
            title: 'Memproses...'
            , html: 'Sedang menetapkan status AK siap'
            , allowOutsideClick: false
            , didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await fetch(`/de/penugasan-ak/${currentPengajuanId}/mark-ready`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    tanggal_mulai: tanggalMulai
                    , tanggal_selesai: tanggalSelesai
                    , catatan: catatan
                })
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , html: `
                        <p>${data.message}</p>
                        <hr>
                        <small class="text-muted">
                            <strong>Periode AK:</strong><br>
                            ${formatDate(data.data.tanggal_mulai)} - ${formatDate(data.data.tanggal_selesai)}
                        </small>
                    `
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

    async function assignUser(event, pengajuanId) {
        event.preventDefault();

        const roleId = document.getElementById('roleId').value;
        const userId = document.getElementById('userId').value;
        const fileSuratTugas = document.getElementById('fileSuratTugas').files[0];
        const validatorDokumen = document.getElementById('useValidatorDokumen');
        const useValidatorDokumen = validatorDokumen ? validatorDokumen.checked : false;

        if (!roleId || !userId) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Mohon lengkapi semua field!'
            });
            return;
        }

        const formData = new FormData();
        formData.append('id_user', userId);
        formData.append('id_role', roleId);
        formData.append('use_validator_dokumen', useValidatorDokumen ? '1' : '0');

        if (fileSuratTugas) {
            formData.append('file_surat_tugas', fileSuratTugas);
        }

        try {
            const response = await fetch(`/de/penugasan-ak/${pengajuanId}/assign-user`, {
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

    async function removeUser(pengajuanId, userId, userName) {
        const result = await Swal.fire({
            title: 'Konfirmasi Hapus'
            , html: `Hapus <strong>${userName}</strong> dari penugasan AK?`
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Hapus'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/de/penugasan-ak/${pengajuanId}/remove-user/${userId}`, {
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

    function formatDate(dateStr) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        const date = new Date(dateStr);
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    function handleRoleChange() {
        const roleSelect = document.getElementById('roleId');
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.dataset.name;
        const validatorOptions = document.getElementById('validatorOptionsContainer');
        const userSelection = document.getElementById('userSelectionContainer');

        if (roleName === 'validator' && validatorDokumenId) {
            validatorOptions.style.display = 'block';
            handleValidatorOptionChange();
        } else {
            validatorOptions.style.display = 'none';
            userSelection.style.display = 'flex';
        }
    }

    function handleValidatorOptionChange() {
        const useValidatorDokumen = document.getElementById('useValidatorDokumen');
        const useExisting = useValidatorDokumen ? useValidatorDokumen.checked : false;
        const userSelection = document.getElementById('userSelectionContainer');
        const userIdSelect = document.getElementById('userId');

        if (useExisting && validatorDokumenId) {
            userSelection.style.display = 'none';
            userIdSelect.value = validatorDokumenId;
            userIdSelect.required = false;
        } else {
            userSelection.style.display = 'flex';
            userIdSelect.value = '';
            userIdSelect.required = true;
        }
    }

    function showValidatorInfo() {
        const modal = new bootstrap.Modal(document.getElementById('modalValidatorInfo'));
        modal.show();
    }

    function showUploadSuratTugasModal(jenisDokumen) {
        const form = document.getElementById('formUploadSuratTugas');
        form.action = `/de/penugasan-ak/${pengajuanId}/upload-surat-tugas/${jenisDokumen}`;

        document.getElementById('modalFileSuratTugas').value = '';
        document.getElementById('modalSuratTugasPreview').innerHTML = '';

        const modal = new bootstrap.Modal(document.getElementById('modalUploadSuratTugas'));
        modal.show();
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

    const useValidatorDokumen = document.getElementById('useValidatorDokumen')
    const useNewValidator = document.getElementById('useNewValidator')
    if (useValidatorDokumen) useValidatorDokumen.addEventListener('change', handleValidatorOptionChange);
    if (useNewValidator) useNewValidator.addEventListener('change', handleValidatorOptionChange);

</script>
@endpush
@endsection
