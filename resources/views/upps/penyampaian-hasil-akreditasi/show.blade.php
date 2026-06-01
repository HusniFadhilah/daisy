{{-- resources/views/upps/penyampaian-hasil-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Hasil Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.penyampaian-hasil-akreditasi') }}">Penyampaian Hasil Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Detail Penyampaian Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <div class="d-flex gap-2">
            @if($pengajuan->nomor_sertifikat)
            <a href="{{ route('upps.penyampaian-hasil-akreditasi.preview-sertifikat', $pengajuan->id) }}" class="btn btn-outline-dark" target="_blank">
                <i class="bi bi-patch-check"></i> Lihat Sertifikat
            </a>
            @endif
            <a href="{{ route('upps.penyampaian-hasil-akreditasi') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Congratulations Alert -->
            <div class="alert alert-light alert-permanent border-start border-2 border-dark">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        {{-- <h5 class="mb-2 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> Program Studi Anda <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkat) }}; color:#222">{{ $peringkat }}</span>
                        </h5> --}}
                        <p class="mb-2">
                            Hasil akreditasi untuk program studi <strong>{{ $pengajuan->studyProgram->name }}</strong>
                            telah disampaikan oleh LAMDEPILAR.
                        </p>
                        @if($pengajuan->peringkat_hasil)
                        Status Akreditasi:
                        <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor(null, 'hasil') }}; color:#222">{{ $peringkat }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info Tahap Selanjutnya -->
            <div class="alert alert-light alert-permanent">
                <i class="bi bi-info-circle"></i>
                <strong>Tahap Selanjutnya:</strong> Setelah penyampaian hasil akreditasi, akan memasuki periode
                <strong>Masa Sanggah</strong>. Program studi dapat mengajukan banding jika memiliki keberatan
                terhadap hasil akreditasi.
            </div>

            <!-- ✅ Berita Acara Section -->
            @if($beritaAcara)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start">
                        <div class="d-flex align-items-start flex-grow-1">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Berita Acara Rapat Penyampaian Hasil</h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-file-pdf text-danger"></i>
                                    {{ $beritaAcara->original_name }}
                                </p>
                                <small class="text-muted">
                                    Diupload pada {{ $beritaAcara->uploaded_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penyampaian-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-primary mt-3" target="_blank">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informasi Hasil Akreditasi -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Penyampaian Hasil Akreditasi
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
                            <th>Status Akreditasi Disampaikan</th>
                            <td>
                                : <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor(null, 'hasil') }}; color:#222">{{ $peringkat }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Hasil Disampaikan</th>
                            <td>
                                : {{ $pengajuan->tanggal_hasil_akreditasi_dikirim
                                    ? $pengajuan->tanggal_hasil_akreditasi_dikirim->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Penyampaian Hasil Akreditasi</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('penyampaian_hasil', 'upps','label_long_for','text-dark') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil -->

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
                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
                    \App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
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
                        <li>Penyampaian hasil akreditasi (selesai)</li>
                        <li><strong>Masa sanggah</strong> - periode untuk pengajuan banding</li>
                        <li>Penetapan hasil akhir akreditasi</li>
                        <li>Pengumuman hasil akreditasi</li>
                        <li>Proses selesai</li>
                    </ol>

                    <hr>

                    <p class="small text-muted mb-0">
                        <i class="bi bi-exclamation-circle"></i>
                        Jika memiliki keberatan terhadap hasil, dapat mengajukan banding pada tahap masa sanggah.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <!-- ✅ Detail Skor per Elemen Standar (UPDATED) -->
            @if(!empty($elemenList))
            @php
            // ✅ Group elements by kriteria
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
                        <!-- Alternative: Tanpa merged cells (untuk DataTables) -->
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
                                @php $rowspan = $elemens->count(); @endphp
                                @foreach($elemens as $index => $elemen)
                                @php
                                $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'color' => '#e9ecef'];
                                @endphp
                                <tr>
                                    {{-- ✅ Merge kriteria cells --}}
                                    @if($index === 0)
                                    <td class="text-center align-middle fw-bold" rowspan="{{ $rowspan }}">
                                        <span class="badge bg-secondary fs-6 py-2 px-3">{{ $kodeKriteria }}</span>
                                    </td>
                                    @endif

                                    {{-- ✅ Kode elemen + nama elemen --}}
                                    <td>
                                        <code class="text-primary me-2 fw-bold">{{ $elemen['kode_elemen'] }}</code>
                                        {{ $elemen['nama_elemen'] }}
                                    </td>

                                    {{-- ✅ Kategori --}}
                                    <td class="text-center">
                                        <span class="badge text-wrap py-2 px-3" style="background-color: {{ $kategori['color'] }}; color: #222; width: 220px; font-size: 0.85rem;">
                                            {{ $kategori['label'] === 'Melampaui Standar' ? 'Melampaui' : $kategori['label'] }}
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
