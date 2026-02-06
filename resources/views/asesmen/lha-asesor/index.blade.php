{{-- resources/views/asesmen/lha-asesor/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Laporan Hasil Asesmen (LHA)')

@push('styles')
<style>
    .section-card {
        transition: all 0.3s ease;
    }

    .section-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .progress-bar-custom {
        height: 25px;
        font-size: 14px;
        font-weight: bold;
    }

    .auto-save-indicator {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
        display: none;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Auto Save Indicator -->
    <div class="auto-save-indicator">
        <div class="alert alert-success alert-permanent mb-0">
            <i class="bi bi-check-circle"></i> Tersimpan otomatis
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('al.berkas', $asesmen->id) }}">Berkas AL</a></li>
            <li class="breadcrumb-item active">LHA Asesor</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Pengisian Laporan Hasil Asesmen Lapangan (LHA)
            </h5>
            <small class="text-muted">{{ $asesmen->pengajuan->nomor_pengajuan ?? $asesmen->code }}</small>
        </div>
        <a href="{{ route('al.berkas', $asesmen->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @if($lha->isFinalized())
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Pengisian Laporan Hasil Asesmen Lapangan (LHA)</strong><br>
                LHA telah difinalisasi pada {{ $lha->finalized_at->format('d M Y H:i') }}.
                Dokumen telah dikirim ke Program Studi untuk peninjauan.
            </div>
            @elseif($lha->isDraft())
            <div class="alert alert-info alert-permanent mb-4">
                <i class="bi bi-info-circle"></i>
                <strong>Pengisian Laporan Hasil Asesmen Lapangan (LHA)</strong><br>
                Anda dapat melakukan pengisian LHA dan menyimpan draftnya. Kemudian, mohon segera lakukan finalisasi LHA setelah semua bagian terisi lengkap.
            </div>
            @endif

            <!-- Progress -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="bi bi-graph-up"></i> Progress Pengisian LHA
                    </h6>
                    <div class="progress progress-bar-custom">
                        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: {{ $lha->getCompletionPercentage() }}%">
                            {{ $lha->getCompletionPercentage() }}%
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Pastikan semua bagian terisi sebelum finalisasi
                    </small>
                </div>
            </div>

            <!-- Form -->
            <form id="lhaForm">
                @csrf

                <!-- 1. Pendahuluan -->
                <div class="card section-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-1-circle"></i> Pendahuluan
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Berikan pengantar/pendahuluan mengenai pelaksanaan AL
                        </p>
                        <textarea name="pendahuluan" class="form-control lha-field" rows="6" placeholder="Tuliskan pendahuluan mengenai pelaksanaan asesmen lapangan..." {{ $lha->isFinalized() ? 'readonly' : '' }}>{{ old('pendahuluan', $lha->pendahuluan) }}</textarea>
                    </div>
                </div>

                <!-- 2. Proses AL -->
                <div class="card section-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-2-circle"></i> Proses AL
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Mohon berikan penjelasan mengenai proses AL yang dilaksanakan
                        </p>
                        <textarea name="proses_al" class="form-control lha-field" rows="8" placeholder="Jelaskan proses pelaksanaan asesmen lapangan secara detail..." {{ $lha->isFinalized() ? 'readonly' : '' }}>{{ old('proses_al', $lha->proses_al) }}</textarea>
                    </div>
                </div>

                <!-- 3. Hasil AL -->
                <div class="card section-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-3-circle"></i> Hasil AL
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Berikan penjelasan mengenai hasil akreditasi yang dilaksanakan
                        </p>
                        <textarea name="hasil_al" class="form-control lha-field" rows="10" placeholder="Tuliskan hasil dan temuan dari asesmen lapangan..." {{ $lha->isFinalized() ? 'readonly' : '' }}>{{ old('hasil_al', $lha->hasil_al) }}</textarea>
                    </div>
                </div>

                <!-- 4. Rekomendasi PS -->
                <div class="card section-card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-4-circle"></i> Rekomendasi untuk Program Studi
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Berikan rekomendasi untuk program studi
                        </p>
                        <textarea name="rekomendasi_ps" class="form-control lha-field" rows="8" placeholder="Tuliskan rekomendasi untuk perbaikan dan pengembangan program studi..." {{ $lha->isFinalized() ? 'readonly' : '' }}>{{ old('rekomendasi_ps', $lha->rekomendasi_ps) }}</textarea>
                    </div>
                </div>

                <!-- 5. Rekomendasi LAMDEPILAR -->
                <div class="card section-card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-5-circle"></i> Rekomendasi untuk LAMDEPILAR
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Berikan rekomendasi untuk LAMDEPILAR
                        </p>
                        <textarea name="rekomendasi_lamdepilar" class="form-control lha-field" rows="6" placeholder="Tuliskan rekomendasi untuk LAMDEPILAR..." {{ $lha->isFinalized() ? 'readonly' : '' }}>{{ old('rekomendasi_lamdepilar', $lha->rekomendasi_lamdepilar) }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                @if(!$lha->isFinalized())
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-save"></i> Simpan & Finalisasi
                                </h6>
                                <small class="text-muted">
                                    Pastikan semua bagian telah terisi dengan lengkap
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info" onclick="previewPDF()">
                                    <i class="bi bi-eye"></i> Preview PDF
                                </button>
                                <button type="button" class="btn btn-success" onclick="finalizeLHA()" id="btnFinalize">
                                    <i class="bi bi-check-circle"></i> Finalisasi LHA
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-success">
                                    <i class="bi bi-check-circle-fill"></i> LHA Telah Difinalisasi
                                </h6>
                                <small class="text-muted">
                                    Dokumen telah dikirim ke Program Studi
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info" onclick="previewPDF()">
                                    <i class="bi bi-eye"></i> Preview PDF
                                </button>
                                <a href="{{ route('al.berkas.lha-asesor.download', $asesmen->id) }}" class="btn btn-success">
                                    <i class="bi bi-download"></i> Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Asesmen -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Akreditasi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="40%">Program Studi</th>
                            <td>: {{ $asesmen->pengajuan->studyProgram->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $asesmen->pengajuan->studyProgram->university->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Panduan -->
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb"></i> Panduan Pengisian
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Tips Pengisian:</h6>
                    <ol class="small mb-3 ps-3">
                        <li>Isi setiap bagian dengan lengkap dan detail</li>
                        <li>Perubahan akan disimpan otomatis</li>
                        <li>Preview PDF dapat dilakukan sebelum finalisasi LHA</li>
                        <li>Setelah finalisasi, LHA tidak dapat diubah, kecuali ketika ada permintaan revisi dari prodi</li>
                    </ol>

                    <hr>

                    <p class="small mb-2">
                        <strong>Struktur LHA:</strong>
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Pendahuluan</li>
                        <li>Proses AL</li>
                        <li>Hasil AL</li>
                        <li>Rekomendasi PS</li>
                        <li>Rekomendasi LAMDEPILAR</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
$lhaIsFinalized = $lha->isFinalized();
@endphp
<script>
    let autoSaveTimeout;

    $(document).ready(function() {
        @if(!$lhaIsFinalized)
        // Auto-save on input
        $('.lha-field').on('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                saveLHA();
            }, 2000); // Save after 2 seconds of inactivity
        });
        @endif

        // Update progress bar on load
        updateProgress();
    });

    function saveLHA() {
        const formData = new FormData($('#lhaForm')[0]);

        $.ajax({
            url: '{{ route("al.berkas.lha-asesor.save", $asesmen->id) }}'
            , method: 'POST'
            , data: formData
            , processData: false
            , contentType: false
            , success: function(response) {
                if (response.success) {
                    showAutoSaveIndicator();
                    updateProgress(response.completion);
                }
            }
            , error: function(xhr) {
                console.error('Auto-save failed:', xhr.responseText);
            }
        });
    }

    function showAutoSaveIndicator() {
        $('.auto-save-indicator').fadeIn(300).delay(2000).fadeOut(300);
    }

    function updateProgress(percentage) {
        if (percentage === undefined) {
            // Calculate from filled fields
            let filled = 0;
            const total = 5;

            $('.lha-field').each(function() {
                if ($(this).val().trim() !== '') {
                    filled++;
                }
            });

            percentage = Math.round((filled / total) * 100);
        }

        $('#progressBar').css('width', percentage + '%').text(percentage + '%');

        // Enable/disable finalize button
        if (percentage === 100) {
            $('#btnFinalize').prop('disabled', false);
        } else {
            $('#btnFinalize').prop('disabled', true);
        }
    }

    function previewPDF() {
        window.open('{{ route("al.berkas.lha-asesor.preview", $asesmen->id) }}', '_blank');
    }

    function finalizeLHA() {
        // Calculate completion
        let filled = 0;
        const total = 5;

        $('.lha-field').each(function() {
            if ($(this).val().trim() !== '') {
                filled++;
            }
        });

        if (filled < total) {
            alert('Harap lengkapi semua bagian LHA sebelum finalisasi.\n\nBagian yang telah diisi: ' + filled + ' dari ' + total);
            return;
        }

        if (!confirm('Apakah Anda yakin ingin finalisasi LHA?\n\nSetelah difinalisasi:\n- LHA akan di-generate menjadi PDF\n- Dokumen akan dikirim ke Program Studi\n- LHA tidak dapat diubah lagi')) {
            return;
        }

        // Disable button
        $('#btnFinalize').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

        // Submit
        $.ajax({
            url: '{{ route("al.berkas.lha-asesor.finalize", $asesmen->id) }}'
            , method: 'POST'
            , data: {
                _token: '{{ csrf_token() }}'
            }
            , success: function(response) {
                window.location.reload();
            }
            , error: function(xhr) {
                alert('Gagal finalisasi LHA: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan'));
                $('#btnFinalize').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Finalisasi LHA');
            }
        });
    }

</script>
@endpush
