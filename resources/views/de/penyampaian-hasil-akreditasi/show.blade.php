{{-- resources\views\de\penyampaian-hasil-akreditasi\show.blade.php --}}

@extends('layouts.template.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('de.penyampaian-hasil-akreditasi') }}" class="text-dark">Penyampaian Hasil</a>
                    </li>
                    <li class="breadcrumb-item active">Detail Hasil</li>
                </ol>
            </nav>
            <h4 class="mb-0">
                <i class="bi bi-clipboard-data text-primary"></i>
                Hasil Akreditasi
            </h4>
            <p class="mb-2">{{ $pengajuan->judul }}</p>
        </div>

        <div>
            <a href="{{ route('de.penyampaian-hasil-akreditasi') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        {{-- Skor AL --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Skor AL</h6>
                    @if($hasil->skor_al)
                    <h1 class="mb-0 text-dark display-4">{{ number_format($hasil->skor_al, 0) }}</h1>
                    <small class="text-muted">dari 400</small>
                    @else
                    <h2 class="mb-0 text-muted">-</h2>
                    <small class="text-muted">Belum dihitung</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Peringkat --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Peringkat Akreditasi (yang disampaikan ke PS)</h6>
                    @if($hasil->peringkat_akreditasi)
                    @php
                    $badgeClass = match($hasil->peringkat_akreditasi) {
                    'Unggul' => 'success',
                    'Baik Sekali' => 'primary',
                    'Baik' => 'info',
                    default => 'secondary'
                    };
                    $peringkatAkreditasi = $hasil->peringkat_akreditasi;
                    @endphp
                    <span class="badge p-2 px-3 my-4 fs-5" style="background-color: {{ $hasil->getPeringkatColor($peringkatAkreditasi) }}; color:#222">
                        {{ $peringkatAkreditasi }}
                    </span>
                    @else
                    <h3 class="mb-0 text-muted">Belum Difinalisasi</h3>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-4">Status Hasil</h6>
                    @if($hasil->isAlFinalized())
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-check-circle"></i> Difinalisasi
                    </span>
                    @if($hasil->tanggal_finalisasi_al)
                    <div class="mt-2">
                        <small class="text-muted">{{ $hasil->tanggal_finalisasi_al->format('d M Y') }}</small>
                    </div>
                    @endif
                    @else
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-clock"></i> Draft
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Total Elemen --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Elemen Dinilai</h6>
                    <h1 class="mb-0 display-4">{{ count($elemenList) }}</h1>
                    <small class="text-muted">Elemen standar</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    @if(!$hasil->isAlFinalized())
    <div class="card border-info mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="bi bi-info-circle text-info"></i>
                        Status Draft
                    </h5>
                    <p class="text-muted mb-0">
                        @php
                        $peringkatAL = $hasil->getPeringkatFromSkorAL($hasil->skor_al);
                        @endphp
                        Hasil masih dalam status <strong>DRAFT</strong> dengan skor akhir yaitu: {{ number_format($hasil->skor_al, 0) }},
                        dan masuk ke kategori:
                        <span class="badge p-2 px-3 my-2" style="background-color: {{ $hasil->getPeringkatColor($peringkatAL) }}; color:#222">
                            {{ $peringkatAL }}
                        </span>
                        <br>Anda dapat menghitung ulang atau melakukan finalisasi.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <form action="{{ route('de.penyampaian-hasil-akreditasi.calculate', $pengajuan->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Hitung ulang hasil akreditasi?')">
                                <i class="bi bi-arrow-repeat"></i> Hitung Ulang
                            </button>
                        </form>

                        <form action="{{ route('de.penyampaian-hasil-akreditasi.finalize', $pengajuan->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success {{ !$canFinalize ? 'disabled' : '' }}" {{ !$canFinalize ? 'disabled' : '' }} onclick="return confirm('Finalisasi hasil akreditasi? Tindakan ini tidak dapat dibatalkan!')" @if(!$canFinalize) title="Upload Berita Acara terlebih dahulu" @endif>
                                <i class="bi bi-lock"></i> Finalisasi
                            </button>
                        </form>
                    </div>

                    @if(!$canFinalize)
                    <div class="mt-2">
                        <small class="text-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            Upload Berita Acara untuk finalisasi
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @elseif($hasil->isAlFinalized())
    <div class="alert alert-light alert-permanent">
        <i class="bi bi-check-circle-fill me-2"></i>
        Hasil telah <strong>Disampaikan ke Program Studi</strong> pada {{ $hasil->tanggal_finalisasi_al?->format('d F Y, H:i') }}
    </div>
    @else
    <div class="alert alert-light alert-permanent">
        <i class="bi bi-check-circle-fill me-2"></i>
        Hasil belum diketahui
    </div>
    @endif

    @if(!$hasil->isAlFinalized())
    {{-- Jika Berita Acara belum diupload --}}
    @if(!$beritaAcara)
    <div class="card border-light mb-4">
        <div class="card-header bg-light text-dark">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Upload Berita Acara Rapat Penyampaian Hasil
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-light border-2 alert-permanent">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Perhatian:</strong> Anda harus mengupload <strong>Berita Acara Rapat Penyampaian Hasil Akreditasi</strong>
                sebelum dapat melakukan finalisasi hasil.
            </div>

            <form action="{{ route('de.penyampaian-hasil-akreditasi.upload-berita-acara', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="berita_acara" class="form-label">
                        File Berita Acara <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control @error('berita_acara') is-invalid @enderror" id="berita_acara" name="berita_acara" accept=".pdf" required>
                    <small class="text-muted">Format: PDF, Maksimal 10MB</small>
                    @error('berita_acara')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-dark">
                        <i class="bi bi-cloud-upload"></i> Upload Berita Acara
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Jika Berita Acara sudah diupload --}}
    @else
    <div class="card mb-4">
        <div class="card-header bg-light text-dark">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-check-fill"></i>
                Berita Acara Rapat Penyampaian Hasil
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">{{ $beritaAcara->title }}</h6>
                    <p class="text-muted mb-2">
                        <i class="bi bi-file-pdf text-danger"></i>
                        {{ $beritaAcara->original_name }}
                        {{-- <span class="badge bg-light text-dark ms-2">
                            {{ number_format($beritaAcara->size / 1024, 2) }} KB
                        </span> --}}
                    </p>
                    <small class="text-muted">
                        Diupload pada {{ $beritaAcara->uploaded_at?->format('d M Y, H:i') }}
                    </small>
                    @if($beritaAcara->keterangan)
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="bi bi-chat-left-text"></i>
                            {{ $beritaAcara->keterangan }}
                        </small>
                    </div>
                    @endif
                </div>
                <div class="btn-group">
                    <a href="{{ route('de.penyampaian-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-eye"></i> Lihat File
                    </a>
                    <form action="{{ route('de.penyampaian-hasil-akreditasi.delete-berita-acara', $pengajuan->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus berita acara ini? Anda harus upload ulang untuk finalisasi.')">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    {{-- Jika sudah difinalisasi, tampilkan berita acara (read-only) --}}
    @if($beritaAcara)
    <div class="card mb-4">
        <div class="card-header bg-light text-dark">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-check-fill"></i>
                Berita Acara Rapat Penyampaian Hasil
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">{{ $beritaAcara->title }}</h6>
                    <p class="text-muted mb-2">
                        <i class="bi bi-file-pdf text-danger"></i>
                        {{ $beritaAcara->original_name }}
                    </p>
                    <small class="text-muted">
                        Diupload pada {{ $beritaAcara->uploaded_at?->format('d M Y, H:i') }}
                    </small>
                </div>
                <div>
                    <a href="{{ route('de.penyampaian-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-secondary text-white border-0">
            <h5 class="mb-0">
                <i class="bi bi-info-circle"></i>
                Keterangan Batasan Skor Akreditasi
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                {{-- 0-250: Tidak Terakreditasi --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #f8d7da;">
                        <div class="me-3">
                            <strong class="text-dark">0 - 250</strong>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-dark">Tidak Terakreditasi</strong>
                            <div><small class="text-muted">Ditolak validator dokumen</small></div>
                        </div>
                    </div>
                </div>

                {{-- 251-300: Terakreditasi Sementara (2 tahun) --}}
                {{-- <div class="col-md-4">
                    <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #fff3cd;">
                        <div class="me-3">
                            <strong class="text-dark">251 - 300</strong>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-dark">Terakreditasi Sementara</strong>
                            <div><small class="text-muted">(2 tahun)</small></div>
                        </div>
                    </div>
                </div> --}}

                {{-- 301-350: Terakreditasi (5 tahun) / Baik --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #d1ecf1;">
                        <div class="me-3">
                            <strong class="text-dark">251 - 350</strong>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-dark">Terakreditasi</strong>
                            {{-- <div><small class="text-muted">(5 tahun)</small></div> --}}
                        </div>
                    </div>
                </div>

                {{-- 351-360: Terakreditasi Unggul with requirement (Unggul 2 tahun) --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #d4edda;">
                        <div class="me-3">
                            <strong class="text-dark">351 - 360</strong>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-dark">⁠terakreditasi Unggul with Requirement (2 Tahun)*</strong>
                            <div><small class="text-muted">*Dengan syarat pelampauan standar</small></div>
                        </div>
                    </div>
                </div>

                {{-- 361-400: Terakreditasi Unggul (5 tahun) --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #c3e6cb;">
                        <div class="me-3">
                            <strong class="text-dark">361 - 400</strong>
                        </div>
                        <div class="flex-grow-1">
                            <strong class="text-dark">Terakreditasi Unggul (5 Tahun)</strong>
                            <div><small class="text-muted">*Dengan syarat pelampauan standar</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light alert-permanent mt-3 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Catatan:</strong> Untuk peringkat <strong>Unggul</strong>, selain mencapai skor >= 351,
                program studi harus memiliki <strong>minimal 1 elemen dengan kategori: Pelampauan Standar (Exceeding Standard)</strong>
                di <strong>setiap kriteria</strong> (D, E, P, I, L, A, R).
            </div>
        </div>
    </div>

    {{-- Validasi Peringkat Unggul --}}
    @if($hasil->skor_final >= 361)
    <div class="card mb-4 border-{{ $validationSummary['dapat_unggul'] ? 'success' : 'warning' }}">
        <div class="card-header bg-{{ $validationSummary['dapat_unggul'] ? 'success' : 'warning' }} text-white">
            <h5 class="mb-0">
                <i class="bi bi-{{ $validationSummary['dapat_unggul'] ? 'shield-check' : 'exclamation-triangle' }}"></i>
                Validasi Syarat Peringkat UNGGUL
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 bg-success bg-opacity-10 rounded">
                        <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                        <div>
                            <strong>Skor Memenuhi Syarat</strong>
                            <div class="text-muted">Skor >= 361 ({{ number_format($hasil->skor_final, 2) }})</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 bg-{{ $validationSummary['pelampauan_memenuhi'] ? 'success' : 'danger' }} bg-opacity-10 rounded">
                        <i class="bi bi-{{ $validationSummary['pelampauan_memenuhi'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} fs-3 me-3"></i>
                        <div>
                            <strong>Pelampauan Standar</strong>
                            <div class="text-muted">
                                @if($validationSummary['pelampauan_memenuhi'])
                                Semua kriteria terpenuhi ✓
                                @else
                                {{ count($validationSummary['missing_kriteria']) }} kriteria belum memiliki pelampauan
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail per Kriteria --}}
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Kriteria</th>
                            <th class="text-center" width="25%">Status Pelampauan</th>
                            <th class="text-center" width="20%">Jumlah Elemen Skor 4</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\HasilAkreditasi::KRITERIA_REQUIRED as $kode)
                        @php
                        $status = $validationSummary['kriteria_status'][$kode] ?? ['has_pelampauan' => false, 'jumlah_elemen_skor_4' => 0];
                        @endphp
                        <tr>
                            <td class="text-center"><strong>{{ $kode }}</strong></td>
                            <td class="text-center">
                                @if($status['has_pelampauan'])
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Terpenuhi
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Belum Terpenuhi
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $status['jumlah_elemen_skor_4'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!$validationSummary['pelampauan_memenuhi'])
            <div class="alert alert-warning alert-permanent mt-3 mb-0">
                <strong>⚠️ Perhatian:</strong> Meskipun skor mencapai >= 361, peringkat <strong>TIDAK DAPAT</strong> ditetapkan sebagai UNGGUL
                karena kriteria <strong>{{ implode(', ', $validationSummary['missing_kriteria']) }}</strong>
                belum memiliki minimal 1 elemen dengan kategori Pelampauan Standar (Exceeding Standard).
                <br><br>
                Peringkat akan diturunkan menjadi: <strong class="text-danger">BAIK SEKALI</strong>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Detail Skor per Kriteria --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-bar-chart-fill"></i>
                Detail Skor per Kriteria
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">Kode</th>
                            <th width="40%">Nama Kriteria</th>
                            <th class="text-center" width="15%">Jumlah Elemen</th>
                            <th class="text-center" width="15%">Total Bobot</th>
                            <th class="text-center" width="15%">Skor Tertimbang</th>
                            <th class="text-center" width="10%">Pelampauan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kriteriaList as $kode => $data)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $kode }}</span></td>
                            <td>{{ $data['nama'] }}</td>
                            <td class="text-center">{{ $data['elemen_count'] }}</td>
                            <td class="text-center">{{ number_format($data['total_bobot'], 2) }}</td>
                            <td class="text-center">
                                <strong class="text-primary">{{ number_format($data['total_skor'], 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($data['has_pelampauan'])
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted fs-5"></i>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($kriteriaList))
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">TOTAL:</th>
                            <th class="text-center">{{ number_format($hasil->total_bobot_al ?? 0, 2) }}</th>
                            <th class="text-center">
                                <strong class="text-primary fs-5">{{ number_format($hasil->skor_al ?? 0, 2) }}</strong>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Skor per Elemen Standar --}}
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Detail Skor per Elemen Standar
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="table-elemen">
                    <thead class="table-light align-middle">
                        <tr>
                            <th width="5%">Kriteria - Elemen</th>
                            <th width="30%">Pernyataan Elemen</th>
                            <th class="text-center" width="35%">Kategori</th>
                            <th class="text-center" width="10%">Bobot</th>
                            <th class="text-center" width="15%">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($elemenList as $elemen)
                        @php
                        $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'class' => 'secondary'];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-secondary mb-1" style="width: fit-content;">{{ $elemen['kode_kriteria'] }}</span>
                                    <code class="text-primary">{{ $elemen['kode_elemen'] }}</code>
                                </div>
                            </td>
                            <td>{{ Str::limit($elemen['nama_elemen'], 120) }}</td>
                            <td class="text-center">
                                <span class="badge text-wrap" style="width: 15rem; background-color: {{ $kategori['color'] }}; color: #222;">
                                    {{ $kategori['label'] }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">({{ number_format($elemen['skor'], 2) }})</small>
                                </div>
                            </td>
                            <td class="text-center">{{ number_format($elemen['bobot'], 2) }}</td>
                            <td class="text-center">
                                <strong>{{ number_format($elemen['skor_tertimbang'], 2) }}</strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Catatan Validasi --}}
    @if($hasil->catatan_validasi)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-chat-left-text-fill"></i>
                Catatan Validasi
            </h5>
        </div>
        <div class="card-body">
            <pre class="mb-0" style="white-space: pre-wrap;">{{ $hasil->catatan_validasi }}</pre>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#table-elemen').DataTable({
            pageLength: 25
            , order: [], // ✅ NO SORT - preserve insertion order
            columnDefs: [{
                    orderable: false
                    , targets: '_all'
                } // Disable sorting on all columns
            ]
            , language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });
    });

</script>
@endpush
@endsection
