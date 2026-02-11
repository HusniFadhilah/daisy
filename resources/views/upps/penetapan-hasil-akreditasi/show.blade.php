{{-- resources/views/upps/penetapan-hasil-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penetapan Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('upps.penetapan-hasil-akreditasi') }}">Penetapan Hasil Akreditasi</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-info-circle"></i> Detail Penetapan Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penetapan-hasil-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">

            <!-- Info Alert -->
            <div class="alert alert-light alert-permanent border-start border-2 border-dark">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <p class="mb-2">
                            Hasil akreditasi untuk program studi <strong>{{ $pengajuan->studyProgram->name }}</strong>
                            telah <strong>ditetapkan</strong> oleh LAMDEPILAR.
                        </p>
                        @if($pengajuan->peringkat_hasil)
                        <div class="alert alert-light border border-success mb-0">
                            <i class="bi bi-star-fill text-warning"></i>
                            Peringkat Akreditasi:
                            <strong class="text-success fs-5">{{ $pengajuan->peringkat_hasil }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info Tahap Selanjutnya -->
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-info-circle"></i>
                <strong>Tahap Selanjutnya:</strong> Setelah penetapan hasil akreditasi, hasil akan masuk ke tahap
                <strong>Pelaporan Hasil</strong> sesuai ketentuan yang berlaku.<br>Program studi dapat mengunduh berita acara rapat penetapan hasil sebagai berikut
            </div>

            <!-- ✅ Berita Acara Section -->
            @if($beritaAcara)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-start flex-grow-1">
                            <div class="flex-grow-1 ms-2">
                                <h5 class="mb-1">Berita Acara Rapat Penetapan Hasil</h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-file-pdf text-danger"></i>
                                    {{ $beritaAcara->original_name }}
                                </p>
                                <small class="text-muted">
                                    Diupload pada {{ $beritaAcara->uploaded_at?->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penetapan-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informasi Penetapan Hasil -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penetapan Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan Akreditasi</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Peringkat Akreditasi</th>
                            <td>
                                : <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkat) }}; color:#222">
                                    {{ $peringkat }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Hasil Ditetapkan</th>
                            <td>
                                : {{ $pengajuan->tanggal_hasil_akreditasi_dikirim
                                        ? $pengajuan->tanggal_hasil_akreditasi_dikirim->format('d M Y H:i')
                                        : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penetapan Hasil Akreditasi</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penetapan_hasil', 'upps','label_long_for','text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
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
                                    <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">Belum ada riwayat</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Proses
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Tahapan Berikutnya:</strong>
                    </p>
                    <ol class="small mb-0 ps-3 text-muted">
                        <li>Penyampaian hasil akreditasi</li>
                        <li>Masa sanggah</li>
                        <li><strong>Penetapan hasil akhir akreditasi</strong> (selesai)</li>
                        <li>Pelaporan hasil akreditasi</li>
                        <li>Penyimpanan Arsip Akreditasi</li>
                    </ol>

                    <hr>

                    <p class="small text-muted mb-0">
                        <i class="bi bi-exclamation-circle"></i>
                        Hasil yang telah ditetapkan akan dilanjutkan ke tahap pelaporan hasil akreditasi.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Detail Skor per Elemen Standar -->
    <div class="row">
        <div class="col-lg-12">
            @if(!empty($elemenList))
            @php
            $groupedByKriteria = collect($elemenList)->groupBy('kode_kriteria');
            @endphp

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check text-secondary"></i>
                        Detail Kategori per Elemen Standar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%" class="text-center">Kriteria</th>
                                    <th width="57%">Pernyataan Elemen</th>
                                    <th width="35%" class="text-center">Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedByKriteria as $kodeKriteria => $elemens)
                                @foreach($elemens as $index => $elemen)
                                @php
                                $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'color' => '#e9ecef'];
                                @endphp
                                <tr>
                                    @if($index === 0)
                                    <td class="text-center align-middle fw-bold" rowspan="{{ count($elemens) }}">
                                        <span class="badge bg-secondary fs-6 py-2 px-3">{{ $kodeKriteria }}</span>
                                    </td>
                                    @endif

                                    <td>
                                        <code class="text-primary me-2 fw-bold">{{ $elemen['kode_elemen'] }}</code>
                                        {{ $elemen['nama_elemen'] }}
                                    </td>

                                    <td class="text-center">
                                        <span class="badge text-wrap py-2 px-3" style="background-color: {{ $kategori['color'] }}; color: #222; width: 220px; font-size: 0.85rem;">
                                            {{ $kategori['label'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#table-elemen').DataTable({
            pageLength: 25
            , order: []
            , columnDefs: [{
                orderable: false
                , targets: '_all'
            }]
            , language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });
    });

</script>
@endpush
