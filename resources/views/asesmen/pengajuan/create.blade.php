{{-- resources/views/asesmen/pengajuan/create.blade.php --}}

@extends('layouts.template.app')

@section('title', isset($pengingat) ? 'Respon Pengingat Akreditasi' : 'Permohonan Akreditasi Baru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="mb-4">
        <h2>
            <i class="bi bi-file-earmark-plus"></i>
            {{ isset($pengingat) ? 'Respon Pengingat Akreditasi' : 'Permohonan Akreditasi Baru' }}
        </h2>
        <p class="text-muted">
            {{ isset($pengingat) ? 'Lengkapi formulir untuk merespon pengingat akreditasi dari DE' : 'Lengkapi formulir di bawah untuk mengajukan permohonan akreditasi' }}
        </p>
    </div>

    {{-- Alert info untuk pengingat --}}
    @if(isset($pengingat))
    <div class="alert alert-info alert-dismissible alert-permanent fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">
                    <strong>Informasi Pengingat Akreditasi</strong>
                </h6>
                <p class="mb-2">
                    Pengingat akreditasi telah dikirim oleh
                    <strong>{{ $pengingat->pengirim->name }}</strong> (Dewan Eksekutif)
                    pada <strong>{{ $pengingat->tanggal_dikirim->format('d F Y, H:i') }} WIB</strong>
                </p>

                <div class="bg-white p-3 rounded border mb-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="30%" class="text-muted">Program Studi</td>
                            <td><strong>{{ $pengingat->studyProgram->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tahun Akreditasi</td>
                            <td><strong>{{ $pengingat->tahun_akreditasi }}</strong></td>
                        </tr>
                    </table>
                </div>

                @if($pengingat->pesan_pengingat)
                <div class="bg-light p-2 rounded border-start border-primary border-3">
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-chat-left-quote"></i> Pesan Pengingat:
                    </small>
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $pengingat->pesan_pengingat }}</p>
                </div>
                @endif
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i>
                        Formulir Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($pengingat) ? route('pengajuan.respond-pengingat', $pengingat->id) : route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" id="formPengajuan">
                        @csrf

                        {{-- Hidden field untuk id_pengingat jika ada --}}
                        @if(isset($pengingat))
                        <input type="hidden" name="id_pengingat" value="{{ $pengingat->id }}">
                        @endif

                        <!-- Program Studi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Program Studi <span class="text-danger">*</span>
                            </label>
                            <select name="id_program_studi" class="form-select @error('id_program_studi') is-invalid @enderror" {{ isset($pengingat) ? 'disabled' : '' }} required>
                                <option value="">-- Pilih Program Studi --</option>
                                @if($prodiUser)
                                <option value="{{ $prodiUser->id }}" selected>{{ $prodiUser->full_name }}</option>
                                @else
                                @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ (isset($pengingat) && $pengingat->id_program_studi == $prodi->id) ||
                                           (request('study_program_id') == $prodi->id) ||
                                           (old('id_program_studi') == $prodi->id) ? 'selected' : '' }}>
                                    {{ $prodi->full_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>

                            {{-- Hidden input jika disabled untuk tetap submit value --}}
                            @if(isset($pengingat))
                            <input type="hidden" name="id_program_studi" value="{{ $pengingat->id_program_studi }}">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Program studi dipilih otomatis dari pengingat yang dikirim DE.
                            </small>
                            @endif

                            @error('id_program_studi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tahun Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Tahun Akreditasi <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ isset($pengingat) ? $pengingat->tahun_akreditasi : old('tahun_akreditasi', date('Y')) }}" min="2024" max="{{ date('Y') + 2 }}" required>
                            @error('tahun_akreditasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(isset($pengingat))
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Tahun diisi otomatis dari pengingat. Anda dapat mengubahnya jika diperlukan.
                            </small>
                            @endif
                        </div>

                        <!-- Jenis Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Jenis Akreditasi <span class="text-danger">*</span>
                            </label>

                            <select name="jenis_akreditasi" class="form-select @error('jenis_akreditasi') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis --</option>

                                @foreach (\App\Models\PengajuanAkreditasi::jenisAkreditasiOptions() as $value => $label)
                                <option value="{{ $value }}" {{ old('jenis_akreditasi') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>

                            @error('jenis_akreditasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Surat Permohonan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Surat Permohonan (PDF) <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf" required id="fileSuratPermohonan">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Format: PDF | Maksimal: 5 MB
                            </small>
                            <div id="filePreview" class="mt-2"></div>
                            @error('file_surat_permohonan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Persyaratan Info -->
                        <div class="alert alert-light alert-permanent border">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-clipboard-check"></i> Persyaratan Dokumen
                            </h6>
                            <ul class="mb-0 ps-3">
                                <li>Surat permohonan resmi dalam format PDF</li>
                                <li>Menggunakan kop surat program studi/universitas</li>
                                <li>Ditandatangani oleh pejabat berwenang (Ketua Program Studi/Dekan)</li>
                                <li>Mencantumkan tujuan akreditasi yang jelas</li>
                            </ul>
                        </div>

                        <!-- Catatan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Catatan/Keterangan Tambahan (Opsional)
                            </label>
                            <textarea name="catatan_pengaju" class="form-control @error('catatan_pengaju') is-invalid @enderror" rows="4" placeholder="Masukkan catatan atau keterangan tambahan jika ada...">{{ old('catatan_pengaju') }}</textarea>
                            @error('catatan_pengaju')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warning Alert -->
                        <div class="alert alert-warning alert-permanent">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong> Pastikan semua data yang diisi sudah benar.
                            @if(isset($pengingat))
                            Setelah submit, pengingat akan ditandai sebagai sudah direspon.
                            @else
                            Data yang sudah disubmit tidak dapat diubah kecuali dengan persetujuan DE.
                            @endif
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pengajuan') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bi bi-send"></i>
                                {{ isset($pengingat) ? 'Kirim Permohonan' : 'Submit Permohonan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            {{-- Info Card untuk Pengingat --}}
            @if(isset($pengingat))
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-bell"></i> Detail Pengingat
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%" class="text-muted">Dikirim Oleh</td>
                            <td><strong>{{ $pengingat->pengirim->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Kirim</td>
                            <td><strong>{{ $pengingat->tanggal_dikirim->format('d M Y') }}</strong></td>
                        </tr>
                        {{-- <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge bg-{{ $pengingat->status_badge_class }}">
                        {{ $pengingat->status_label }}
                        </span>
                        </td>
                        </tr>
                        @if($pengingat->email_terkirim_ke)
                        <tr>
                            <td class="text-muted">Email Terkirim</td>
                            <td>
                                <small>{{ $pengingat->jumlah_email_terkirim }} penerima</small>
                            </td>
                        </tr>
                        @endif --}}
                    </table>
                </div>
            </div>
            @endif

            <!-- Timeline Card dengan 20 Step -->
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Alur Permohonan Akreditasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Timeline Proses (20 Tahap)</h6>
                    <div class="timeline">
                        @php
                        $timelineItems = [
                        // ========================================
                        // FASE 1: PERSIAPAN
                        // ========================================
                        [
                        'date' => null,
                        'label' => 'Pengingat Masa Akreditasi',
                        'icon' => 'bi-bell',
                        'step' => 1,
                        'current' => isset($pengingat)
                        ],
                        [
                        'date' => null,
                        'label' => 'Surat Permohonan dari PS',
                        'icon' => 'bi-envelope',
                        'step' => 2,
                        'current' => !isset($pengingat)
                        ],
                        [
                        'date' => null,
                        'label' => 'Penyampaian Template Dokumen',
                        'icon' => 'bi-file-earmark-arrow-down',
                        'step' => 3
                        ],
                        [
                        'date' => null,
                        'label' => 'Validasi Pembayaran',
                        'icon' => 'bi-credit-card-2-front',
                        'step' => 4
                        ],
                        [
                        'date' => null,
                        'label' => 'Penerimaan Draft Dokumen',
                        'icon' => 'bi-file-earmark-check',
                        'step' => 5
                        ],

                        // ========================================
                        // FASE 2: VALIDASI LED
                        // ========================================
                        [
                        'date' => null,
                        'label' => 'Validasi Dokumen',
                        'icon' => 'bi-clipboard-check',
                        'step' => 6,
                        'color' => 'primary'
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaporan Validasi Dokumen',
                        'icon' => 'bi-file-earmark-text',
                        'step' => 7,
                        'color' => 'primary'
                        ],

                        // ========================================
                        // FASE 3: ASESMEN KECUKUPAN (AK)
                        // ========================================
                        [
                        'date' => null,
                        'label' => 'Penugasan Asesor untuk AK',
                        'icon' => 'bi-person-check',
                        'step' => 8,
                        'color' => 'success'
                        ],
                        [
                        'date' => null,
                        'label' => 'Validasi AK',
                        'icon' => 'bi-clipboard2-check',
                        'step' => 9,
                        'color' => 'success'
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaporan AK',
                        'icon' => 'bi-file-earmark-medical',
                        'step' => 10,
                        'color' => 'success'
                        ],

                        // ========================================
                        // FASE 4: ASESMEN LAPANGAN (AL)
                        // ========================================
                        [
                        'date' => null,
                        'label' => 'Penugasan Asesor untuk AL',
                        'icon' => 'bi-person-badge',
                        'step' => 11,
                        'color' => 'info'
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaksanaan AL & Berita Acara',
                        'icon' => 'bi-building',
                        'step' => 12,
                        'color' => 'info'
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaporan AL',
                        'icon' => 'bi-clipboard-data',
                        'step' => 13,
                        'color' => 'info'
                        ],

                        // ========================================
                        // FASE 5: PENYELESAIAN
                        // ========================================
                        [
                        'date' => null,
                        'label' => 'Penyampaian Hasil Akreditasi',
                        'icon' => 'bi-envelope-paper',
                        'step' => 14,
                        'color' => 'warning'
                        ],
                        [
                        'date' => null,
                        'label' => 'Masa Sanggah',
                        'icon' => 'bi-clock-history',
                        'step' => 15,
                        'color' => 'warning'
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaksanaan Banding',
                        'icon' => 'bi-arrow-repeat',
                        'step' => 16,
                        'color' => 'danger',
                        'optional' => true
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaporan Banding',
                        'icon' => 'bi-file-earmark-ruled',
                        'step' => 17,
                        'color' => 'danger',
                        'optional' => true
                        ],
                        [
                        'date' => null,
                        'label' => 'Penetapan Hasil Akreditasi',
                        'icon' => 'bi-award',
                        'step' => 18,
                        'color' => 'success'
                        ],
                        [
                        'date' => null,
                        'label' => 'Pelaporan Hasil Akreditasi',
                        'icon' => 'bi-megaphone',
                        'step' => 19,
                        'color' => 'success'
                        ],
                        [
                        'date' => null,
                        'label' => 'Penyimpanan Arsip Pelaksanaan Akreditasi',
                        'icon' => 'bi-archive',
                        'step' => 20,
                        'color' => 'secondary'
                        ],
                        ];
                        @endphp

                        @foreach($timelineItems as $item)
                        @php
                        $isCompleted = !is_null($item['date']);
                        $isCurrent = $item['current'] ?? false;
                        $iconColor = $isCompleted ? 'text-success' : ($isCurrent ? 'text-primary' : 'text-muted');
                        $itemColor = $item['color'] ?? ($isCompleted ? 'success' : 'muted');
                        $isOptional = $item['optional'] ?? false;
                        @endphp

                        <div class="d-flex mb-3 {{ $isOptional && !$isCompleted ? 'opacity-50' : '' }}">
                            <div class="me-3">
                                @if($isCompleted)
                                <i class="bi bi-check-circle-fill {{ $iconColor }}" style="font-size: 1.2rem;"></i>
                                @elseif($isCurrent)
                                <i class="bi bi-arrow-right-circle-fill {{ $iconColor }}" style="font-size: 1.2rem;"></i>
                                @else
                                <i class="bi bi-circle {{ $iconColor }}"></i>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="{{ $isCompleted ? 'text-' . $itemColor : ($isCurrent ? 'text-primary fw-bold' : 'text-muted') }}">
                                            <i class="{{ $item['icon'] }} me-1"></i>
                                            {{ $item['step'] }}. {{ $item['label'] }}
                                            @if($isOptional)
                                            <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Opsional</span>
                                            @endif
                                        </strong>
                                    </div>

                                    @if($isCompleted)
                                    <span class="badge bg-{{ $itemColor }}">
                                        {{ $item['date']->format('d M Y') }}
                                    </span>
                                    @elseif($isCurrent)
                                    <span class="badge bg-primary">
                                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Saat ini
                                    </span>
                                    @endif
                                </div>

                                @if($isCompleted)
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $item['date']->format('H:i') }} WIB
                                </small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <!-- Fase Summary -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <small class="text-muted d-block">Persiapan</small>
                                <strong class="text-secondary">Step 1-5</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <small class="text-muted d-block">Validasi</small>
                                <strong class="text-primary">Step 6-7</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <small class="text-muted d-block">AK</small>
                                <strong class="text-success">Step 8-10</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <small class="text-muted d-block">AL</small>
                                <strong class="text-info">Step 11-13</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-center p-2 bg-light rounded">
                                <small class="text-muted d-block">Penyelesaian</small>
                                <strong class="text-warning">Step 14-20</strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-permanent mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong> Pastikan semua data yang diisi sudah benar sebelum submit permohonan.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        var fileInput = document.getElementById('fileSuratPermohonan');
        var preview = document.getElementById('filePreview');
        var form = document.getElementById('formPengajuan');
        var btnSubmit = document.getElementById('btnSubmit');

        // ===============================
        // File upload preview
        // ===============================
        if (fileInput && preview) {
            fileInput.addEventListener('change', function(e) {

                if (!e || !e.target || !e.target.files || !e.target.files.length) {
                    preview.innerHTML = '';
                    return;
                }

                var file = e.target.files[0];
                var fileSize = file.size / 1024 / 1024; // number (MB)
                var fileName = file.name;

                if (file.type !== 'application/pdf') {
                    preview.innerHTML =
                        '<div class="alert alert-danger alert-dismissible alert-permanent fade show">' +
                        '<i class="bi bi-x-circle"></i> File harus berformat PDF' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>';
                    fileInput.value = '';
                    return;
                }

                if (fileSize > 5) {
                    preview.innerHTML =
                        '<div class="alert alert-danger alert-dismissible alert-permanent fade show">' +
                        '<i class="bi bi-x-circle"></i> Ukuran file terlalu besar (' + fileSize.toFixed(2) + ' MB). Maksimal 5 MB' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>';
                    fileInput.value = '';
                    return;
                }

                preview.innerHTML =
                    '<div class="alert alert-success alert-dismissible alert-permanent fade show">' +
                    '<i class="bi bi-check-circle"></i> ' +
                    '<strong>' + fileName + '</strong> (' + fileSize.toFixed(2) + ' MB)' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
            });
        }

        // ===============================
        // Form confirmation
        // ===============================
        if (form) {
            form.addEventListener('submit', function(e) {
                var isFromPengingat = {
                    {
                        isset($pengingat) ? 'true' : 'false'
                    }
                };
                var confirmText = isFromPengingat ?
                    'Apakah Anda yakin akan mengirim permohonan akreditasi sebagai respon pengingat ini?' :
                    'Apakah Anda yakin data yang diisi sudah benar dan siap untuk disubmit?';

                if (!confirm(confirmText)) {
                    e.preventDefault();
                }
            });
        }

        // ===============================
        // Disable button after submit
        // ===============================
        if (btnSubmit && form) {
            btnSubmit.addEventListener('click', function() {
                if (form.checkValidity()) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
                }
            });
        }

    });

</script>
@endpush
