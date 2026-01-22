@extends('layouts.template.app')

@section('title', 'Detail Surat Permohonan')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.surat-permohonan') }}">Surat Permohonan</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Surat Permohonan
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <a href="{{ route('de.surat-permohonan') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Informasi Pengajuan -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pengajuan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Pengajuan</th>
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
                            <td>: {{ $pengajuan->studyProgram->degreeLevel->name }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengajuan->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Akreditasi</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Pengaju</th>
                            <td>: {{ $pengajuan->pengaju->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Saat Ini</th>
                            <td>
                                : <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen Surat Permohonan -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Dokumen Surat Permohonan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $suratPermohonan = $pengajuan->dokumen->first();
                    @endphp

                    @if($suratPermohonan)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="{{ $suratPermohonan->file_icon_class }} me-3" style="font-size: 32px;"></i>
                            <div>
                                <strong>{{ $suratPermohonan->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $suratPermohonan->file_size_formatted }} •
                                    Diupload: {{ $suratPermohonan->created_at->format('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('de.surat-permohonan.download', $pengajuan->id) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2">Belum ada dokumen surat permohonan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Timeline & Actions -->
        <div class="col-lg-4">
            <!-- Actions -->
            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM && $suratPermohonan)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('de.surat-permohonan.terima', $pengajuan->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Terima Surat Permohonan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Status Log -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @if($pengajuan->statusLog->count() > 0)
                    <div class="timeline">
                        @foreach($pengajuan->statusLog->sortBy('changed_at') as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>{{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $log->changed_at->format('d M Y H:i') }}
                                    </small>
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
        </div>
    </div>
</div>
@endsection
