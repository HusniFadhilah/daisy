{{-- resources/views/de/validasi-pembayaran-banding/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pembayaran Banding — ' . $pembayaran->nomor_invoice)

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('de.validasi-pembayaran-banding.index') }}">Validasi Pembayaran Banding</a>
            </li>
            <li class="breadcrumb-item active">{{ $pembayaran->nomor_invoice }}</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-credit-card"></i> Detail Pembayaran Banding
            </h5>
            <small class="text-muted">{{ $pembayaran->nomor_invoice }}</small>
        </div>
        <a href="{{ route('de.validasi-pembayaran-banding.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">

        {{-- ═══════════════ KOLOM KIRI ═══════════════ --}}
        <div class="col-lg-8 mb-4">

            {{-- Alert status --}}
            @if($pembayaran->status_pembayaran === 'menunggu_pembayaran')
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu Pembayaran</strong><br>
                Invoice telah dikirim. Program studi belum melakukan pembayaran.
            </div>

            @elseif($pembayaran->status_pembayaran === 'menunggu_verifikasi')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Menunggu Validasi</strong><br>
                Formulir & bukti pembayaran telah diupload oleh PS pada
                <strong>
                    {{ $dokumenFormulir?->created_at->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}
                </strong>.
                Silakan lakukan validasi di bawah.
            </div>

            @elseif($pembayaran->status_pembayaran === 'upload_ulang')
            <div class="alert alert-secondary alert-permanent">
                <i class="bi bi-arrow-repeat"></i>
                <strong>Diminta Upload Ulang</strong><br>
                PS diminta mengupload ulang formulir & bukti pembayaran.
                @if($pembayaran->catatan_verifikasi)
                <hr class="my-2">
                <strong>Catatan:</strong> {{ $pembayaran->catatan_verifikasi }}
                @endif
            </div>

            @elseif($pembayaran->status_pembayaran === 'terverifikasi')
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle-fill"></i>
                <strong>Pembayaran Tervalidasi</strong><br>
                Divalidasi oleh <strong>{{ $pembayaran->verifier?->name ?? '-' }}</strong>
                pada {{ $pembayaran->tanggal_verifikasi?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}.
                Proses dapat dilanjutkan ke penugasan asesor banding.
            </div>

            @elseif($pembayaran->status_pembayaran === 'ditolak')
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-x-circle-fill"></i>
                <strong>Pembayaran Ditolak</strong><br>
                @if($pembayaran->catatan_verifikasi)
                <strong>Alasan:</strong> {{ $pembayaran->catatan_verifikasi }}
                @endif
            </div>
            @endif

            {{-- ── Formulir & Bukti Pembayaran dari UPPS ── --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        Formulir & Bukti Pembayaran dari PS
                    </h5>
                </div>
                <div class="card-body">
                    @if($dokumenFormulir)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-spreadsheet text-success me-3" style="font-size:42px;"></i>
                            <div>
                                <strong>{{ $dokumenFormulir->original_filename }}</strong><br>
                                <small class="text-muted">
                                    {{ number_format($dokumenFormulir->file_size / 1024, 2) }} KB &bull;
                                    Diupload: {{ $dokumenFormulir->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small><br>
                                <span class="badge bg-success">Versi Terbaru</span>
                            </div>
                        </div>
                        <a href="{{ route('de.validasi-pembayaran-banding.download-formulir', $pembayaran->id) }}" class="btn btn-success">
                            <i class="bi bi-eye"></i> Lihat
                        </a>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x text-muted" style="font-size:48px;"></i>
                        <p class="text-muted mt-2 mb-0">
                            PS belum mengupload formulir & bukti pembayaran.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Form Validasi (hanya muncul jika menunggu_verifikasi) ── --}}
            @if($pembayaran->status_pembayaran === 'menunggu_verifikasi')
            <div class="card mb-4 border-primary border-2">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check"></i> Validasi Pembayaran Banding
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('de.validasi-pembayaran-banding.validasi', $pembayaran->id) }}" id="formValidasi">
                        @csrf

                        {{-- Keputusan --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Keputusan Validasi <span class="text-danger">*</span>
                            </label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="action" id="actionApprove" value="approve" checked onchange="updateCatatan(this.value)">
                                <label class="form-check-label" for="actionApprove">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <strong>Setujui</strong> — Pembayaran banding valid & lunas
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="action" id="actionUploadUlang" value="upload_ulang" onchange="updateCatatan(this.value)">
                                <label class="form-check-label" for="actionUploadUlang">
                                    <i class="bi bi-arrow-repeat text-warning"></i>
                                    <strong>Upload Ulang</strong> — Minta PS kirim ulang bukti pembayaran
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="actionDitolak" value="ditolak" onchange="updateCatatan(this.value)">
                                <label class="form-check-label" for="actionDitolak">
                                    <i class="bi bi-x-circle text-danger"></i>
                                    <strong>Tolak</strong> — Pembayaran tidak valid
                                </label>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Catatan untuk PS <span class="text-danger">*</span>
                            </label>
                            <textarea id="catatanVerifikasi" name="catatan_verifikasi" class="form-control @error('catatan_verifikasi') is-invalid @enderror" rows="4" required>{{ old('catatan_verifikasi') }}</textarea>
                            @error('catatan_verifikasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="btnSimpanValidasi">
                                <i class="bi bi-send-check"></i> Simpan Keputusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- ── Informasi Invoice ── --}}
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt"></i> Informasi Invoice Banding
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="35%">Nomor Invoice</th>
                            <td>: <strong>{{ $pembayaran->nomor_invoice }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td>: <span class="badge bg-info">Pembayaran Banding</span></td>
                        </tr>
                        <tr>
                            <th>Nominal</th>
                            <td>
                                : <strong class="text-success fs-5">
                                    Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Invoice</th>
                            <td>: {{ $pembayaran->created_at->locale('id')->translatedFormat('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                : {{ $pembayaran->tanggal_jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                                @if($pembayaran->tanggal_jatuh_tempo && $pembayaran->tanggal_jatuh_tempo < now() && $pembayaran->status_pembayaran !== 'terverifikasi')
                                    <span class="badge bg-danger ms-1">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>
                                : {{ $pembayaran->tanggal_pembayaran
                                    ? $pembayaran->tanggal_pembayaran->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                @php
                                $cfg = [
                                'menunggu_pembayaran' => ['warning', 'hourglass-split', 'Menunggu Pembayaran'],
                                'menunggu_verifikasi' => ['info', 'clock-history', 'Menunggu Validasi'],
                                'upload_ulang' => ['secondary', 'arrow-repeat', 'Upload Ulang'],
                                'terverifikasi' => ['success', 'check-circle', 'Tervalidasi'],
                                'ditolak' => ['danger', 'x-circle', 'Ditolak'],
                                ][$pembayaran->status_pembayaran] ?? ['secondary','question-circle','—'];
                                @endphp
                                <span class="badge bg-{{ $cfg[0] }}">
                                    <i class="bi bi-{{ $cfg[1] }}"></i> {{ $cfg[2] }}
                                </span>
                            </td>
                        </tr>
                        @if($pembayaran->keterangan)
                        <tr>
                            <th>Keterangan Invoice</th>
                            <td>: {{ $pembayaran->keterangan }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($pembayaran->catatan_verifikasi)
                    <hr>
                    <h6 class="fw-bold">Catatan Validasi:</h6>
                    <p class="text-muted mb-0">{{ $pembayaran->catatan_verifikasi }}</p>
                    @endif

                    @if($pembayaran->verifier)
                    <hr>
                    <small class="text-muted">
                        Divalidasi oleh: <strong>{{ $pembayaran->verifier->name }}</strong>
                    </small>
                    @endif
                </div>
            </div>

        </div>

        {{-- ═══════════════ SIDEBAR ═══════════════ --}}
        <div class="col-lg-4">

            {{-- Riwayat Status --}}
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Status</h5>
                </div>
                <div class="card-body" style="max-height:400px;overflow-y:auto;">
                    @php
                    $logs = $pembayaran->pengajuan->statusLog
                    ->whereIn('status_to', [
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                    ])
                    ->sortBy('changed_at')
                    ->unique('status_to');
                    @endphp

                    @forelse($logs as $log)
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 pt-1">
                            <i class="bi bi-circle-fill text-secondary" style="font-size:8px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label']
                                    ?? $log->status_to }}
                            </strong><br>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($log->changed_at ?? $log->created_at)
                                    ->locale('id')->translatedFormat('d M Y H:i') }}
                            </small>
                            {{-- @if($log->keterangan)
                            <br>
                            <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                            @endif --}}
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center small mb-0">Belum ada riwayat</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
    const catatanDefaults = {
        approve: 'Pembayaran banding telah kami verifikasi dan dinyatakan valid. Proses banding akan segera dilanjutkan.'
        , upload_ulang: 'Mohon upload ulang formulir & bukti pembayaran banding yang lebih jelas dan sesuai.'
        , ditolak: 'Pembayaran banding tidak dapat divalidasi. Silakan hubungi LAMDEPILAR untuk informasi lebih lanjut.'
    , };

    function updateCatatan(action) {
        const el = document.getElementById('catatanVerifikasi');
        const oldVal = @json(old('catatan_verifikasi'));

        // Jika ada nilai lama dari validasi error, tidak perlu override
        if (oldVal) return;

        el.value = catatanDefaults[action] || '';
    }

    // Set default catatan saat halaman load
    document.addEventListener('DOMContentLoaded', function() {
        const checked = document.querySelector('input[name="action"]:checked');
        if (checked) updateCatatan(checked.value);

        const formValidasi = document.getElementById('formValidasi')
        if (formValidasi) formValidasi.addEventListener('submit', function() {
            const btn = document.getElementById('btnSimpanValidasi');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        });
    });

</script>
@endpush
@endsection
