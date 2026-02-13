{{-- resources/views/de/penyampaian-template/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pengiriman Formulir dan Templat Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penyampaian-template') }}">Pengiriman Formulir dan Templat Dokumen</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark"></i> Detail Pengiriman Formulir dan Templat Dokumen
            </h5>
            <small class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.penyampaian-template') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if($uploadRequests->count() > 0)
    <div class="alert alert-warning alert-permanent mb-4">
        <h6 class="fw-bold">
            <i class="bi bi-exclamation-triangle-fill"></i> Permintaan Pengiriman Ulang Dokumen
            <span class="badge bg-danger ms-2">{{ $uploadRequests->count() }}</span>
        </h6>

        @foreach($uploadRequests as $notif)
        @php
        $data = $notif->data;
        $jenisDokumenLabels = $data['jenis_dokumen_label'] ?? [];
        @endphp
        <div class="border-start border-warning border-3 ps-3 mb-3">
            <p class="mb-1">
                <strong>Dokumen yang diminta:</strong>
                <span class="badge bg-warning text-dark">{{ implode(', ', $jenisDokumenLabels) }}</span>
            </p>
            <p class="mb-1">
                <strong>Diminta oleh:</strong>
                ({{ $data['program_studi'] ?? '-' }})
            </p>
            <p class="mb-1">
                <strong>Tanggal Permintaan:</strong>
                {{ \Carbon\Carbon::parse($data['requested_at'])->locale('id')->translatedFormat('d M Y H:i') }}
            </p>
            <p class="mb-2">
                <strong>Alasan:</strong><br>
                <em class="text-dark">{{ $data['alasan_request'] ?? '-' }}</em>
            </p>

            {{-- ✅ Quick Action Button --}}
            <button type="button" class="btn btn-sm btn-success" onclick="scrollToUploadForm('{{ $notif->id }}')">
                <i class="bi bi-arrow-down-circle"></i> Proses Sekarang
            </button>
            {{-- <button type="button" class="btn btn-sm btn-outline-secondary" onclick="markAsRead('{{ $notif->id }}')">
            <i class="bi bi-check"></i> Tandai Sudah Dibaca
            </button> --}}
        </div>
        @endforeach
    </div>
    @endif

    <div class="row">
        <!-- Informasi Permohonan -->
        <div class="col-lg-8 mb-4">
            {{-- ✅ UPLOAD FORM (Updated) --}}
            <div class="card mb-4" id="upload-form-section">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-upload"></i>
                        {{ $hasExistingDokumen ? 'Kirim Ulang' : 'Proses Pengiriman' }}
                        Formulir dan Templat Dokumen
                    </h5>
                </div>

                <div class="card-body">
                    @if($hasExistingDokumen)
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Info:</strong> Dokumen sudah pernah dikirim. Anda dapat mengirim ulang dengan mengupload file baru.
                        File lama akan tetap tersimpan sebagai riwayat (versi sebelumnya).
                    </div>
                    @endif

                    <form action="{{ route('de.penyampaian-template.kirim-upload', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ✅ Hidden field untuk notification_id --}}
                        <input type="hidden" name="notification_id" id="notification_id" value="">

                        {{-- TEMPLAT FORMULIR PEMBAYARAN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Templat Formulir Pembayaran
                                @if(!$formulirPembayaran)
                                <span class="text-danger">*</span>
                                @else
                                <span class="badge bg-secondary">Opsional (untuk upload ulang)</span>
                                @endif
                            </label>

                            <input type="file" name="file_template_pembayaran" id="template_formulir" class="form-control @error('file_template_pembayaran') is-invalid @enderror" accept=".xls,.xlsx" {{ !$formulirPembayaran ? 'required' : '' }}>
                            <small>Format file yang diizinkan: XLSX/XLS</small>
                            @error('file_template_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                File terpilih: <span id="template_formulir_name">
                                    {{ $formulirPembayaran ? 'Gunakan file lama atau upload baru' : '-' }}
                                </span>
                            </small>
                        </div>

                        {{-- TEMPLAT DOKUMEN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Templat Dokumen
                                @if(!$templateLed)
                                <span class="text-danger">*</span>
                                @else
                                <span class="badge bg-secondary">Opsional (untuk upload ulang)</span>
                                @endif
                            </label>

                            <input type="file" name="file_template_led" id="template_dokumen" class="form-control @error('file_template_led') is-invalid @enderror" accept=".rar,.zip,.pdf,.doc,.docx,.xls,.xlsx" {{ !$templateLed ? 'required' : '' }}>
                            <small>Format file yang diizinkan: RAR/ZIP/PDF/DOCX/XLSX</small>
                            @error('file_template_led')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                File terpilih: <span id="template_dokumen_name">
                                    {{ $templateLed ? 'Gunakan file lama atau upload baru' : '-' }}
                                </span>
                            </small>
                        </div>

                        {{-- KETERANGAN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Pemberitahuan Pengiriman
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" required>{{ old('keterangan', 'Templat Dokumen LED telah tersedia dalam satu berkas Ms.Word (.docx) (termasuk lembar pengesahan), sedangkan templat LKPS disediakan dalam file Excel (.xlsx).') }}</textarea>
                            @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Pesan ini akan dikirim ke Program Studi bersama dengan dokumen.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-arrow-up"></i>
                            {{ $hasExistingDokumen ? 'Kirim Ulang' : 'Kirim' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Informasi Permohonan --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Formulir dan Templat Dokumen
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Templat Dikirim</th>
                            <td>
                                : {{ $pengajuan->tanggal_template_led_dikirim
                                    ? $pengajuan->tanggal_template_led_dikirim->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Formulir dan Templat Dokumen</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('borang_template','de') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($hasExistingDokumen)
            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle"></i> Cek Dokumen yang Sudah Dikirim
                    </h5>
                </div>
                <div class="card-body">
                    @if($templateLed)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center">
                            @if($templateLed->template_link)
                            <i class="bi bi-link-45deg me-3 text-primary" style="font-size: 48px;"></i>
                            @else
                            <i class="{{ $templateLed->file_icon_class }} me-3" style="font-size: 48px;"></i>
                            @endif
                            <div>
                                <strong>Templat Dokumen Akreditasi</strong>
                                <span class="badge bg-info ms-2">Versi {{ $templateLed->versi }}</span>
                                <br>
                                @if($templateLed->template_link)
                                <small class="text-muted">Via Link</small>
                                @else
                                <small class="text-muted">
                                    {{ $templateLed->original_filename }}
                                </small>
                                @endif
                                <br>
                                <small class="text-muted">
                                    Dikirim: {{ $templateLed->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                @if($templateLed->keterangan)
                                <br>
                                <small class="text-muted fst-italic">{{ $templateLed->keterangan }}</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($templateLed->template_link)
                            <a href="{{ $templateLed->template_link }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Link
                            </a>
                            @else
                            <a href="{{ route('de.penyampaian-template.download', [$pengajuan->id, 'borang_template']) }}" class="btn btn-success btn-sm" target="_blank">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($formulirPembayaran)
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            @if($formulirPembayaran->template_link)
                            <i class="bi bi-link-45deg me-3 text-primary" style="font-size: 48px;"></i>
                            @else
                            <i class="{{ $formulirPembayaran->file_icon_class }} me-3" style="font-size: 48px;"></i>
                            @endif
                            <div>
                                <strong>Formulir Pembayaran</strong>
                                <span class="badge bg-info ms-2">Versi {{ $formulirPembayaran->versi }}</span>
                                <br>
                                @if($formulirPembayaran->template_link)
                                <small class="text-muted">Via Link</small>
                                @else
                                <small class="text-muted">
                                    {{ $formulirPembayaran->original_filename }}
                                </small>
                                @endif
                                <br>
                                <small class="text-muted">
                                    Dikirim: {{ $formulirPembayaran->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                                @if($formulirPembayaran->keterangan)
                                <br>
                                <small class="text-muted fst-italic">{{ $formulirPembayaran->keterangan }}</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($formulirPembayaran->template_link)
                            <a href="{{ $formulirPembayaran->template_link }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Link
                            </a>
                            @else
                            <a href="{{ route('de.penyampaian-template.download', [$pengajuan->id, 'template_formulir_pembayaran']) }}" class="btn btn-success btn-sm" target="_blank">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
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
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
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
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        Formulir dan Templat Dokumen Diterima
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>

                                    {{-- @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">Formulir Pembayaran (Formulir Pembayaran Akreditasi LAMDEPILAR.xlsx) dan Templat Dokumen (Templat Dokumen.rar) telah diterima oleh PS</small>
                                    @endif --}}
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

        // ✅ Function to scroll to upload form
        window.scrollToUploadForm = function(notificationId) {
            document.getElementById('notification_id').value = notificationId;
            document.getElementById('upload-form-section').scrollIntoView({
                behavior: 'smooth'
                , block: 'center'
            });

            // Highlight form
            const formSection = document.getElementById('upload-form-section');
            formSection.classList.add('border-warning', 'border-3');
            setTimeout(() => {
                formSection.classList.remove('border-warning', 'border-3');
            }, 2000);
        };

        // ✅ Function to mark notification as read
        window.markAsRead = function(notificationId) {
            if (confirm('Tandai notifikasi ini sebagai sudah dibaca?')) {
                fetch(`/de/notifications/${notificationId}/mark-as-read`, {
                        method: 'POST'
                        , headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            , 'Accept': 'application/json'
                        , }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        };
    })();

</script>
@endpush
