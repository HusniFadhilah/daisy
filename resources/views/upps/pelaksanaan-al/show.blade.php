{{-- resources/views/upps/pelaksanaan-al/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-al') }}">Pelaksanaan AL</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Pelaksanaan AL & Berita Acara
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('upps.pelaksanaan-al') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            ]; // ini contoh, bisa dinamis dari config/db/request

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp
            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaksanaan AL</strong><br>
                Permohonan akreditasi program studi memasuki tahap pelaksanaan AL
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaksanaan AL</strong><br>
                Permohonan akreditasi program studi memasuki tahap pelaksanaan AL
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-person-check"></i>
                <strong>Proses Pelaksanaan AL</strong><br>
                Berita acara pelaksanaan AL program studi dapat diunduh pada link berikut
                Mohon program studi dapat melakukan tanggapan laporan hasil AL dengan melakukan persetujuan atau memberikan tambahan substansi yang diperlukan
            </div>
            @endif

            <!-- Berita Acara Asesmen Lapangan (Read Only) -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Berita Acara Asesmen Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $beritaAcaraList = $pengajuan->asesmen->beritaAcaraAL ?? collect([]);
                    @endphp

                    @if($beritaAcaraList->count() > 0)
                    <div class="alert alert-light alert-permanent border mb-3">
                        <i class="bi bi-info-circle text-secondary"></i>
                        Berikut adalah berita acara pelaksanaan asesmen lapangan.
                    </div>

                    @foreach($beritaAcaraList as $index => $beritaAcara)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 40px;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <strong>{{ $beritaAcara->title }}</strong>
                                    </h6>
                                    <small class="text-muted">
                                        Diupload: {{ $beritaAcara->uploaded_at ? $beritaAcara->uploaded_at->format('d M Y H:i') : '-' }}
                                    </small>
                                </div>
                                <div>
                                    <div class="btn-group-vertical" role="group">
                                        <a href="{{ route('al.berkas.documents.preview', ['id' => $pengajuan->asesmen->id, 'docId' => $beritaAcara->id]) }}" class="btn btn-outline-success" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                        </a>
                                        <a href="{{ route('al.berkas.documents.download', ['id' => $pengajuan->asesmen->id, 'docId' => $beritaAcara->id]) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada berita acara yang diupload</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Laporan Hasil Asesmen (LHA) - Dengan Approval -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check"></i> Laporan Hasil Asesmen Lapangan (LHA)
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $lhaList = $pengajuan->asesmen->documents()
                    ->where('type', 'lha_asesor')
                    ->where('is_active', true)
                    ->latest('uploaded_at')
                    ->get();
                    @endphp

                    @if($lhaList->count() > 0)
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-info-circle"></i>
                        Berikut adalah laporan hasil asesmen lapangan yang telah diupload oleh asesor. Mohon lakukan peninjauan dan berikan persetujuan.
                    </div>

                    @foreach($lhaList as $index => $lha)
                    <div class="card berita-acara-card mb-3 border-{{
                        $lha->status_persetujuan_prodi === 'approved' ? 'outline-success' :
                        ($lha->status_persetujuan_prodi === 'rejected' ? 'outline-danger' :
                        ($lha->status_persetujuan_prodi === 'revision_required' ? 'warning' : 'secondary'))
                    }}">
                        <div class="card-body">
                            <!-- Header -->
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 40px;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <strong>{{ $lha->title }}</strong>
                                    </h6>
                                    <small class="text-muted">
                                        Diupload: {{ $lha->uploaded_at ? $lha->uploaded_at->format('d M Y H:i') : '-' }}
                                    </small>
                                    <br>
                                    Status: <span class="badge {{ $lha->status_prodi_badge_class }} mt-1">
                                        {{ $lha->status_prodi_label }}
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('al.berkas.documents.preview', ['id' => $pengajuan->asesmen->id, 'docId' => $lha->id]) }}" class="btn btn-success" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                    </a>
                                </div>
                            </div>

                            <!-- Catatan Sebelumnya (jika ada) -->
                            @if($lha->catatan_prodi)
                            <div class="alert alert-light alert-permanent border mb-3">
                                <strong><i class="bi bi-chat-left-text"></i> Catatan Program Studi:</strong><br>
                                {{ $lha->catatan_prodi }}
                                @if($lha->approved_at_prodi)
                                <br><small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $lha->approved_at_prodi->format('d M Y H:i') }}
                                </small>
                                @endif
                            </div>
                            @endif

                            <!-- Approval Section (hanya jika bisa direvisi) -->
                            @if($lha->canBeRevised())
                            <div class="approval-section">
                                <form action="{{ route('upps.pelaksanaan-al.lha.approve.process', ['id' => $pengajuan->id, 'docId' => $lha->id]) }}" method="POST" class="approval-form" id="approvalForm{{ $lha->id }}">
                                    @csrf

                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-hand-thumbs-up"></i>
                                        {{ $lha->status_persetujuan_prodi === 'revision_required' ? 'Tinjau Ulang Laporan' : 'Tinjau & Berikan Persetujuan' }}
                                    </h6>

                                    <!-- Action Selection -->
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check action-radio p-3 border rounded">
                                                <input class="form-check-input action-input" type="radio" name="action" id="approve{{ $lha->id }}" value="approve" data-form-id="{{ $lha->id }}" required>
                                                <label class="form-check-label w-100" for="approve{{ $lha->id }}">
                                                    <i class="bi bi-check-circle text-success"></i>
                                                    <strong>Setujui</strong>
                                                    <br><small class="text-muted">Laporan sudah sesuai</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check action-radio p-3 border rounded">
                                                <input class="form-check-input action-input" type="radio" name="action" id="revision{{ $lha->id }}" value="revision" data-form-id="{{ $lha->id }}" required>
                                                <label class="form-check-label w-100" for="revision{{ $lha->id }}">
                                                    <i class="bi bi-arrow-repeat text-warning"></i>
                                                    <strong>Permintaan Revisi</strong>
                                                    <br><small class="text-muted">Perlu perbaikan</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Catatan -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-chat-left-text"></i> Catatan
                                        </label>
                                        <textarea name="catatan_prodi" id="catatan{{ $lha->id }}" class="form-control catatan-textarea" rows="4" placeholder="Pilih tindakan di atas untuk mengisi catatan otomatis, atau tulis catatan Anda sendiri"></textarea>
                                        <small class="text-muted">
                                            Catatan akan terlihat oleh asesor
                                        </small>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary btn-md">
                                            <i class="bi bi-send"></i> Kirim Persetujuan
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-x" style="font-size: 64px; color: #ddd;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada laporan hasil asesmen yang diupload</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Pelaksanaan -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaksanaan AL
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Mulai AL</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_mulai
                                    ? $pengajuan->tanggal_al_mulai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal AL Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_al_selesai
                                    ? $pengajuan->tanggal_al_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Pelaksanaan AL & Berita Acara</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaksanaan_al', 'upps', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline Pelaksanaan -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
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
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                                    => 'text-warning',
                                    default => 'text-info',
                                    };
                                    @endphp
                                    <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->changed_at->format('d M Y H:i') }}</small>

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
                    <p class="text-muted text-center mb-0">Belum ada riwayat pelaksanaan</p>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb"></i> Panduan Persetujuan LHA
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Yang Harus Diperhatikan:</h6>
                    <ol class="small mb-3 ps-3">
                        <li>Pastikan dokumen laporan dapat dibuka dengan baik</li>
                        <li>Periksa kelengkapan isi laporan hasil asesmen</li>
                        <li>Verifikasi temuan dan rekomendasi asesor</li>
                        <li>Pastikan data dan fakta sudah akurat</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Status Persetujuan LHA:</strong>
                    </p>
                    <ul class="small mb-3 ps-3">
                        <li><span class="badge bg-secondary">Menunggu</span> - Laporan menunggu peninjauan</li>
                        <li><span class="badge bg-success">Disetujui</span> - Laporan telah disetujui</li>
                        <li><span class="badge bg-warning">Perlu Revisi</span> - Laporan perlu diperbaiki</li>
                    </ul>

                    <hr>

                    <p class="small mb-2">
                        <strong>Catatan Penting:</strong>
                    </p>
                    <p class="small text-muted mb-0">
                        Laporan hasil asesmen yang sudah disetujui tidak dapat diubah lagi.
                        Jika laporan memerlukan revisi, asesor akan melakukan perbaikan dan mengirim ulang dokumen yang telah diperbaiki.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const prodiName = '{{ $pengajuan->studyProgram->name ?? "" }} '
        // Template catatan
        const catatanTemplates = {
            approve: `Program Studi ${prodiName}menyatakan menyetujui laporan hasil akreditasi (LHA)`
            , revision: "Program Studi meminta revisi pada Laporan Hasil Asesmen Lapangan dengan catatan sebagai berikut:\n\n[Jelaskan bagian yang perlu diperbaiki]"
        };

        // Handle action change
        $('.action-input').on('change', function() {
            const formId = $(this).data('form-id');
            const action = $(this).val();
            const catatanField = $(`#catatan${formId}`);

            if (action === 'approve') {
                catatanField.val(catatanTemplates.approve);
            } else if (action === 'revision') {
                catatanField.val(catatanTemplates.revision);
                // Set cursor position after the template
                setTimeout(() => {
                    catatanField.focus();
                    const val = catatanField.val();
                    catatanField[0].setSelectionRange(val.length, val.length);
                }, 100);
            }
        });

        // Handle form submit
        $('.approval-form').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const action = form.find('input[name="action"]:checked').val();
            const catatan = form.find('textarea[name="catatan_prodi"]').val().trim();

            // Validation
            if (!action) {
                alert('Silakan pilih tindakan terlebih dahulu (Setujui atau Minta Revisi)');
                return false;
            }

            if (action === 'revision' && catatan === catatanTemplates.revision.trim()) {
                alert('Harap lengkapi catatan revisi dengan penjelasan yang spesifik');
                form.find('textarea[name="catatan_prodi"]').focus();
                return false;
            }

            // Confirmation
            let confirmMsg = '';
            if (action === 'approve') {
                confirmMsg = 'Apakah Anda yakin ingin menyetujui laporan hasil asesmen ini?\n\nSetelah disetujui, asesor akan mendapat notifikasi dan status akan berubah.';
            } else if (action === 'revision') {
                confirmMsg = 'Apakah Anda yakin ingin meminta revisi?\n\nAsesor akan diminta melakukan perbaikan sesuai catatan yang Anda berikan.';
            }

            if (!confirm(confirmMsg)) {
                return false;
            }

            // Disable button to prevent double submit
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

            // Submit form
            form[0].submit();
        });
    });

</script>
@endpush
