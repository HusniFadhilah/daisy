{{-- resources/views/de/penyampaian-template/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penyampaian Template')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penyampaian-template') }}">Penyampaian Template</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-arrow-down"></i> Detail Penyampaian Template
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <a href="{{ route('de.penyampaian-template') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Informasi Permohonan -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Permohonan Akreditasi</h5>
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
                            <td>: {{ $pengajuan->studyProgram->degreeLevel->name }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>: {{ $pengajuan->studyProgram->category->name ?? '-' }}</td>
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
                            <th>Status</th>
                            <td>
                                : <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen Template -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-zip"></i> Template LED+Suplemen dan LKPS
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $template = $pengajuan->dokumen->where('jenis_dokumen', 'borang_template')->first();
                    @endphp

                    @if($template)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            @if($template->template_link)
                            <i class="bi bi-link-45deg me-3 text-primary" style="font-size: 32px;"></i>
                            <div>
                                <strong>Template via Link</strong>
                                <br>
                                <small class="text-muted">
                                    Dikirim: {{ $template->created_at->format('d M Y H:i') }}
                                </small>
                                @if($template->keterangan)
                                <br>
                                <small class="text-muted fst-italic">{{ $template->keterangan }}</small>
                                @endif
                            </div>
                            @else
                            <i class="{{ $template->file_icon_class }} me-3" style="font-size: 32px;"></i>
                            <div>
                                <strong>{{ $template->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $template->file_size_formatted }} •
                                    Dikirim: {{ $template->created_at->format('d M Y H:i') }}
                                </small>
                                @if($template->keterangan)
                                <br>
                                <small class="text-muted fst-italic">{{ $template->keterangan }}</small>
                                @endif
                            </div>
                            @endif
                        </div>
                        <div>
                            @if($template->template_link)
                            <a href="{{ $template->template_link }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Link
                            </a>
                            @else
                            <a href="{{ route('de.penyampaian-template.download', $pengajuan->id) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-download"></i> Download
                            </a>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2">Template belum dikirim</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions & Timeline -->
        <div class="col-lg-4">
            <!-- Actions -->
            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-content" type="button">
                                Via Link
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-content" type="button">
                                Upload
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Tab Link -->
                        <div class="tab-pane fade show active" id="link-content">
                            <form action="{{ route('de.penyampaian-template.kirim-link', $pengajuan->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Link Template <span class="text-danger">*</span></label>
                                    <input type="url" name="template_link" class="form-control" placeholder="https://..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-send"></i> Kirim Template
                                </button>
                            </form>
                        </div>

                        <!-- Tab Upload -->
                        <div class="tab-pane fade" id="upload-content">
                            <form action="{{ route('de.penyampaian-template.kirim-upload', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">File Template <span class="text-danger">*</span></label>
                                    <input type="file" name="file_template" class="form-control" accept=".pdf,.zip,.rar,.docx" required>
                                    <small class="text-muted">Max 50MB</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i> Upload Template
                                </button>
                            </form>
                        </div>
                    </div>
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
                <div class="card-body">
                    @if($pengajuan->statusLog->count() > 0)
                    <div class="timeline">
                        @foreach($pengajuan->statusLog->sortByDesc('changed_at') as $log)
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
