{{-- resources/views/asesmen/hasil-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Hasil Akreditasi - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2>
                <i class="bi bi-calculator"></i> Hasil Akreditasi
            </h2>
            <p class="text-muted mb-0">{{ $asesmen->studyProgram->name }}</p>
        </div>

        @if($hasil && $hasil->status === 'published')
        <span class="badge bg-success fs-5">
            {{ $hasil->peringkat_akreditasi }}
        </span>
        @endif
    </div>

    @if($hasil && $hasil->skor_al)
    <div class="row mb-4">
        <div class="col-12">
            @php
            $validationSummary = app(\App\Services\HasilAkreditasiService::class)
            ->getValidationSummary($hasil);
            @endphp

            <div class="card border-{{ $validationSummary['dapat_unggul'] ? 'success' : 'warning' }}">
                <div class="card-header bg-{{ $validationSummary['dapat_unggul'] ? 'success' : 'warning' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check"></i>
                        Validasi Syarat Status Akreditasi Unggul
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <!-- Skor Check -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                @if($validationSummary['skor_memenuhi'])
                                <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                                @else
                                <i class="bi bi-x-circle-fill text-danger fs-3 me-3"></i>
                                @endif
                                <div>
                                    <h6 class="mb-0">Skor >= 361</h6>
                                    <small class="text-muted">
                                        Skor AL: <strong>{{ number_format($hasil->skor_al, 2) }}</strong>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Pelampauan Check -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                @if($validationSummary['pelampauan_memenuhi'])
                                <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                                @else
                                <i class="bi bi-x-circle-fill text-danger fs-3 me-3"></i>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }} (Skor 4)</h6>
                                    <small class="text-muted">
                                        Minimal 1 per kriteria (D, E, P, I, L, A, R)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail per Kriteria -->
                    <hr>
                    <h6 class="fw-bold mb-3">Detail Pelampauan per Kriteria:</h6>
                    <div class="row g-2">
                        @foreach($validationSummary['kriteria_status'] as $kriteria => $status)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-{{ $status['has_pelampauan'] ? 'success' : 'danger' }} h-100">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        @if($status['has_pelampauan'])
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        @else
                                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                        @endif
                                        <div class="flex-grow-1">
                                            <strong>Kriteria {{ $kriteria }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $status['jumlah_elemen_skor_4'] }} elemen dengan skor 4
                                            </small>
                                        </div>

                                        @if($status['has_pelampauan'])
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail-{{ $kriteria }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @endif
                                    </div>

                                    <!-- Collapsible Detail -->
                                    @if($status['has_pelampauan'])
                                    <div class="collapse mt-2" id="detail-{{ $kriteria }}">
                                        <hr class="my-2">
                                        <small>
                                            @foreach($status['elemen_list'] as $elemen)
                                            <div class="mb-1">
                                                <i class="bi bi-star-fill text-warning"></i>
                                                {{ $elemen['kode_elemen'] }} - {{ Str::limit($elemen['nama_elemen'], 50) }}
                                            </div>
                                            @endforeach
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Warning jika tidak memenuhi -->
                    @if(!$validationSummary['dapat_unggul'] && $hasil->skor_al >= 361)
                    <div class="alert alert-warning alert-permanent mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong>
                        Meskipun skor mencapai {{ number_format($hasil->skor_al, 2) }} (>= 361),
                        Status akreditasi akan diturunkan menjadi <strong>Baik Sekali</strong> karena
                        kriteria berikut belum memiliki kategori "{{ \App\Models\JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI }}":
                        <strong>{{ implode(', ', $validationSummary['missing_kriteria']) }}</strong>
                    </div>
                    @endif

                    <!-- Success message -->
                    @if($validationSummary['dapat_unggul'])
                    <div class="alert alert-success alert-permanent mt-3">
                        <i class="bi bi-check-circle"></i>
                        <strong>Selamat!</strong>
                        Semua syarat untuk status akreditasi <strong>Unggul</strong> telah terpenuhi.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- AK Score Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check"></i>
                        Asesmen Kecukupan (AK)
                    </h5>
                </div>
                <div class="card-body">
                    @if($hasil && $hasil->skor_ak)
                    <!-- Score Display -->
                    <div class="text-center mb-4">
                        <h1 class="display-3 fw-bold text-primary">
                            {{ number_format($hasil->skor_ak, 2) }}
                        </h1>
                        <p class="text-muted">Skor Total AK</p>

                        @if($hasil->isAkFinalized())
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> FINAL
                        </span>
                        @else
                        <span class="badge bg-warning">
                            <i class="bi bi-clock"></i> Draft
                        </span>
                        @endif
                    </div>

                    <!-- Detail per Kriteria -->
                    <h6 class="fw-bold mb-3">Detail per Kriteria:</h6>
                    @foreach($hasil->detail_skor_ak['kriteria'] ?? [] as $code => $kriteria)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold">{{ $code }}</small>
                            <small>{{ number_format($kriteria['total_skor'], 2) }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ ($kriteria['total_skor'] / ($kriteria['total_bobot'] * 3)) * 100 }}%">
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Actions -->
                    <div class="mt-4 d-flex gap-2">
                        @if(!$hasil->isAkFinalized())
                        <form action="{{ route('hasil-akreditasi.hitung-ak', $asesmen->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-repeat"></i> Hitung Ulang
                            </button>
                        </form>

                        <form id="form-finalisasi-ak" action="{{ route('hasil-akreditasi.finalize-ak', $asesmen->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-success btn-sm tombol-konfirmasi" data-id-form="form-finalisasi-ak" data-message="Finalisasi hasil AK">
                                <i class="bi bi-lock"></i> Finalisasi AK
                            </button>
                        </form>
                        @else
                        <button class="btn btn-outline-success btn-sm" disabled>
                            <i class="bi bi-check-circle"></i> AK Sudah Final
                        </button>
                        @endif
                    </div>
                    @else
                    <!-- No calculation yet -->
                    <div class="text-center py-5">
                        <i class="bi bi-calculator" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada perhitungan AK</p>

                        <form action="{{ route('hasil-akreditasi.hitung-ak', $asesmen->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-play-circle"></i> Hitung Skor AK
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- AL Score Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building"></i>
                        Asesmen Lapangan (AL)
                    </h5>
                </div>
                <div class="card-body">
                    @if($hasil && $hasil->isAkFinalized())
                    @if($hasil->skor_al)
                    <!-- Score Display -->
                    <div class="text-center mb-4">
                        <h1 class="display-3 fw-bold text-info">
                            {{ number_format($hasil->skor_al, 2) }}
                        </h1>
                        <p class="text-muted">Skor Total AL</p>

                        @if($hasil->isAlFinalized())
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> FINAL
                        </span>
                        @else
                        <span class="badge bg-warning">
                            <i class="bi bi-clock"></i> Draft
                        </span>
                        @endif
                    </div>

                    <!-- Detail per Kriteria -->
                    <h6 class="fw-bold mb-3">Detail per Kriteria:</h6>
                    @foreach($hasil->detail_skor_al['kriteria'] ?? [] as $code => $kriteria)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold">{{ $code }}</small>
                            <small>{{ number_format($kriteria['total_skor'], 2) }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ ($kriteria['total_skor'] / ($kriteria['total_bobot'] * 3)) * 100 }}%">
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Actions -->
                    <div class="mt-4 d-flex gap-2">
                        @if(!$hasil->isAlFinalized())
                        <form action="{{ route('hasil-akreditasi.hitung-al', $asesmen->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="bi bi-arrow-repeat"></i> Hitung Ulang
                            </button>
                        </form>

                        <form id="form-finalisasi-al" action="{{ route('hasil-akreditasi.finalize-al', $asesmen->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-success btn-sm tombol-konfirmasi" data-id-form="form-finalisasi-al" data-message="Finalisasi hasil AL">
                                <i class="bi bi-lock"></i> Finalisasi AL
                            </button>
                        </form>
                        @else
                        <button class="btn btn-outline-success btn-sm" disabled>
                            <i class="bi bi-check-circle"></i> AL Sudah Final
                        </button>
                        @endif
                    </div>
                    @else
                    <!-- No calculation yet -->
                    <div class="text-center py-5">
                        <i class="bi bi-calculator" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada perhitungan AL</p>

                        <form action="{{ route('hasil-akreditasi.hitung-al', $asesmen->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-play-circle"></i> Hitung Skor AL
                            </button>
                        </form>
                    </div>
                    @endif
                    @else
                    <!-- AK not finalized -->
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        AK harus difinalisasi terlebih dahulu sebelum menghitung AL.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Final Result Card -->
    @if($hasil && $hasil->isFinalCombined())
    <div class="row">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-award"></i> Hasil Akhir Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <h1 class="display-1 fw-bold text-success">
                                {{ number_format($hasil->skor_final, 2) }}
                            </h1>
                            <p class="text-muted">Skor Final (dari AL)</p>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Menggunakan skor AL sebagai hasil akhir
                            </small>
                        </div>

                        <div class="col-md-4 text-center">
                            <h2 class="display-4 fw-bold text-success">
                                {{ $hasil->peringkat_akreditasi }}
                            </h2>
                            <p class="text-muted">Status Akreditasi</p>

                            @if($hasil->peringkat_akreditasi === 'Unggul')
                            <span class="badge bg-success">
                                <i class="bi bi-star-fill"></i> Memenuhi Syarat Unggul
                            </span>
                            @elseif($hasil->skor_final >= 361 && !$hasil->al_memenuhi_syarat_unggul)
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-triangle"></i> Diturunkan dari Unggul
                            </span>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="d-grid gap-2">
                                @if($hasil->status !== 'published')
                                <form id="form-publish-hasil-prodi" action="{{ route('hasil-akreditasi.publish', $asesmen->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="button" class="btn btn-success btn-sm tombol-konfirmasi" data-id-form="form-publish-hasil-prodi" data-message="Publish hasil ke prodi">
                                        <i class="bi bi-send"></i> Publish Hasil ke Prodi
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('hasil-akreditasi.form', $hasil->id_pengajuan) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Sampaikan Hasil Resmi
                                </a>
                                @endif

                                <a href="{{ route('hasil-akreditasi.download', [$asesmen->id, 'pdf']) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-download"></i> Download Laporan (PDF)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Validasi -->
                    @if($hasil->catatan_validasi)
                    <hr>
                    <div class="alert alert-info alert-permanent mb-0">
                        <h6 class="fw-bold">Catatan Validasi:</h6>
                        <pre class="mb-0" style="white-space: pre-wrap;">{{ $hasil->catatan_validasi }}</pre>
                    </div>
                    @endif

                    <!-- Calculation Formula -->
                    <hr>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Skor AK</p>
                            <p class="fw-bold">{{ number_format($hasil->skor_ak, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Skor AL</p>
                            <p class="fw-bold">{{ number_format($hasil->skor_al, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Formula</p>
                            <p class="fw-bold">(AK + AL) / 2</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
