@extends('layouts.template.app')

@section('title', 'Detail Pelaporan AL - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('de.pelaporan-al') }}">
                    <i class="bi bi-arrow-left"></i> Monitoring Pelaporan AL
                </a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Detail Pelaporan AL
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            @if($statusPelaporan['is_reported'])
            <span class="badge bg-success fs-6">
                <i class="bi bi-check-circle-fill"></i> Sudah Dilaporkan
            </span>
            @else
            <span class="badge bg-warning fs-6">
                <i class="bi bi-clock"></i> Menunggu Pelaporan
            </span>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Info -->
        <div class="col-lg-4">
            <!-- Program Studi Info -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Program Studi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Program Studi</td>
                            <td><strong>{{ $pengajuan->studyProgram->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Universitas</td>
                            <td>{{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenjang</td>
                            <td>{{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge bg-info text-wrap">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- AL Schedule -->
            @if($pengajuan->asesmen?->asesmenLapangan)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-range"></i> Jadwal AL
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        @if($pengajuan->asesmen->asesmenLapangan->tanggal_mulai)
                        <tr>
                            <td class="text-muted" width="40%">Tanggal Mulai</td>
                            <td><strong>{{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_mulai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)
                        <tr>
                            <td class="text-muted">Tanggal Selesai</td>
                            <td><strong>{{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($statusPelaporan['reported_at'])
                        <tr>
                            <td class="text-muted">Tanggal Dilaporkan</td>
                            <td>
                                <strong class="text-success">
                                    {{ \Carbon\Carbon::parse($statusPelaporan['reported_at'])->format('d M Y') }}
                                </strong>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Laporan Documents -->
        <div class="col-lg-8">
            <!-- Validator Info -->
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Validator yang Ditugaskan</h6>
                </div>
                <div class="card-body">
                    @php
                    $validators = $pengajuan->asesmen?->asesmenUserRoles->filter(function($aur) {
                    return $aur->role_selected->name === 'validator';
                    }) ?? collect();
                    @endphp

                    @if($validators->count() > 0)
                    <div class="row">
                        @foreach($validators as $validator)
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-person-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>{{ $validator->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $validator->user->email }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">Belum ada validator yang ditugaskan</p>
                    @endif
                </div>
            </div>

            <!-- Laporan Documents -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Dokumen Laporan AL</h6>
                    @if($statusPelaporan['has_laporan'])
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> {{ $laporanDocuments->count() }} file
                    </span>
                    @else
                    <span class="badge bg-danger">
                        <i class="bi bi-x-circle"></i> Belum ada laporan
                    </span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($laporanDocuments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Nama File</th>
                                    <th width="15%">Ukuran</th>
                                    <th width="20%">Diupload Oleh</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laporanDocuments as $index => $doc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                        <strong>{{ $doc->title ?? $doc->original_name }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $doc->size ? number_format($doc->size / 1024, 2) : '-' }} KB</small>
                                    </td>
                                    <td>
                                        <small>{{ $doc->uploadedBy->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $doc->uploaded_at?->format('d M Y H:i') ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="btn btn-sm btn-primary" title="Lihat File">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada dokumen laporan yang diupload</p>
                        <small class="text-muted">Validator perlu mengupload laporan hasil AL</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
