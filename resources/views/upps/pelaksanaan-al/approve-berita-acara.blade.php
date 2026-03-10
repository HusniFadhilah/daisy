{{-- resources/views/upps/pelaksanaan-al/approve-berita-acara.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Approval Berita Acara AL')

@push('styles')
<style>
    .approval-card {
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .approval-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .document-preview {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        background: #f8f9fa;
        margin-bottom: 1rem;
    }

    .action-buttons {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 1.5rem;
        border-top: 2px solid #e9ecef;
        margin: 0 -1.5rem -1.5rem;
        border-radius: 0 0 12px 12px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-al') }}">Pelaksanaan AL</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pelaksanaan-al.show', $pengajuan->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Approval Berita Acara</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Approval Berita Acara AL
            </h4>
            <p class="text-muted mb-0">Review dan setujui Berita Acara Asesmen Lapangan</p>
        </div>
        <a href="{{ route('upps.pelaksanaan-al.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info border-start border-2 border-info mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle-fill fs-1 me-3 text-info"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-clipboard-check"></i> Review Berita Acara
                </h5>
                <p class="mb-0">
                    Silakan review Berita Acara yang telah diupload oleh asesor.
                    Jika sudah sesuai, klik <strong>"Setujui Berita Acara"</strong>.
                    Jika ada yang perlu diperbaiki, klik <strong>"Tolak & Minta Revisi"</strong> dan berikan catatan revisi.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Informasi Pelaksanaan AL -->
            <div class="card approval-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pelaksanaan AL
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Nomor Pengajuan</th>
                            <td>: {{ $pengajuan->nomor_pengajuan }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi AL</th>
                            <td>: {{ $al->lokasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pelaksanaan</th>
                            <td>
                                : {{ $al->tanggal_mulai ? $al->tanggal_mulai->locale('id')->translatedFormat('d M Y') : '-' }}
                                @if($al->tanggal_selesai)
                                s/d {{ $al->tanggal_selesai->locale('id')->translatedFormat('d M Y') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status AL</th>
                            <td>
                                : <span class="badge {{ $al->status === 'completed' ? 'bg-success' : 'bg-info' }}">
                                    {{ ucfirst($al->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Berita Acara Documents -->
            <div class="card approval-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Berita Acara Asesmen Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    @if($beritaAcara->count() > 0)
                    <p class="text-muted mb-3">
                        Total {{ $beritaAcara->count() }} dokumen Berita Acara telah diupload.
                    </p>

                    @foreach($beritaAcara as $index => $doc)
                    <div class="document-preview">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 48px;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    <strong>{{ $doc->title ?? 'Berita Acara ' . ($index + 1) }}</strong>
                                </h6>
                                <p class="text-muted mb-2">
                                    <small>
                                        File: {{ $doc->original_name }}<br>
                                        Ukuran: {{ number_format($doc->size / 1024, 2) }} KB<br>
                                        Diupload: {{ $doc->uploaded_at ? $doc->uploaded_at->locale('id')->translatedFormat('d M Y H:i') : '-' }}
                                    </small>
                                </p>
                                <a href="{{ route('asesmen.document.download', $doc->id) }}" class="btn btn-success btn-md" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat & Download File
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada Berita Acara yang diupload</p>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons (Sticky Bottom) -->
                @if($beritaAcara->count() > 0 && $al->status !== 'completed')
                <div class="action-buttons">
                    <form action="{{ route('upps.pelaksanaan-al.berita-acara.approve.process', $pengajuan->id) }}" method="POST" id="approvalForm">
                        @csrf

                        <!-- Catatan (Optional) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-chat-left-text"></i> Catatan (Optional)
                            </label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            <small class="text-muted">
                                Catatan ini akan dilihat oleh asesor dan tersimpan dalam riwayat.
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-danger btn-md" onclick="submitAction('reject')">
                                <i class="bi bi-x-circle"></i> Tolak & Minta Revisi
                            </button>
                            <button type="button" class="btn btn-success btn-md" onclick="confirmApproval()">
                                <i class="bi bi-check-circle"></i> Setujui Berita Acara
                            </button>
                        </div>

                        <input type="hidden" name="action" id="actionInput">
                    </form>
                </div>
                @elseif($al->status === 'completed')
                <div class="card-footer bg-light">
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>Berita Acara telah disetujui.</strong> Pelaksanaan AL sudah selesai.
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-info mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Status Approval
                    </h6>
                </div>
                <div class="card-body">
                    @if($al->status === 'completed')
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 64px;"></i>
                        <h5 class="mt-3 text-success">Sudah Disetujui</h5>
                        <p class="text-muted mb-0">
                            Berita Acara telah disetujui pada:<br>
                            <strong>{{ $al->completed_at ? $al->completed_at->locale('id')->translatedFormat('d M Y H:i') : '-' }}</strong>
                        </p>
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 64px;"></i>
                        <h5 class="mt-3 text-warning">Menunggu Approval</h5>
                        <p class="text-muted mb-0">Berita Acara perlu direview dan disetujui oleh Program Studi</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Info Panduan -->
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb"></i> Panduan Approval
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Yang Harus Diperhatikan:</h6>
                    <ol class="small mb-3 ps-3">
                        <li>Pastikan semua dokumen Berita Acara dapat dibuka</li>
                        <li>Periksa kelengkapan konten Berita Acara</li>
                        <li>Verifikasi tanda tangan asesor dan pihak terkait</li>
                        <li>Pastikan tanggal dan lokasi sudah sesuai</li>
                    </ol>

                    <hr>

                    <h6 class="fw-bold mb-2">Langkah Selanjutnya:</h6>
                    <p class="small mb-0">
                        Setelah Berita Acara disetujui, status akan berubah menjadi
                        <strong>"AL Selesai"</strong> dan proses akan dilanjutkan ke
                        <strong>Pelaporan AL</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function confirmApproval() {
        if (await swalConfirmSubmit('warning', 'Apakah Anda yakin ingin menyetujui Berita Acara ini?<br><br>Setelah disetujui, Pelaksanaan AL akan dinyatakan selesai.')) {
            submitAction('approve');
        }
    }

    function submitAction(action) {
        document.getElementById('actionInput').value = action;
        document.getElementById('approvalForm').submit();
    }

</script>
@endpush
