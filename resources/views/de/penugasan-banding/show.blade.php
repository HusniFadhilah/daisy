{{-- resources/views/de/penugasan-banding/show.blade.php --}}
@extends('layouts.template.app')
@section('title', 'Detail Penugasan Banding')
@section('content')
<div class="container-fluid py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penugasan-banding') }}">Penugasan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1"><i class="bi bi-person-check"></i> Detail Penugasan Banding</h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <div class="d-flex gap-2">
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA
            && !($pengajuan->asesmen && $pengajuan->asesmen->asesmenBanding))
            <button class="btn btn-success" onclick="showMarkReadyModal()">
                <i class="bi bi-check-circle"></i> Tetapkan Siap Banding
            </button>
            @endif
            <a href="{{ route('de.penugasan-banding') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">

            @if($pengajuan->asesmen && $pengajuan->asesmen->asesmenBanding)

            @php
            $lastLog = $pengajuan->statusLog
            ->whereIn('status_to', [
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ])
            ->sortByDesc('changed_at')->first();
            @endphp

            @if($lastLog)
            <div class="alert alert-success alert-permanent mb-3">
                <i class="bi bi-info-circle"></i>
                <strong>Status Penugasan Banding:</strong>
                @if($lastLog->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED)
                Asesor Banding telah ditugaskan. Pastikan semua telah menyetujui penawaran.
                @elseif($lastLog->status_to === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                Banding sedang dilaksanakan.
                @endif
            </div>
            @endif

            {{-- Form Penugasan --}}
            {{-- @if(!$requirementsStatus['met']) --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Tugaskan Asesor Banding</h5>
                </div>
                <div class="card-body">
                    <form id="assignForm" onsubmit="assignUser(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Asesor Banding <span class="text-danger">*</span></label>
                                <select id="userId" class="form-select" required>
                                    <option value="">-- Pilih User --</option>
                                    @foreach($availableUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Surat Tugas</label>
                                <input type="file" id="fileSuratTugas" class="form-control" accept=".pdf">
                                <small class="text-muted">
                                    Satu surat tugas bersama untuk semua asesor. PDF max 5MB.
                                    @if($pengajuan->dokumen->where('jenis_dokumen','surat_tugas_asesor_banding')->where('is_latest',true)->first())
                                    <span class="text-success"><i class="bi bi-check-circle"></i> Sudah ada</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        @if($roles->first())
                        <input type="hidden" id="roleId" value="{{ $roles->first()->id }}">
                        @endif
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Tugaskan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- @endif --}}

            {{-- Daftar Penugasan --}}
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Asesor Banding</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%">#</th>
                                    <th width="30%">Nama</th>
                                    <th width="20%">Status Penawaran</th>
                                    <th width="32%">Surat Tugas</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $suratTugas = $pengajuan->dokumen
                                ->where('jenis_dokumen', 'surat_tugas_asesor_banding')
                                ->where('is_latest', true)
                                ->first();
                                @endphp

                                @forelse($pengajuan->asesmen->asesmenUserRoles->where('jenis_asesmen','banding') as $assignment)
                                <tr>
                                    <td><span class="badge bg-secondary">#{{ $assignment->urutan_asesor }}</span></td>
                                    <td>
                                        <strong>{{ $assignment->user->name }}</strong><br>
                                        <small class="text-muted text-wrap">{{ $assignment->user->email }}</small>
                                    </td>
                                    <td>
                                        @php
                                        $spClass = match($assignment->status_penawaran) {
                                        'accepted' => 'success',
                                        'pending' => 'warning',
                                        default => 'danger',
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $spClass }}">{{ ucfirst($assignment->status_penawaran) }}</span>
                                    </td>
                                    <td style="ma">
                                        @if($assignment->urutan_asesor === 1)
                                        @if($suratTugas && $suratTugas->path_file)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                            <div class="flex-grow-1" style="min-width:0;">
                                                <small class="d-block text-truncate">{{ $suratTugas->original_filename }}</small>
                                                <small class="text-muted">Versi {{ $suratTugas->versi }}</small>
                                            </div>
                                            <a href="{{ route('de.penugasan-banding.download-surat-tugas', [$pengajuan->id, 'surat_tugas_asesor_banding']) }}" class="btn btn-sm btn-success" target="_blank" title="Lihat"><i class="bi bi-eye"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="showUploadModal('surat_tugas_asesor_banding')" title="Ganti"><i class="bi bi-upload"></i></button>
                                        </div>
                                        @else
                                        <button type="button" class="btn btn-sm btn-warning" onclick="showUploadModal('surat_tugas_asesor_banding')">
                                            <i class="bi bi-upload"></i> Upload Surat Tugas
                                        </button>
                                        @endif
                                        @else
                                        <small class="text-muted"><i class="bi bi-files"></i> Surat tugas bersama (lihat asesor #1)</small>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="removeUser({{ $pengajuan->id }}, {{ $assignment->id_user }}, '{{ addslashes($assignment->user->name) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox"></i> Belum ada asesor yang ditugaskan
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Info Penugasan --}}
            <div class="card my-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Penugasan Banding</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="35%">Tanggal Penugasan</th>
                            <td>: {{ $pengajuan->tanggal_penugasan_banding
                                    ? $pengajuan->tanggal_penugasan_banding->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Periode Banding</th>
                            <td>:
                                {{ ($pengajuan->asesmen && $pengajuan->asesmen->asesmenBanding && $pengajuan->asesmen->asesmenBanding->tanggal_mulai)
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenBanding->tanggal_mulai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                                @if($pengajuan->asesmen && $pengajuan->asesmen->asesmenBanding && $pengajuan->asesmen->asesmenBanding->tanggal_selesai)
                                s/d {{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenBanding->tanggal_selesai)->locale('id')->translatedFormat('d M Y') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penugasan Banding</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penugasan_banding', 'de', 'label_long_for', 'text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @else
            <div class="alert alert-warning alert-permanent">
                <h5><i class="bi bi-exclamation-triangle"></i> Belum Siap untuk Banding</h5>
                <p class="mb-0">Klik "Tetapkan Siap Banding" di kanan atas untuk memulai.</p>
            </div>
            @endif
        </div>

        {{-- SIDEBAR --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-{{ $requirementsStatus['met'] ? 'success' : 'warning' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $requirementsStatus['met'] ? 'check-circle' : 'exclamation-triangle' }}"></i>
                        Status Persyaratan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-3">
                        <tr>
                            <th><i class="bi bi-person"></i> Asesor Banding</th>
                            <td>: <strong>{{ $requirementsStatus['asesor_count'] }}</strong> / min 1</td>
                        </tr>
                    </table>
                    @if(!$requirementsStatus['met'])
                    <div class="alert alert-warning alert-permanent mb-0">
                        <small><i class="bi bi-exclamation-triangle"></i> Perlu: {{ implode(', ', $requirementsStatus['missing']) }}</small>
                    </div>
                    @else
                    <div class="alert alert-success alert-permanent mb-0">
                        <small><i class="bi bi-check-circle"></i> Persyaratan terpenuhi</small>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Status</h5>
                </div>
                <div class="card-body" style="max-height:350px; overflow-y:auto;">
                    @php
                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', [
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    ])
                    ->sortBy('changed_at')->unique('status_to')->values();
                    @endphp
                    @forelse($logs as $log)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 mt-1">
                            <i class="bi bi-circle-fill text-success" style="font-size:8px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                            </strong>
                            <small class="text-muted d-block">
                                {{ $log->changed_at->locale('id')->translatedFormat('d M Y H:i') }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">Belum ada riwayat</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Mark Ready --}}
<div class="modal fade" id="modalMarkReady" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Tetapkan Siap Banding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitMarkReady(event)">
                <div class="modal-body">
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i> Tentukan periode pelaksanaan banding.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggalMulai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estimasi Selesai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggalSelesai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan <span class="text-muted">(Opsional)</span></label>
                        <textarea class="form-control" id="catatanModal" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Tetapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Upload Surat Tugas --}}
<div class="modal fade" id="modalUpload" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Upload Surat Tugas Banding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUpload" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Surat Tugas (PDF) <span class="text-danger">*</span></label>
                        <input type="file" name="file_surat_tugas" class="form-control" accept=".pdf" required>
                        <small class="text-muted">Format PDF · Maks 5MB · Berlaku untuk semua asesor banding</small>
                    </div>
                    <div class="alert alert-info alert-permanent mb-0">
                        <i class="bi bi-info-circle"></i> File yang diupload menggantikan versi sebelumnya.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    const pengajuanId = '{{ $pengajuan->id }}';

    function showMarkReadyModal() {
        const today = new Date();
        const nextMonth = new Date(today);
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        document.getElementById('tanggalMulai').value = today.toISOString().split('T')[0];
        document.getElementById('tanggalSelesai').value = nextMonth.toISOString().split('T')[0];
        document.getElementById('catatanModal').value = '';
        new bootstrap.Modal(document.getElementById('modalMarkReady')).show();
    }

    function showUploadModal(jenisDokumen) {
        const form = document.getElementById('formUpload');
        form.action = `/de/penugasan-banding/${pengajuanId}/upload-surat-tugas/${jenisDokumen}`;
        new bootstrap.Modal(document.getElementById('modalUpload')).show();
    }

    async function submitMarkReady(event) {
        event.preventDefault();
        const tanggalMulai = document.getElementById('tanggalMulai').value;
        const tanggalSelesai = document.getElementById('tanggalSelesai').value;
        const catatan = document.getElementById('catatanModal').value;

        if (new Date(tanggalSelesai) < new Date(tanggalMulai)) {
            return Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai!'
            });
        }

        bootstrap.Modal.getInstance(document.getElementById('modalMarkReady')).hide();
        Swal.fire({
            title: 'Memproses...'
            , allowOutsideClick: false
            , didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(`/de/penugasan-banding/${pengajuanId}/mark-ready`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify({
                    tanggal_mulai: tanggalMulai
                    , tanggal_selesai: tanggalSelesai
                    , catatan
                })
            , });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                });
                location.reload();
            } else throw new Error(data.message);
        } catch (e) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: e.message
            });
        }
    }

    async function assignUser(event) {
        event.preventDefault();

        const userId = document.getElementById('userId').value;
        const roleId = document.getElementById('roleId').value;
        const fileSuratTugas = document.getElementById('fileSuratTugas').files[0];

        if (!userId || !roleId) {
            return Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Pilih asesor terlebih dahulu!'
            });
        }

        const formData = new FormData();
        formData.append('id_user', userId);
        formData.append('id_role', roleId);
        if (fileSuratTugas) formData.append('file_surat_tugas', fileSuratTugas);

        try {
            const res = await fetch(`/de/penugasan-banding/${pengajuanId}/assign-user`, {
                method: 'POST'
                , headers: {
                    'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
                , body: formData
            , });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                });
                location.reload();
            } else throw new Error(data.message);
        } catch (e) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: e.message
            });
        }
    }

    async function removeUser(pengajuanId, userId, userName) {
        const res = await Swal.fire({
            title: 'Konfirmasi Hapus'
            , html: `Hapus <strong>${userName}</strong> dari penugasan banding?`
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Hapus'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#dc3545'
        , });
        if (!res.isConfirmed) return;

        try {
            const r = await fetch(`/de/penugasan-banding/${pengajuanId}/remove-user/${userId}`, {
                method: 'DELETE'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
            , });
            const data = await r.json();
            if (data.success) {
                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: data.message
                    , timer: 1500
                });
                location.reload();
            } else throw new Error(data.message);
        } catch (e) {
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: e.message
            });
        }
    }

</script>
@endpush
@endsection
