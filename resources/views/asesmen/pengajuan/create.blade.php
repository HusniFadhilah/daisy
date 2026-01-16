@extends('layouts.template.app')

@section('title', isset($pengajuan) ? 'Lengkapi Pengajuan Akreditasi' : 'Ajukan Akreditasi Baru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="mb-4">
        <h2>
            <i class="bi bi-file-earmark-plus"></i>
            {{ isset($pengajuan) ? 'Lengkapi Pengajuan Akreditasi' : 'Ajukan Akreditasi Baru' }}
        </h2>
        <p class="text-muted">
            {{ isset($pengajuan) ? 'Lengkapi data pengajuan yang telah dibuat oleh DE' : 'Lengkapi formulir di bawah untuk mengajukan permohonan akreditasi' }}
        </p>
    </div>

    @if(isset($pengajuan))
    <div class="alert alert-info alert-permanent">
        <i class="bi bi-info-circle"></i>
        <strong>Informasi:</strong> Pengajuan ini telah dibuat oleh <strong>{{ $pengajuan->deskEvaluator->name }}</strong>@if ($pengajuan->tanggal_pengingat) pada {{ $pengajuan->tanggal_pengingat->format('d F Y') }}@endif. Silakan lengkapi data di bawah untuk melanjutkan proses akreditasi.
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
                    <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ✅ Hidden field untuk pengajuan_id jika update --}}
                        @if(isset($pengajuan))
                        <input type="hidden" name="pengajuan_id" value="{{ $pengajuan->id }}">

                        {{-- Show existing nomor pengajuan --}}
                        <div class="alert alert-secondary alert-permanent">
                            <strong>Nomor Pengajuan:</strong> {{ $pengajuan->nomor_pengajuan }}
                        </div>
                        @endif

                        <!-- Program Studi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Program Studi <span class="text-danger">*</span>
                            </label>
                            <select name="id_program_studi" class="form-select @error('id_program_studi') is-invalid @enderror" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @if($prodiUser)
                                <option value="{{ $prodiUser->id }}" selected>{{ $prodiUser->full_name }}</option>
                                @else
                                @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ ((request('study_program_id') ?? $pengajuan->id_program_studi ?? old('id_program_studi')) == $prodi->id) ? 'selected' : '' }}>
                                    {{ $prodi->full_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                            @error('id_program_studi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(isset($pengajuan))
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Prodi ini dipilih oleh DE. Anda bisa mengubahnya jika tidak sesuai.
                            </small>
                            @endif
                        </div>

                        <!-- Tahun Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Tahun Akreditasi <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ isset($pengajuan) ? $pengajuan->tahun_akreditasi : old('tahun_akreditasi', date('Y')) }}" min="2024" required>
                            @error('tahun_akreditasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jenis Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Jenis Akreditasi <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_akreditasi" class="form-select @error('jenis_akreditasi') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="baru" {{ old('jenis_akreditasi') == 'baru' ? 'selected' : '' }}>
                                    Akreditasi Baru
                                </option>
                                <option value="perpanjangan" {{ old('jenis_akreditasi') == 'perpanjangan' ? 'selected' : '' }}>
                                    Perpanjangan
                                </option>
                                <option value="re-akreditasi" {{ old('jenis_akreditasi') == 're-akreditasi' ? 'selected' : '' }}>
                                    Re-Akreditasi
                                </option>
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
                            <input type="file" name="surat_permohonan" class="form-control @error('surat_permohonan') is-invalid @enderror" accept=".pdf" required>
                            <small class="text-muted">
                                Format: PDF | Maksimal: 5 MB
                            </small>
                            @error('surat_permohonan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <h6 class="fw-bold">Persyaratan Dokumen</h6>
                        <ul class="ps-3">
                            <li>Surat permohonan resmi (PDF)</li>
                            <li>Format surat sesuai prodi</li>
                            <li>Ditandatangani oleh pejabat berwenang</li>
                        </ul>

                        <!-- Catatan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Catatan/Keterangan Tambahan
                            </label>
                            <textarea name="catatan_pengaju" class="form-control @error('catatan_pengaju') is-invalid @enderror" rows="4" placeholder="Masukkan catatan atau keterangan tambahan jika ada...">{{ old('catatan_pengaju') }}</textarea>
                            @error('catatan_pengaju')
                            <span class="invalid-feedback" role="alert">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pengajuan') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                                {{ isset($pengajuan) ? 'Lengkapi & Submit' : 'Submit Permohonan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Alur Pengajuan Akreditasi</h6>
                    <div class="timeline">
                        @php
                        $timelineItems = [
                        // ========================================
                        // FASE 1: PERSIAPAN
                        // ========================================
                        [
                        'date' => optional($pengajuan)->tanggal_pengingat,
                        'label' => 'Pengingat Masa Akreditasi',
                        'icon' => 'bi-bell',
                        'step' => 1
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_surat_permohonan,
                        'label' => 'Surat Permohonan dari PS',
                        'icon' => 'bi-envelope',
                        'step' => 2
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_template_led_dikirim,
                        'label' => 'Penyampaian Template LED+Suplemen dan LKPS, Formulir Pembayaran',
                        'icon' => 'bi-file-earmark-arrow-down',
                        'step' => 3
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pembayaran,
                        'label' => 'Validasi Pembayaran',
                        'icon' => 'bi-credit-card-2-front',
                        'step' => 4
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_draft_borang,
                        'label' => 'Penerimaan draft LED+Suplemen dan LKPS dari Prodi',
                        'icon' => 'bi-file-earmark-check',
                        'step' => 5
                        ],

                        // ========================================
                        // FASE 2: VALIDASI LED
                        // ========================================
                        [
                        'date' => optional($pengajuan)->tanggal_validasi_borang_assigned,
                        'label' => 'Validasi LED+Suplemen dan LKPS',
                        'icon' => 'bi-clipboard-check',
                        'step' => 6,
                        'color' => 'primary'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaporan_validasi_borang,
                        'label' => 'Pelaporan Validasi LED+Suplemen dan LKPS',
                        'icon' => 'bi-file-earmark-text',
                        'step' => 7,
                        'color' => 'primary'
                        ],

                        // ========================================
                        // FASE 3: ASESMEN KECUKUPAN (AK)
                        // ========================================
                        [
                        'date' => optional($pengajuan)->tanggal_penugasan_asesor_ak,
                        'label' => 'Penugasan Asesor untuk AK',
                        'icon' => 'bi-person-check',
                        'step' => 8,
                        'color' => 'success'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_validasi_ak,
                        'label' => 'Validasi AK',
                        'icon' => 'bi-clipboard2-check',
                        'step' => 9,
                        'color' => 'success'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaporan_ak,
                        'label' => 'Pelaporan AK',
                        'icon' => 'bi-file-earmark-medical',
                        'step' => 10,
                        'color' => 'success'
                        ],

                        // ========================================
                        // FASE 4: ASESMEN LAPANGAN (AL)
                        // ========================================
                        [
                        'date' => optional($pengajuan)->tanggal_penugasan_asesor_al,
                        'label' => 'Penugasan Asesor untuk AL',
                        'icon' => 'bi-person-badge',
                        'step' => 11,
                        'color' => 'info'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaksanaan_al,
                        'label' => 'Pelaksanaan AL dan Penyampaian Berita Acara AL',
                        'icon' => 'bi-building',
                        'step' => 12,
                        'color' => 'info'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaporan_al,
                        'label' => 'Pelaporan AL',
                        'icon' => 'bi-clipboard-data',
                        'step' => 13,
                        'color' => 'info'
                        ],

                        // ========================================
                        // FASE 5: PENYELESAIAN
                        // ========================================
                        [
                        'date' => optional($pengajuan)->tanggal_hasil_akreditasi,
                        'label' => 'Penyampaian Hasil Akreditasi',
                        'icon' => 'bi-envelope-paper',
                        'step' => 14,
                        'color' => 'warning'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_masa_sanggah_mulai,
                        'label' => 'Masa Sanggah',
                        'icon' => 'bi-clock-history',
                        'step' => 15,
                        'color' => 'warning'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaksanaan_banding,
                        'label' => 'Pelaksanaan Banding',
                        'icon' => 'bi-arrow-repeat',
                        'step' => 16,
                        'color' => 'danger',
                        'optional' => true
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaporan_banding,
                        'label' => 'Pelaporan Banding',
                        'icon' => 'bi-file-earmark-ruled',
                        'step' => 17,
                        'color' => 'danger',
                        'optional' => true
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_penetapan,
                        'label' => 'Penetapan Hasil Akreditasi',
                        'icon' => 'bi-award',
                        'step' => 18,
                        'color' => 'success'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_pelaporan_hasil,
                        'label' => 'Pelaporan Hasil Akreditasi',
                        'icon' => 'bi-megaphone',
                        'step' => 19,
                        'color' => 'success'
                        ],
                        [
                        'date' => optional($pengajuan)->tanggal_penyimpanan,
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
                        $iconColor = $isCompleted ? 'text-success' : 'text-muted';
                        $itemColor = $item['color'] ?? ($isCompleted ? 'success' : 'muted');
                        $isOptional = $item['optional'] ?? false;
                        @endphp

                        <div class="d-flex mb-3 {{ $isOptional && !$isCompleted ? 'opacity-50' : '' }}">
                            <div class="me-3">
                                @if($isCompleted)
                                <i class="bi bi-check-circle-fill {{ $iconColor }}" style="font-size: 1.2rem;"></i>
                                @else
                                <i class="bi bi-circle {{ $iconColor }}"></i>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="{{ $isCompleted ? 'text-' . $itemColor : 'text-muted' }}">
                                            <i class="{{ $item['icon'] }} me-1"></i>
                                            {{ $item['label'] }}
                                            @if($isOptional)
                                            <span class="badge bg-secondary ms-1">Opsional</span>
                                            @endif
                                        </strong>
                                    </div>

                                    @if($isCompleted)
                                    <span class="badge bg-{{ $itemColor }}">
                                        {{ $item['date']->format('d M Y') }}
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

                    <div class="alert alert-warning alert-permanent mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong> Pastikan semua data yang diisi sudah benar
                            sebelum submit permohonan.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
