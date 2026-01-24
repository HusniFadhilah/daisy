@extends('layouts.template.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('de.masa-sanggah') }}">Masa Sanggah</a>
                    </li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="bi bi-clock-history text-primary"></i>
                Masa Sanggah: {{ $pengajuan->studyProgram->name }}
            </h1>
        </div>

        <div>
            <a href="{{ route('de.masa-sanggah') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        {{-- Peringkat --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Peringkat Akreditasi</h6>
                    @if($hasil && $hasil->peringkat_akreditasi)
                    @php
                    $badgeClass = match($hasil->peringkat_akreditasi) {
                    'Unggul' => 'success',
                    'Baik Sekali' => 'primary',
                    'Baik' => 'info',
                    default => 'secondary'
                    };
                    @endphp
                    <h2 class="mb-0 text-{{ $badgeClass }}">{{ $hasil->peringkat_akreditasi }}</h2>
                    @else
                    <h2 class="mb-0 text-muted">-</h2>
                    @endif
                </div>
            </div>
        </div>

        {{-- Skor Final --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Skor Final</h6>
                    @if($hasil && $hasil->skor_final)
                    <h1 class="mb-0 text-info display-4">{{ number_format($hasil->skor_final, 2) }}</h1>
                    <small class="text-muted">dari 400</small>
                    @else
                    <h2 class="mb-0 text-muted">-</h2>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Masa Sanggah --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Status Masa Sanggah</h6>
                    <span class="badge bg-{{ $masaSanggahInfo['badge_class'] }} fs-6 px-4 py-2">
                        @if($masaSanggahInfo['is_active'])
                        <i class="bi bi-hourglass-split"></i> Aktif
                        @elseif($masaSanggahInfo['is_expired'])
                        <i class="bi bi-check-circle"></i> Selesai
                        @else
                        <i class="bi bi-clock"></i> Belum Dimulai
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Countdown --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">
                        @if($masaSanggahInfo['is_expired'])
                        Sudah Lewat
                        @else
                        Sisa Waktu
                        @endif
                    </h6>
                    <h3 class="mb-0 text-{{ $masaSanggahInfo['badge_class'] }}">
                        {{ $masaSanggahInfo['countdown_text'] }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline Masa Sanggah --}}
    @if($pengajuan->tanggal_masa_sanggah_mulai)
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-calendar-range"></i>
                Timeline Masa Sanggah
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="border-end pe-3">
                        <h6 class="text-muted">Tanggal Hasil Akreditasi</h6>
                        <p class="mb-0 fw-semibold">
                            {{ $pengajuan->tanggal_hasil_akreditasi->format('d F Y') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-end pe-3">
                        <h6 class="text-muted">Masa Sanggah Mulai</h6>
                        <p class="mb-0 fw-semibold">
                            {{ \Carbon\Carbon::parse($pengajuan->tanggal_masa_sanggah_mulai)->format('d F Y') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Masa Sanggah Selesai</h6>
                    <p class="mb-0 fw-semibold">
                        {{ \Carbon\Carbon::parse($pengajuan->tanggal_masa_sanggah_selesai)->format('d F Y, H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Action Buttons --}}
    @if($pengajuan->status === App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM)
    <div class="alert alert-info">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                Hasil akreditasi telah disampaikan. Klik tombol di bawah untuk memulai masa sanggah (7 hari).
            </div>
            <form action="{{ route('de.masa-sanggah.start', $pengajuan->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Mulai masa sanggah sekarang?')">
                    <i class="bi bi-play-circle"></i> Mulai Masa Sanggah
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($masaSanggahInfo['is_expired'] && !$masaSanggahInfo['has_banding'] && $pengajuan->status === App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH)
    <div class="alert alert-warning">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-exclamation-triangle me-2"></i>
                Masa sanggah telah selesai dan tidak ada pengajuan banding. Akhiri masa sanggah untuk melanjutkan ke penetapan hasil.
            </div>
            <form action="{{ route('de.masa-sanggah.end', $pengajuan->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Akhiri masa sanggah dan tetapkan hasil?')">
                    <i class="bi bi-check-circle"></i> Akhiri Masa Sanggah
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Countdown Alert --}}
    @if($masaSanggahInfo['is_hampir_habis'])
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Perhatian!</strong> Masa sanggah akan berakhir dalam {{ $masaSanggahInfo['countdown_text'] }}.
    </div>
    @endif

    {{-- Status Banding --}}
    <div class="card mb-4">
        <div class="card-header bg-{{ $masaSanggahInfo['has_banding'] ? 'danger' : 'secondary' }} text-white">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-break"></i>
                Status Banding
            </h5>
        </div>
        <div class="card-body">
            @if($masaSanggahInfo['has_banding'])
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Program studi telah mengajukan banding!</strong>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6>Informasi Banding</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Tanggal Pengajuan:</th>
                            <td>{{ $pengajuan->tanggal_banding ? $pengajuan->tanggal_banding->format('d F Y, H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaksanaan:</th>
                            <td>{{ $pengajuan->tanggal_pelaksanaan_banding ? \Carbon\Carbon::parse($pengajuan->tanggal_pelaksanaan_banding)->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Dokumen Banding</h6>
                    @if($dokumenBanding)
                    <div class="list-group">
                        <a href="{{ route('pengajuan.dokumen.download', $dokumenBanding->id) }}" class="list-group-item list-group-item-action" target="_blank">
                            <i class="bi bi-file-pdf text-danger"></i>
                            {{ $dokumenBanding->original_filename }}
                            <small class="text-muted d-block">
                                {{ $dokumenBanding->created_at->format('d M Y, H:i') }}
                            </small>
                        </a>
                    </div>
                    @else
                    <p class="text-muted">Belum ada dokumen</p>
                    @endif

                    @if($laporanBanding)
                    <h6 class="mt-3">Laporan Banding</h6>
                    <div class="list-group">
                        <a href="{{ route('pengajuan.dokumen.download', $laporanBanding->id) }}" class="list-group-item list-group-item-action" target="_blank">
                            <i class="bi bi-file-pdf text-success"></i>
                            {{ $laporanBanding->original_filename }}
                            <small class="text-muted d-block">
                                {{ $laporanBanding->created_at->format('d M Y, H:i') }}
                            </small>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="text-center py-4">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <p class="text-muted mt-3 mb-0">Tidak ada pengajuan banding</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Detail Hasil Akreditasi --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-data"></i>
                Detail Hasil Akreditasi
            </h5>
        </div>
        <div class="card-body">
            @if($hasil)
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Peringkat:</th>
                            <td>
                                @php
                                $badgeClass = match($hasil->peringkat_akreditasi) {
                                'Unggul' => 'bg-success',
                                'Baik Sekali' => 'bg-primary',
                                'Baik' => 'bg-info',
                                default => 'bg-secondary'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $hasil->peringkat_akreditasi }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Skor AK:</th>
                            <td>{{ number_format($hasil->skor_ak, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Skor AL:</th>
                            <td>{{ number_format($hasil->skor_al, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Skor Final:</th>
                            <td><strong>{{ number_format($hasil->skor_final, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Tanggal Finalisasi:</th>
                            <td>{{ $hasil->tanggal_finalisasi_al ? $hasil->tanggal_finalisasi_al->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Memenuhi Syarat Unggul:</th>
                            <td>
                                @if($hasil->memenuhi_syarat_unggul)
                                <i class="bi bi-check-circle-fill text-success"></i> Ya
                                @else
                                <i class="bi bi-x-circle-fill text-danger"></i> Tidak
                                @endif
                            </td>
                        </tr>
                    </table>

                    <a href="{{ route('de.penyampaian-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-eye"></i> Lihat Detail Lengkap
                    </a>
                </div>
            </div>

            @if($hasil->catatan_validasi)
            <hr>
            <h6>Catatan Validasi:</h6>
            <pre class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $hasil->catatan_validasi }}</pre>
            @endif
            @else
            <p class="text-muted mb-0">Hasil akreditasi belum tersedia</p>
            @endif
        </div>
    </div>
</div>
@endsection
