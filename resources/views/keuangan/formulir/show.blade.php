{{-- resources/views/keuangan/formulir/show.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Detail Formulir Pembayaran — ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('keuangan.formulir.index') }}">Formulir Pembayaran</a></li>
            <li class="breadcrumb-item active">{{ $pengajuan->nomor_pengajuan }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark-text"></i> Detail Formulir Pembayaran</h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <a href="{{ route('keuangan.formulir.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @php
    $pmbAkreditasi = $pengajuan->pembayaran;
    $pmbBanding = $pengajuan->pembayaranBanding;
    @endphp

    <div class="row">

        {{-- ═══════════════ KOLOM KIRI ═══════════════ --}}
        <div class="col-lg-7">

            {{-- ── SEKSI AKREDITASI ── --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Formulir Pembayaran Akreditasi
                    </h5>
                    <span class="badge bg-light text-primary">Akreditasi</span>
                </div>
                <div class="card-body">

                    @if($formulirAkreditasi)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-spreadsheet text-primary me-3" style="font-size:40px;"></i>
                            <div>
                                <strong class="d-block">{{ $formulirAkreditasi->original_filename }}</strong>
                                <small class="text-muted">
                                    Diupload: {{ $formulirAkreditasi->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small><br>
                                <span class="badge bg-success">Versi Terbaru</span>
                            </div>
                        </div>
                        <a href="{{ route('keuangan.formulir.download', [$pengajuan->id, $formulirAkreditasi->id]) }}" class="btn btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-file-earmark-x text-muted" style="font-size:40px;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada formulir pembayaran akreditasi diupload.</p>
                    </div>
                    @endif

                    @if($pmbAkreditasi)
                    <hr>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th width="40%">Nomor Invoice</th>
                            <td><strong>{{ $pmbAkreditasi->nomor_invoice }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jumlah</th>
                            <td><strong class="text-success">Rp {{ number_format($pmbAkreditasi->jumlah_pembayaran, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                {{ $pmbAkreditasi->tanggal_jatuh_tempo ? \App\Libraries\Date::tglIndo($pmbAkreditasi->tanggal_jatuh_tempo) : '-' }}
                                @if($pmbAkreditasi->tanggal_jatuh_tempo && $pmbAkreditasi->tanggal_jatuh_tempo < now() && $pmbAkreditasi->status_pembayaran !== 'terverifikasi')
                                    <span class="badge bg-danger ms-1">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Bayar</th>
                            <td>{{ $pmbAkreditasi->tanggal_pembayaran ? \App\Libraries\Date::tglWaktu($pmbAkreditasi->tanggal_pembayaran) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $pmbAkreditasi->status_pembayaran_badge ?? 'secondary' }}">
                                    {{ $pmbAkreditasi->status_pembayaran_label ?? $pmbAkreditasi->status_pembayaran }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($pmbAkreditasi->status_pembayaran === 'menunggu_verifikasi')
                    <div class="mt-3">
                        <a href="{{ route('keuangan.pembayaran.show', $pengajuan->id) }}" class="btn btn-primary w-100">
                            <i class="bi bi-shield-check"></i> Lakukan Validasi Akreditasi →
                        </a>
                    </div>
                    @endif

                    @if($pmbAkreditasi->catatan_verifikasi)
                    <div class="alert alert-secondary mt-3 mb-0">
                        <strong>Catatan:</strong> {{ $pmbAkreditasi->catatan_verifikasi }}
                    </div>
                    @endif
                    @else
                    <div class="alert alert-light mt-2 mb-0">Belum ada invoice akreditasi.</div>
                    @endif
                </div>
            </div>

            {{-- ── SEKSI BANDING ── --}}
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-arrow-up"></i> Formulir Pembayaran Banding
                    </h5>
                    <span class="badge bg-dark text-warning">Banding</span>
                </div>
                <div class="card-body">

                    @if($formulirBanding)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-spreadsheet text-warning me-3" style="font-size:40px;"></i>
                            <div>
                                <strong class="d-block">{{ $formulirBanding->original_filename }}</strong>
                                <small class="text-muted">
                                    Diupload: {{ $formulirBanding->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small><br>
                                <span class="badge bg-success">Versi Terbaru</span>
                            </div>
                        </div>
                        <a href="{{ route('keuangan.formulir.download', [$pengajuan->id, $formulirBanding->id]) }}" class="btn btn-warning">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-file-earmark-x text-muted" style="font-size:40px;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada formulir pembayaran banding diupload.</p>
                    </div>
                    @endif

                    @if($pmbBanding)
                    <hr>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th width="40%">Nomor Invoice</th>
                            <td><strong>{{ $pmbBanding->nomor_invoice }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jumlah</th>
                            <td><strong class="text-warning">Rp {{ number_format($pmbBanding->jumlah_pembayaran, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                {{ $pmbBanding->tanggal_jatuh_tempo ? \App\Libraries\Date::tglIndo($pmbBanding->tanggal_jatuh_tempo) : '-' }}
                                @if($pmbBanding->tanggal_jatuh_tempo && $pmbBanding->tanggal_jatuh_tempo < now() && $pmbBanding->status_pembayaran !== 'terverifikasi')
                                    <span class="badge bg-danger ms-1">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Bayar</th>
                            <td>{{ $pmbBanding->tanggal_pembayaran ? \App\Libraries\Date::tglWaktu($pmbBanding->tanggal_pembayaran) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $pmbBanding->status_pembayaran_badge ?? 'secondary' }}">
                                    {{ $pmbBanding->status_pembayaran_label ?? $pmbBanding->status_pembayaran }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($pmbBanding->status_pembayaran === 'menunggu_verifikasi')
                    <div class="mt-3">
                        <a href="{{ route('de.validasi-pembayaran-banding.show', $pmbBanding->id) }}" class="btn btn-warning w-100">
                            <i class="bi bi-shield-check"></i> Lakukan Validasi Banding →
                        </a>
                    </div>
                    @endif

                    @if($pmbBanding->catatan_verifikasi)
                    <div class="alert alert-secondary mt-3 mb-0">
                        <strong>Catatan:</strong> {{ $pmbBanding->catatan_verifikasi }}
                    </div>
                    @endif
                    @else
                    <div class="alert alert-light mt-2 mb-0">Belum ada invoice banding.</div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ═══════════════ SIDEBAR ═══════════════ --}}
        <div class="col-lg-5">

            {{-- Ringkasan Status --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Ringkasan Status</h5>
                </div>
                <div class="card-body">
                    @php
                    $statusCfg = [
                    'menunggu_pembayaran' => ['warning', 'Menunggu Pembayaran'],
                    'menunggu_verifikasi' => ['info', 'Menunggu Validasi'],
                    'upload_ulang' => ['secondary', 'Upload Ulang'],
                    'terverifikasi' => ['success', 'Tervalidasi'],
                    'ditolak' => ['danger', 'Ditolak'],
                    ];
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">Akreditasi</span>
                        @if($pmbAkreditasi)
                        @php $cfg = $statusCfg[$pmbAkreditasi->status_pembayaran] ?? ['secondary','—']; @endphp
                        <span class="badge bg-{{ $cfg[0] }}">{{ $cfg[1] }}</span>
                        @else
                        <span class="badge bg-light text-muted border">Belum Ada Invoice</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Banding</span>
                        @if($pmbBanding)
                        @php $cfg = $statusCfg[$pmbBanding->status_pembayaran] ?? ['secondary','—']; @endphp
                        <span class="badge bg-{{ $cfg[0] }}">{{ $cfg[1] }}</span>
                        @else
                        <span class="badge bg-light text-muted border">Belum Ada Invoice</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info Prodi --}}
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
                    <div class="mb-2">
                        <label class="text-muted small">Jenjang</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-muted small">Nomor Permohonan</label>
                        <p class="fw-bold mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
                    </div>
                </div>
            </div>

            {{-- Riwayat Upload --}}
            @if($riwayatDokumen->count() > 0)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Upload</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($riwayatDokumen as $dok)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <small class="d-block text-muted">
                                    {{ $dok->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                <small>{{ $dok->original_filename }}</small>
                                <div class="mt-1">
                                    @if($dok->jenis_dokumen === 'formulir_pembayaran')
                                    <span class="badge bg-primary" style="font-size:10px;">Akreditasi</span>
                                    @else
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">Banding</span>
                                    @endif
                                    @if($dok->is_latest)
                                    <span class="badge bg-success ms-1" style="font-size:10px;">Terbaru</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('keuangan.formulir.download', [$pengajuan->id, $dok->id]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
