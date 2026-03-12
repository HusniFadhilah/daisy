{{-- resources/views/upps/pelaksanaan-banding/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan Banding')

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-banding') }}">Pelaksanaan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1"><i class="bi bi-play-circle"></i> Detail Pelaksanaan Banding</h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaksanaan-banding') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">

        {{-- ═══════════════ KOLOM KIRI ═══════════════ --}}
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_BANDING_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
            ];
            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
            <div class="alert alert-light alert-permanent border border-dark mb-3">
                <i class="bi bi-info-circle"></i>
                Proses pelaksanaan banding sedang berlangsung. Hasil akan dilaporkan setelah selesai.
            </div>
            @endif
            {{-- ── BLOK 1: PEMBAYARAN BANDING (selalu tampil pertama) ── --}}
            <div class="card mb-4 {{ $pembayaranLunas ? 'border-secondary' : 'border-secondary border-2' }}">
                <div class="card-header {{ $pembayaranLunas ? 'bg-secondary' : 'bg-secondary' }} text-{{ $pembayaranLunas ? 'white' : 'white' }}">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card"></i>
                        Pembayaran Banding
                    </h5>
                </div>
                <div class="card-body">

                    @if(!$pembayaranBanding)
                    {{-- Invoice belum dibuat oleh DE --}}
                    <div class="text-center py-4">
                        <i class="bi bi-hourglass-split text-secondary" style="font-size: 48px;"></i>
                        <h5 class="mt-3 text-muted">Menunggu Invoice dari LAMDEPILAR</h5>
                        <p class="text-muted mb-0">
                            Invoice pembayaran banding akan dibuat oleh LAMDEPILAR setelah
                            permohonan banding diterima dan diverifikasi.
                        </p>
                    </div>

                    @else
                    {{-- Action berdasarkan status --}}
                    @if(in_array($pembayaranBanding->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang']))
                    <div class="d-grid">
                        <a href="{{ route('upps.pelaksanaan-banding.upload-pembayaran', $pengajuan->id) }}" class="btn btn-warning">
                            <i class="bi bi-upload"></i>
                            {{ $formulirBanding ? 'Upload Ulang Formulir & Bukti Pembayaran' : 'Upload Formulir & Bukti Pembayaran' }}
                        </a>
                    </div>
                    @elseif($pembayaranBanding->status_pembayaran === 'menunggu_verifikasi')
                    <div class="alert alert-info alert-permanent mb-0">
                        <i class="bi bi-clock-history"></i>
                        Formulir & bukti pembayaran sedang divalidasi oleh LAMDEPILAR.
                        Harap menunggu konfirmasi.
                    </div>
                    @elseif($pembayaranLunas)
                    <div class="alert alert-light border-2 border-secondary alert-permanent mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        Pembayaran banding telah tervalidasi. Proses pelaksanaan banding dapat dilanjutkan.
                    </div>
                    @endif

                    {{-- Invoice sudah ada --}}
                    <table class="table table-borderless mb-3 mt-2">
                        <tr>
                            <th style="width:40%">Nomor Invoice</th>
                            <td>: <strong>{{ $pembayaranBanding->nomor_invoice }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nominal</th>
                            <td>
                                : <strong class="text-dark fs-5">
                                    Rp {{ number_format($pembayaranBanding->jumlah_pembayaran, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                :
                                {{ $pembayaranBanding->tanggal_jatuh_tempo?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                                @if($pembayaranBanding->tanggal_jatuh_tempo
                                && $pembayaranBanding->tanggal_jatuh_tempo < now() && !$pembayaranLunas) <span class="badge bg-danger ms-1">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>: {{ $pembayaranBanding->tanggal_pembayaran
                                ? $pembayaranBanding->tanggal_pembayaran->locale('id')->translatedFormat('d M Y H:i')
                                : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pembayaran</th>
                            <td>:
                                @php
                                $cfg = [
                                'menunggu_pembayaran' => ['light', 'hourglass-split', 'Perlu Melakukan Pembayaran'],
                                'menunggu_verifikasi' => ['light', 'clock-history', 'Menunggu Validasi'],
                                'upload_ulang' => ['light', 'arrow-repeat', 'Diminta Upload Ulang'],
                                'terverifikasi' => ['light', 'check-circle', 'Tervalidasi'],
                                'ditolak' => ['danger', 'x-circle', 'Ditolak'],
                                ][$pembayaranBanding->status_pembayaran]
                                ?? ['light', 'question-circle', $pembayaranBanding->status_pembayaran];
                                @endphp
                                <span class="badge bg-{{ $cfg[0] }} text-dark">
                                    <i class="bi bi-{{ $cfg[1] }}"></i> {{ $cfg[2] }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    {{-- Catatan dari keuangan --}}
                    @if($pembayaranBanding->catatan_verifikasi)
                    <div class="alert alert-light alert-permanent border-start border-dark border-2 mt-2 mb-3">
                        <small class="text-muted d-block fw-bold">
                            <i class="bi bi-chat-left-quote"></i> Catatan dari LAMDEPILAR:
                        </small>
                        {{ $pembayaranBanding->catatan_verifikasi }}
                    </div>
                    @endif

                    {{-- Formulir yang sudah diupload --}}
                    @if($formulirBanding)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-spreadsheet text-success me-3" style="font-size: 32px;"></i>
                            <div>
                                <strong>{{ $formulirBanding->original_filename }}</strong><br>
                                <small class="text-muted">
                                    {{ number_format($formulirBanding->file_size / 1024, 2) }} KB &bull;
                                    Diupload: {{ $formulirBanding->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <a href="{{ route('upps.pelaksanaan-banding.download-formulir', $pengajuan->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-eye"></i> Lihat
                        </a>
                    </div>
                    @endif

                    @endif {{-- end if $pembayaranBanding --}}
                </div>
            </div>

            {{-- ═══ PAYMENT GATE — Konten di bawah hanya tampil jika lunas ═══ --}}
            @if(!$pembayaranBanding || !$pembayaranLunas)

            {{-- Blok terkunci --}}
            <div class="card border-secondary opacity-75">
                <div class="card-body text-center py-5">
                    <i class="bi bi-lock-fill text-secondary" style="font-size: 56px;"></i>
                    <h5 class="mt-3 text-muted">Detail Pelaksanaan Banding</h5>
                    <p class="text-muted mb-0">
                        @if(!$pembayaranBanding)
                        Detail pelaksanaan banding akan tersedia setelah invoice dibuat oleh LAMDEPILAR
                        dan pembayaran telah tervalidasi.
                        @else
                        Detail pelaksanaan banding akan tersedia setelah pembayaran banding
                        dinyatakan <strong>Tervalidasi</strong>.
                        @endif
                    </p>
                </div>
            </div>

            @else
            {{-- ═══ KONTEN PELAKSANAAN — tampil hanya jika lunas ═══ --}}

            {{-- Hasil Akreditasi Awal --}}
            <div class="card mb-4 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data"></i> Hasil Akreditasi Awal (Sebelum Banding)
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $hasil = $pengajuan->asesmen?->hasil;
                    @endphp
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Peringkat Akreditasi</label>
                            <p class="mb-0">
                                @if($pengajuan->peringkat_hasil)
                                <span class="badge text-dark fs-6" style="background-color: {{ $hasil->getPeringkatColor($pengajuan->peringkat_hasil) }};">
                                    {{ $pengajuan->peringkat_hasil }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Hasil Disampaikan</label>
                            <p class="mb-0">
                                {{ $pengajuan->tanggal_hasil_akreditasi_dikirim
                                    ? $pengajuan->tanggal_hasil_akreditasi_dikirim->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Pelaksanaan --}}
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-play-circle"></i> Detail Pelaksanaan Banding
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="35%">Tanggal Banding Diajukan</th>
                            <td>: {{ $pengajuan->tanggal_permohonan_banding
                                ? $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y H:i')
                                : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penerimaan Banding</th>
                            <td>: {{ $pengajuan->tanggal_penerimaan_banding
                                ? $pengajuan->tanggal_penerimaan_banding->locale('id')->translatedFormat('d M Y H:i')
                                : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penugasan Banding</th>
                            <td>: {{ $pengajuan->tanggal_penugasan_banding
                                ? $pengajuan->tanggal_penugasan_banding->locale('id')->translatedFormat('d M Y H:i')
                                : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaksanaan</th>
                            <td>: {{ $pengajuan->tanggal_pelaksanaan_banding
                                ? $pengajuan->tanggal_pelaksanaan_banding->locale('id')->translatedFormat('d M Y H:i')
                                : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaksanaan</th>
                            <td>:
                                {!! $pengajuan->getCustomBadgeLastStatus('pelaksanaan_banding','upps','label_long_for') !!}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif {{-- end payment gate --}}

        </div>

        {{-- ═══════════════ SIDEBAR ═══════════════ --}}
        <div class="col-lg-4">

            {{-- Progress Steps --}}
            <div class="card mb-4 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-diagram-3"></i> Progres Banding</h6>
                </div>
                <div class="card-body">
                    @php
                    $steps = [
                    [
                    'done' => true,
                    'label' => 'Banding Diajukan',
                    'date' => $pengajuan->tanggal_permohonan_banding,
                    'icon' => 'check-circle-fill',
                    'color' => 'dark',
                    ],
                    [
                    'done' => (bool) $pengajuan->tanggal_penerimaan_banding,
                    'label' => 'Banding Diterima',
                    'date' => $pengajuan->tanggal_penerimaan_banding,
                    'icon' => $pengajuan->tanggal_penerimaan_banding ? 'check-circle-fill' : 'circle',
                    'color' => $pengajuan->tanggal_penerimaan_banding ? 'dark' : 'secondary',
                    ],
                    [
                    'done' => $pembayaranLunas,
                    'label' => 'Pembayaran Tervalidasi',
                    'date' => $pembayaranLunas ? ($pembayaranBanding->tanggal_pembayaran ?? null) : null,
                    'icon' => $pembayaranLunas ? 'check-circle-fill' : 'credit-card',
                    'color' => $pembayaranLunas ? 'dark' : ($pembayaranBanding ? 'dark' : 'secondary'),
                    ],
                    [
                    'done' => (bool) $pengajuan->tanggal_penugasan_asesor_ak_banding,
                    'label' => 'Asesor Banding Ditugaskan',
                    'date' => $pengajuan->tanggal_penugasan_asesor_ak_banding,
                    'icon' => $pengajuan->tanggal_penugasan_asesor_ak_banding ? 'check-circle-fill' : 'circle',
                    'color' => $pengajuan->tanggal_penugasan_asesor_ak_banding ? 'dark' : 'secondary',
                    ],
                    [
                    'done' => (bool) $pengajuan->tanggal_pelaksanaan_banding,
                    'label' => 'Pelaksanaan Banding',
                    'date' => $pengajuan->tanggal_pelaksanaan_banding,
                    'icon' => $pengajuan->tanggal_pelaksanaan_banding ? 'check-circle-fill' : 'circle',
                    'color' => $pengajuan->tanggal_pelaksanaan_banding ? 'dark' : 'secondary',
                    ],
                    [
                    'done' => $pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                    'label' => 'Pelaporan Banding',
                    'date' => $pengajuan->tanggal_pelaporan_banding ?? null,
                    'icon' => $pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN
                    ? 'check-circle-fill' : 'circle',
                    'color' => $pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN
                    ? 'dark' : 'secondary',
                    ],
                    ];
                    @endphp

                    @foreach($steps as $i => $step)
                    <div class="d-flex align-items-start {{ $i < count($steps)-1 ? 'mb-3' : '' }}">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-{{ $step['color'] }} text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:14px;">
                                <i class="bi bi-{{ $step['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong class="text-{{ $step['done'] ? 'dark' : 'muted' }} small">
                                {{ $step['label'] }}
                            </strong>
                            @if($step['date'])
                            <br>
                            <small class="text-muted">
                                {{ $step['date']->locale('id')->translatedFormat('d M Y: H i s') }}
                            </small>
                            @endif
                        </div>
                    </div>
                    @if($i < count($steps) - 1) <div class="ms-4 ps-1 border-start border-2 border-{{ $step['done'] ? 'success' : 'secondary' }}" style="height:16px;margin-left:18px!important;">
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
</div>
@endsection
