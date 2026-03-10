{{-- resources/views/de/pelaksanaan-banding/show.blade.php --}}
@extends('layouts.template.app')
@section('title', 'Detail Pelaksanaan Banding')
@section('content')
<div class="container-fluid py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaksanaan-banding') }}">Pelaksanaan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1"><i class="bi bi-clipboard-check"></i> Detail Pelaksanaan Banding</h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <div class="d-flex gap-2">
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED)
            <button class="btn btn-primary" onclick="showStartModal()">
                <i class="bi bi-play-circle"></i> Mulai Pelaksanaan
            </button>
            @endif
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN && $laporanBanding)
            <button class="btn btn-success" onclick="showSubmitLaporanModal()">
                <i class="bi bi-send-check"></i> Submit Laporan
            </button>
            @endif
            <a href="{{ route('de.pelaksanaan-banding') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">

        {{-- ════════════ KOLOM KIRI ════════════ --}}
        <div class="col-lg-8 mb-4">

            {{-- Alert status --}}
            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED)
            <div class="alert alert-warning alert-permanent mb-3">
                <i class="bi bi-clock"></i>
                <strong>Menunggu Dimulai</strong> — Semua asesor belum tentu menyetujui penawaran. Klik "Mulai Pelaksanaan" jika sudah siap.
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
            <div class="alert alert-info alert-permanent mb-3">
                <i class="bi bi-hourglass-split"></i>
                <strong>Banding Sedang Dilaksanakan</strong> — Upload berita acara dan laporan banding, lalu submit laporan.
            </div>
            @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
            <div class="alert alert-success alert-permanent mb-3">
                <i class="bi bi-check-circle"></i>
                <strong>Banding Selesai Dilaporkan</strong> — Proses banding telah selesai.
            </div>
            @endif

            {{-- Daftar Asesor --}}
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Asesor Banding</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">#</th>
                                <th width="35%">Nama</th>
                                <th width="20%">Status Penawaran</th>
                                <th width="37%">Surat Tugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asesors as $assignment)
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
                                <td>
                                    @if($assignment->urutan_asesor === 1 && $suratTugasBanding)
                                    <a href="{{ route('de.pelaksanaan-banding.download', [$pengajuan->id, 'surat_tugas_asesor_banding']) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    @elseif($assignment->urutan_asesor === 1)
                                    <span class="text-muted small">Belum diupload</span>
                                    @else
                                    <small class="text-muted">Surat tugas bersama (lihat asesor #1)</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">Tidak ada asesor</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Dokumen Pelaksanaan --}}
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-folder2-open"></i> Dokumen Pelaksanaan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Berita Acara --}}
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-file-earmark-text fs-4 text-primary me-2"></i>
                                    <div>
                                        <strong>Berita Acara Banding</strong>
                                        @if($beritaAcaraBanding)
                                        <span class="badge bg-success ms-1">v{{ $beritaAcaraBanding->versi }}</span>
                                        @else
                                        <span class="badge bg-secondary ms-1">Belum ada</span>
                                        @endif
                                    </div>
                                </div>
                                @if($beritaAcaraBanding)
                                <small class="text-muted d-block mb-2">{{ Str::limit($beritaAcaraBanding->original_filename, 30) }}</small>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('de.pelaksanaan-banding.download', [$pengajuan->id, 'berita_acara_banding']) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                                    <button class="btn btn-sm btn-outline-warning" onclick="showUploadModal('berita_acara_banding', 'Berita Acara Banding')">
                                        <i class="bi bi-arrow-repeat"></i> Ganti
                                    </button>
                                    @endif
                                </div>
                                @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                                <button class="btn btn-sm btn-primary" onclick="showUploadModal('berita_acara_banding', 'Berita Acara Banding')">
                                    <i class="bi bi-upload"></i> Upload
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Laporan Banding --}}
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 {{ !$laporanBanding ? 'border-warning' : '' }}">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-file-earmark-bar-graph fs-4 text-success me-2"></i>
                                    <div>
                                        <strong>Laporan Banding</strong>
                                        @if($laporanBanding)
                                        <span class="badge bg-success ms-1">v{{ $laporanBanding->versi }}</span>
                                        @else
                                        <span class="badge bg-warning ms-1">Diperlukan</span>
                                        @endif
                                    </div>
                                </div>
                                @if($laporanBanding)
                                <small class="text-muted d-block mb-2">{{ Str::limit($laporanBanding->original_filename, 30) }}</small>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('de.pelaksanaan-banding.download', [$pengajuan->id, 'laporan_banding']) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                                    <button class="btn btn-sm btn-outline-warning" onclick="showUploadModal('laporan_banding', 'Laporan Banding')">
                                        <i class="bi bi-arrow-repeat"></i> Ganti
                                    </button>
                                    @endif
                                </div>
                                @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                                <div class="alert alert-warning alert-permanent mb-2 py-1 px-2">
                                    <small><i class="bi bi-exclamation-triangle"></i> Wajib sebelum submit laporan</small>
                                </div>
                                <button class="btn btn-sm btn-warning" onclick="showUploadModal('laporan_banding', 'Laporan Banding')">
                                    <i class="bi bi-upload"></i> Upload
                                </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Info Penugasan --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Penugasan & Periode</h5>
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
                                @if($pengajuan->asesmen && $pengajuan->asesmen->asesmenBanding)
                                {{ $pengajuan->asesmen->asesmenBanding->tanggal_mulai
                                        ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenBanding->tanggal_mulai)->locale('id')->translatedFormat('d M Y')
                                        : '-' }}
                                @if($pengajuan->asesmen->asesmenBanding->tanggal_selesai)
                                s/d {{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenBanding->tanggal_selesai)->locale('id')->translatedFormat('d M Y') }}
                                @endif
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status Saat Ini</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penugasan_banding', 'de', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>

        {{-- ════════════ SIDEBAR ════════════ --}}
        <div class="col-lg-4">

            {{-- Info Program Studi --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Info Program Studi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted small">Universitas</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->university->name ?? '-' }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Program Studi</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-muted small">Nomor Permohonan</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
                    </div>
                </div>
            </div>

            {{-- Checklist Dokumen --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Kelengkapan Dokumen</h5>
                </div>
                <div class="card-body">
                    @php
                    $checks = [
                    ['label' => 'Surat Tugas Banding', 'ada' => (bool) $suratTugasBanding],
                    ['label' => 'Berita Acara Banding', 'ada' => (bool) $beritaAcaraBanding],
                    ['label' => 'Laporan Banding', 'ada' => (bool) $laporanBanding],
                    ];
                    @endphp
                    @foreach($checks as $check)
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-{{ $check['ada'] ? 'check-circle-fill text-success' : 'circle text-muted' }} me-2"></i>
                        <span class="{{ $check['ada'] ? '' : 'text-muted' }}">{{ $check['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Riwayat Status --}}
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Status</h5>
                </div>
                <div class="card-body" style="max-height:350px; overflow-y:auto;">
                    @php
                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                    ])
                    ->sortBy('changed_at')->values();
                    @endphp
                    @forelse($logs as $log)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 mt-1">
                            <i class="bi bi-circle-fill text-success" style="font-size:8px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong class="small">
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                            </strong>
                            <small class="text-muted d-block">
                                {{ $log->changed_at->locale('id')->translatedFormat('d M Y H:i') }}
                            </small>
                            @if($log->keterangan)
                            <small class="text-muted fst-italic">{{ Str::limit($log->keterangan, 80) }}</small>
                            @endif
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

{{-- Modal Mulai Pelaksanaan --}}
<div class="modal fade" id="modalStart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-play-circle"></i> Mulai Pelaksanaan Banding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitStart(event)">
                <div class="modal-body">
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i>
                        Status akan berubah dari <strong>Ditugaskan</strong> ke <strong>Dilaksanakan</strong>.
                        Pastikan minimal 1 asesor sudah menerima penawaran.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan <span class="text-muted">(Opsional)</span></label>
                        <textarea class="form-control" id="catatanStart" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-play-circle"></i> Mulai</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Upload Dokumen --}}
<div class="modal fade" id="modalUpload" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalUploadTitle"><i class="bi bi-upload"></i> Upload Dokumen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">File (PDF) <span class="text-danger">*</span></label>
                    <input type="file" id="uploadFile" class="form-control" accept=".pdf" required>
                    <small class="text-muted">Format PDF · Maks 10MB</small>
                </div>
                <div class="alert alert-info alert-permanent mb-0">
                    <i class="bi bi-info-circle"></i> File menggantikan versi sebelumnya jika sudah ada.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitUpload()">
                    <i class="bi bi-upload"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Submit Laporan --}}
<div class="modal fade" id="modalSubmitLaporan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-send-check"></i> Submit Laporan Banding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitLaporan(event)">
                <div class="modal-body">
                    <div class="alert alert-success alert-permanent mb-3">
                        <i class="bi bi-check-circle"></i>
                        Status akan berubah ke <strong>Dilaporkan</strong>. Pastikan laporan banding sudah final.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan <span class="text-muted">(Opsional)</span></label>
                        <textarea class="form-control" id="catatanSubmit" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-send-check"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    const pengajuanId = '{{ $pengajuan->id }}';
    let currentJenis = null;

    function showStartModal() {
        document.getElementById('catatanStart').value = '';
        new bootstrap.Modal(document.getElementById('modalStart')).show();
    }

    function showUploadModal(jenisDokumen, label) {
        currentJenis = jenisDokumen;
        document.getElementById('modalUploadTitle').innerHTML = `<i class="bi bi-upload"></i> Upload ${label}`;
        document.getElementById('uploadFile').value = '';
        new bootstrap.Modal(document.getElementById('modalUpload')).show();
    }

    function showSubmitLaporanModal() {
        document.getElementById('catatanSubmit').value = '';
        new bootstrap.Modal(document.getElementById('modalSubmitLaporan')).show();
    }

    async function submitStart(event) {
        event.preventDefault();
        const catatan = document.getElementById('catatanStart').value;

        bootstrap.Modal.getInstance(document.getElementById('modalStart')).hide();
        Swal.fire({
            title: 'Memproses...'
            , allowOutsideClick: false
            , didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(`/de/pelaksanaan-banding/${pengajuanId}/start`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify({
                    catatan
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

    async function submitUpload() {
        const fileEl = document.getElementById('uploadFile');
        if (!fileEl.files[0]) {
            return Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: 'Pilih file terlebih dahulu!'
            });
        }

        const formData = new FormData();
        formData.append('file', fileEl.files[0]);

        bootstrap.Modal.getInstance(document.getElementById('modalUpload')).hide();
        Swal.fire({
            title: 'Mengupload...'
            , allowOutsideClick: false
            , didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(`/de/pelaksanaan-banding/${pengajuanId}/upload/${currentJenis}`, {
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

    async function submitLaporan(event) {
        event.preventDefault();
        const catatan = document.getElementById('catatanSubmit').value;

        const confirm = await Swal.fire({
            title: 'Konfirmasi Submit Laporan'
            , text: 'Status akan berubah ke Dilaporkan dan tidak bisa dikembalikan. Lanjutkan?'
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Submit'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#198754'
        , });
        if (!confirm.isConfirmed) return;

        bootstrap.Modal.getInstance(document.getElementById('modalSubmitLaporan')).hide();
        Swal.fire({
            title: 'Memproses...'
            , allowOutsideClick: false
            , didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(`/de/pelaksanaan-banding/${pengajuanId}/submit-laporan`, {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': csrfToken
                    , 'Accept': 'application/json'
                }
                , body: JSON.stringify({
                    catatan
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

</script>
@endpush
@endsection
