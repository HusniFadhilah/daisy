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
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-arrow-down"></i> Detail Pengiriman Formulir dan Template Dokumen
            </h5>
            <small class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.penyampaian-template') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Informasi Permohonan -->
        <div class="col-lg-8 mb-4">
            {{-- ================= UPLOAD TEMPLATE (CHOOSE FILE) ================= --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-upload"></i> Proses Pengiriman Formulir dan Template Dokumen
                    </h5>
                </div>

                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- TEMPLATE DOKUMEN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Template Dokumen <span class="text-danger">*</span>
                            </label>

                            <input type="file" name="template_dokumen" id="template_dokumen" class="form-control @error('template_dokumen') is-invalid @enderror" accept=".rar,.zip,.pdf,.doc,.docx,.xls,.xlsx">
                            <small>Format file yang diizinkan: RAR/ZIP/PDF/DOCX/XLSX</small>
                            @error('template_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                File terpilih: <span id="template_dokumen_name">-</span>
                            </small>
                        </div>

                        {{-- TEMPLATE FORMULIR PEMBAYARAN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Template Formulir Pembayaran <span class="text-danger">*</span>
                            </label>

                            <input type="file" name="template_formulir" id="template_formulir" class="form-control @error('template_formulir') is-invalid @enderror" accept=".xls">
                            <small>Format file yang diizinkan: XLSX/XLS</small>
                            @error('template_formulir')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                File terpilih: <span id="template_formulir_name">-</span>
                            </small>
                        </div>

                        {{-- KETERANGAN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pemberitahuan Pengiriman <span class="text-danger">*</span></label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan','Template Dokumen LED telah tersedia dalam satu berkas Ms.Word (.docx) (termasuk lembar pengesahan), sedangkan template LKPS disediakan dalam file Excel (.xlsx).') }}</textarea>
                            @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-arrow-up"></i> Kirim
                        </button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pengiriman Formulir dan Template Dokumen</h5>
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
                            <th>Akreditasi Kedaluwarsa</th>
                            <td>: {{ $pengajuan->studyProgram->days_left ? $pengajuan->studyProgram->days_left.' hari lagi': '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Permohonan</th>
                            <td>: {{ $pengajuan->jenis_akreditasi_label }}</td>
                        </tr>
                        <tr>
                            <th>Pemohon</th>
                            <td>: {{ $pengajuan->pengaju->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Pengiriman Formulir dan Template Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('borang_template') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dokumen Template -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-zip"></i> Cek Template Formulir Terkirim
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $formulir = $pengajuan->dokumen->where('jenis_dokumen', 'template_formulir_pembayaran')->first();
                    @endphp

                    @if($formulir)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            @if($formulir->template_link)
                            <i class="bi bi-link-45deg me-3 text-primary" style="font-size: 32px;"></i>
                            <div>
                                <strong>Formulir via Link</strong>
                                <br>
                                <small class="text-muted">
                                    Dikirim: {{ $formulir->created_at->format('d M Y H:i') }}
                                </small>
                                @if($formulir->keterangan)
                                <br>
                                <small class="text-muted fst-italic">{{ $formulir->keterangan }}</small>
                                @endif
                            </div>
                            @else
                            <i class="{{ $formulir->file_icon_class }} me-3" style="font-size: 32px;"></i>
                            <div>
                                <strong>{{ $formulir->original_filename }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $formulir->file_size_formatted ?? '' }} •
                                    Dikirim: {{ $formulir->created_at->format('d M Y H:i') }}
                                </small>
                                @if($formulir->keterangan)
                                <br>
                                <small class="text-muted fst-italic">{{ $formulir->keterangan }}</small>
                                @endif
                            </div>
                            @endif
                        </div>
                        <div>
                            @if($formulir->template_link)
                            <a href="{{ $formulir->template_link }}" target="_blank" class="btn btn-primary btn-sm">
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
                        <p class="text-muted mt-2">Template formulir belum dikirim</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-zip"></i> Cek Template Dokumen Terkirim
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
                                    {{ $template->file_size_formatted ?? '' }} •
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
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('changed_at');
            @endphp

            <!-- Status Log -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
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
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        Penerimaan Formulir dan Template Dokumen
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>

                                    @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">Formulir Pembayaran (Formulir Pembayaran Akreditasi LAMDEPILAR.xlsx) dan Template Dokumen (Template Dokumen.rar) telah diterima oleh PS</small>
                                    @endif
                                </div>
                            </div>
                        </div>
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

@push('scripts')
<script>
    (function() {
        const dok = document.getElementById('template_dokumen');
        const frm = document.getElementById('template_formulir');

        const dokName = document.getElementById('template_dokumen_name');
        const frmName = document.getElementById('template_formulir_name');

        if (dok) {
            dok.addEventListener('change', function() {
                dokName.textContent = (dok.files && dok.files[0]) ? dok.files[0].name : '-';
            });
        }

        if (frm) {
            frm.addEventListener('change', function() {
                frmName.textContent = (frm.files && frm.files[0]) ? frm.files[0].name : '-';
            });
        }
    })();

</script>
@endpush
