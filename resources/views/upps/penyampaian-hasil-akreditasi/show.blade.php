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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-trophy"></i> Detail Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.penyampaian-hasil-akreditasi') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Congratulations Alert -->
            <div class="alert alert-success alert-permanent border-start border-4 border-success">
                <div class="d-flex align-items-start">
                    <i class="bi bi-trophy-fill fs-1 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="mb-2 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> Selamat! Program Studi Terakreditasi
                        </h5>
                        <p class="mb-2">
                            Hasil akreditasi untuk program studi <strong>{{ $pengajuan->studyProgram->name }}</strong>
                            telah disampaikan oleh LAMDEPILAR.
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
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i>
                <strong>Tahap Selanjutnya:</strong> Setelah penyampaian hasil akreditasi, akan memasuki periode
                <strong>Masa Sanggah</strong>. Program studi dapat mengajukan banding jika memiliki keberatan
                terhadap hasil akreditasi.
            </div>

            <!-- Informasi Hasil Akreditasi -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Permohonan</th>
                            <td>: {{ $pengajuan->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>: {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Peringkat Akreditasi</th>
                            <td>
                                : @php
                                $badgeClass = match($pengajuan->peringkat_hasil) {
                                'Unggul' => 'bg-warning text-dark',
                                'Baik Sekali' => 'bg-success',
                                'Baik' => 'bg-info',
                                'Tidak Terakreditasi' => 'bg-danger',
                                default => 'bg-secondary',
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6">
                                    @if($pengajuan->peringkat_hasil === 'Unggul')
                                    <i class="bi bi-star-fill"></i>
                                    @elseif($pengajuan->peringkat_hasil === 'Baik Sekali')
                                    <i class="bi bi-award-fill"></i>
                                    @elseif($pengajuan->peringkat_hasil === 'Baik')
                                    <i class="bi bi-check-circle-fill"></i>
                                    @endif
                                    {{ $pengajuan->peringkat_hasil ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Nilai Akhir</th>
                            <td>: <strong>{{ $pengajuan->nilai_akhir ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Hasil Disampaikan</th>
                            <td>
                                : {{ $pengajuan->tanggal_hasil_akreditasi
                                    ? $pengajuan->tanggal_hasil_akreditasi->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('hasil_akreditasi', 'upps') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen Hasil Akreditasi -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check"></i> Dokumen Hasil Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumenHasil = $pengajuan->dokumen
                    ->whereIn('jenis_dokumen', ['sertifikat_akreditasi', 'sk_akreditasi'])
                    ->where('is_latest', true);
                    @endphp

                    @if($dokumenHasil->count() > 0)
                    @foreach($dokumenHasil as $dokumen)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->file_size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->created_at->format('d M Y H:i') }}
                                </small>
                                <br>
                                @if($dokumen->jenis_dokumen === 'sertifikat_akreditasi')
                                <span class="badge bg-success">Sertifikat Akreditasi</span>
                                @else
                                <span class="badge bg-primary">SK Akreditasi</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumen->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Dokumen hasil akreditasi belum tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Hasil -->
            <div class="card mb-4 border-{{ $badgeClass === 'bg-warning text-dark' ? 'warning' : ($badgeClass === 'bg-success' ? 'success' : 'info') }}">
                <div class="card-header {{ $badgeClass }}">
                    <h6 class="mb-0">
                        <i class="bi bi-award"></i> Ringkasan Hasil Akreditasi
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-trophy-fill text-{{ $badgeClass === 'bg-warning text-dark' ? 'warning' : ($badgeClass === 'bg-success' ? 'success' : 'info') }}" style="font-size: 64px;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">{{ $pengajuan->peringkat_hasil ?? '-' }}</h3>
                    @if($pengajuan->nilai_akhir)
                    <p class="mb-3">
                        <span class="text-muted">Nilai Akhir:</span>
                        <br>
                        <span class="fs-4 fw-bold">{{ $pengajuan->nilai_akhir }}</span>
                    </p>
                    @endif
                    <hr>
                    <p class="small text-muted mb-0">
                        Program studi <strong>{{ $pengajuan->studyProgram->name }}</strong>
                        telah meraih peringkat akreditasi <strong>{{ $pengajuan->peringkat_hasil }}</strong>.
                    </p>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Proses
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
                    ->sortBy('changed_at');
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
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>

                                    @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                    @endif
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
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
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
</div>
@endsection
