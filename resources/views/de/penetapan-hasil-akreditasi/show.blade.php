{{-- resources/views/de/penetapan-hasil-akreditasi/show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Penetapan Hasil Akreditasi')

@section('content')
<div class="container-fluid">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penetapan-hasil-akreditasi') }}">Penetapan Hasil Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-award"></i> Detail Penetapan Hasil Akreditasi
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <a href="{{ route('de.penetapan-hasil-akreditasi') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        {{-- Skor AL --}}
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Skor Hasil</h6>
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
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Status Akreditasi (yang ditetapkan)</h6>
                    @if($hasil->peringkat_akreditasi_final)
                    <span class="badge p-2 px-3 my-3 fs-6" style="background-color: {{ $hasil->getPeringkatColor() }}; color:#222">
                        {{ $hasil->peringkat_akreditasi_final }}
                    </span>
                    @if($hasil->statusFinal->siklus_tahun)
                    <div><small class="text-muted">{{ $hasil->statusFinal->siklus_tahun }} Tahun</small></div>
                    @endif
                    @else
                    <h3 class="mb-0 text-muted">Belum Ditetapkan</h3>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-4">Status Penetapan</h6>
                    @if($sudahDitetapkan)
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-check-circle"></i> Ditetapkan
                    </span>
                    @if($pengajuan->tanggal_penetapan)
                    <div class="mt-2">
                        <small class="text-muted">{{ $pengajuan->tanggal_penetapan->locale('id')->translatedFormat('d M Y') }}</small>
                    </div>
                    @endif
                    @else
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-clock"></i> Menunggu
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Total Elemen --}}
        <div class="col-lg-6 col-xl-3 my-1">
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
    @if(!$sudahDitetapkan)
    <div class="card border-info mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="bi bi-info-circle text-info"></i>
                        Menunggu Penetapan
                    </h5>
                    <p class="text-muted mb-0">
                        @php
                        $peringkatFinal = $hasil->getPeringkatFromSkor($hasil->skor_final);
                        @endphp
                        Hasil masih dalam status <strong>MENUNGGU PENETAPAN</strong> dengan skor akhir yaitu: {{ number_format($hasil->skor_final, 0) }},
                        dan masuk ke kategori:
                        <span class="badge p-2 px-3 my-2" style="background-color: {{ $hasil->getPeringkatColor($peringkatFinal) }}; color:#222">
                            {{ $peringkatFinal }}
                        </span>
                        <br>Anda dapat menetapkan hasil akreditasi setelah mengupload Berita Acara Rapat Penetapan Hasil.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <form action="{{ route('de.penetapan-hasil-akreditasi.tetapkan', $pengajuan->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success {{ !$canTetapkan ? 'disabled' : '' }}" {{ !$canTetapkan ? 'disabled' : '' }} onclick="return confirm('Tetapkan hasil akreditasi? Tindakan ini tidak dapat dibatalkan!')" @if(!$canTetapkan) title="Upload Berita Acara terlebih dahulu" @endif>
                            <i class="bi bi-lock"></i> Tetapkan Hasil
                        </button>
                    </form>

                    @if(!$canTetapkan)
                    <div class="mt-2">
                        <small class="text-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            Upload Berita Acara untuk penetapan
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-light border-3 alert-permanent">
        <i class="bi bi-check-circle-fill me-2"></i>
        Hasil telah <strong>Ditetapkan</strong> pada {{ $pengajuan->tanggal_penetapan?->locale('id')->translatedFormat('d F Y, H:i') }}
    </div>

    {{-- Optional: Tombol Batalkan (jika sudah ditetapkan tapi belum diumumkan) --}}
    {{-- @if(!in_array($pengajuan->status, [
    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
    \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
    \App\Models\PengajuanAkreditasi::STATUS_SELESAI,
    ]))
    <div class="alert alert-warning alert-permanent">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-exclamation-triangle me-2"></i>
                Penetapan masih dapat dibatalkan sebelum hasil diumumkan.
            </div>
            <form action="{{ route('de.penetapan-hasil-akreditasi.batalkan', $pengajuan->id) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Batalkan penetapan hasil? Anda perlu menetapkan ulang.')">
        <i class="bi bi-x-circle"></i> Batalkan Penetapan
    </button>
    </form>
</div>
</div>
@endif --}}
@endif

{{-- Berita Acara Penetapan --}}
@if(!$sudahDitetapkan)
{{-- Jika Berita Acara belum diupload --}}
@if(!$beritaAcaraPenetapan)
<div class="card border-light mb-4">
    <div class="card-header bg-light text-dark">
        <h5 class="mb-0">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Upload Berita Acara Rapat Penetapan Hasil
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-light border-2 alert-permanent">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Perhatian:</strong> Anda harus mengupload <strong>Berita Acara Rapat Penetapan Hasil Akreditasi</strong>
            sebelum dapat melakukan penetapan hasil.
        </div>

        <form action="{{ route('de.penetapan-hasil-akreditasi.upload-berita-acara', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
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
            Berita Acara Rapat Penetapan Hasil
        </h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">{{ $beritaAcaraPenetapan->title }}</h6>
                <p class="text-muted mb-2">
                    <i class="bi bi-file-pdf text-danger"></i>
                    {{ $beritaAcaraPenetapan->original_name }}
                </p>
                <small class="text-muted">
                    Diupload pada {{ $beritaAcaraPenetapan->uploaded_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                </small>
                @if($beritaAcaraPenetapan->keterangan)
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-chat-left-text"></i>
                        {{ $beritaAcaraPenetapan->keterangan }}
                    </small>
                </div>
                @endif
            </div>
            <div class="btn-group">
                <a href="{{ route('de.penetapan-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-dark" target="_blank">
                    <i class="bi bi-eye"></i> Lihat File
                </a>
                <form action="{{ route('de.penetapan-hasil-akreditasi.delete-berita-acara', $pengajuan->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus berita acara ini? Anda harus upload ulang untuk penetapan.')">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@else
{{-- Jika sudah ditetapkan, tampilkan berita acara (read-only) --}}
@if($beritaAcaraPenetapan)
<div class="card mb-4">
    <div class="card-header bg-light text-dark">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-check-fill"></i>
            Berita Acara Rapat Penetapan Hasil
        </h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">{{ $beritaAcaraPenetapan->title }}</h6>
                <p class="text-muted mb-2">
                    <i class="bi bi-file-pdf text-danger"></i>
                    {{ $beritaAcaraPenetapan->original_name }}
                </p>
                <small class="text-muted">
                    Diupload pada {{ $beritaAcaraPenetapan->uploaded_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                </small>
            </div>
            <div>
                <a href="{{ route('de.penetapan-hasil-akreditasi.download-berita-acara', $pengajuan->id) }}" class="btn btn-outline-dark" target="_blank">
                    <i class="bi bi-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endif

{{-- Keterangan Batasan Skor Akreditasi --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-secondary text-white border-0">
        <h5 class="mb-0">
            <i class="bi bi-info-circle"></i>
            Keterangan Batasan Skor Akreditasi
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($rentangSkor as $rentang)
            @php
            $makna = $rentang['makna'] ?? null;
            $isUnggul = str_contains(strtolower($rentang['status'] ?? ''), 'unggul');
            @endphp
            <div class="col-md-6">
                <div class="d-flex align-items-start p-3 rounded h-100 border" style="background-color: {{ $rentang['warna'] ?? 'fff' }}">
                    <div class="me-3 text-nowrap pt-1">
                        <strong class="text-dark">
                            {{ $rentang['skor_min'] }} – {{ $rentang['skor_max'] }}
                        </strong>
                        @if(isset($rentang['persen_min']))
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $rentang['persen_min'] }}–{{ $rentang['persen_max'] }}%
                        </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">{{ $rentang['status'] }}</strong>
                        @if(isset($rentang['siklus_tahun']))
                        <span class="badge bg-secondary ms-1" style="font-size:.7rem">
                            {{ $rentang['siklus_tahun'] }} Tahun
                        </span>
                        @endif

                        @if($makna)
                        <div class="mt-1">
                            @if(is_array($makna))
                            <ul class="mb-0 ps-3" style="font-size:.8rem">
                                @foreach($makna as $m)
                                <li class="text-muted">{{ $m }}</li>
                                @endforeach
                            </ul>
                            @else
                            <small class="text-muted">{{ $makna }}</small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="alert alert-light alert-permanent mt-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Catatan:</strong> Untuk status akreditasi <strong>Unggul</strong>, selain mencapai skor >= {{ $validationSummary['skor_minimum'] }},
            program studi harus memiliki <strong>minimal 1 elemen dengan kategori: "Melampaui Standar"</strong>
            di <strong>setiap kriteria</strong> ({{ implode(', ', array_keys($validationSummary['kriteria_status'])) }}),
            serta memenuhi syarat rasio DTPS dan jabatan fungsional dosen.
        </div>
    </div>
</div>

{{-- Validasi Peringkat Unggul --}}
@if($validationSummary['skor_memenuhi'])
@php $syaratP1 = $validationSummary['syarat_p1']; @endphp
<div class="card mb-4 border-{{ $validationSummary['dapat_unggul'] ? 'secondary' : 'secondary' }}">
    <div class="card-header bg-{{ $validationSummary['dapat_unggul'] ? 'secondary' : 'secondary' }} text-white">
        <h5 class="mb-0">
            <i class="bi bi-{{ $validationSummary['dapat_unggul'] ? 'shield-check' : 'exclamation-triangle' }}"></i>
            Validasi Syarat Status Akreditasi UNGGUL
        </h5>
    </div>
    <div class="card-body">

        {{-- Baris syarat: Skor + Pelampauan + Rasio DTPS + Jabatan --}}
        <div class="row mb-3 g-3">

            {{-- Syarat 1: Skor --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded h-100">
                    <i class="bi bi-check-circle-fill text-dark fs-3 me-3"></i>
                    <div>
                        <strong>Skor Memenuhi Syarat</strong>
                        <div class="text-muted">
                            Skor ≥ {{ $validationSummary['skor_minimum'] }}
                            ({{ number_format($validationSummary['skor'], 2) }})
                        </div>
                    </div>
                </div>
            </div>

            {{-- Syarat 2: Pelampauan Standar --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-{{ $validationSummary['pelampauan_memenuhi'] ? 'light' : 'light' }} rounded h-100">
                    <i class="bi bi-{{ $validationSummary['pelampauan_memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-dark' }} fs-3 me-3"></i>
                    <div>
                        <strong>Pelampauan Standar</strong>
                        <div class="text-muted">
                            @if($validationSummary['pelampauan_memenuhi'])
                            Semua kriteria terpenuhi ✓
                            @else
                            {{ count($validationSummary['missing_kriteria']) }} kriteria belum memiliki elemen "Melampaui Standar"
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Syarat 3: Rasio DTPS:Mahasiswa --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-{{ $syaratP1['rasio']['memenuhi'] ? 'light' : 'light' }} rounded h-100">
                    <i class="bi bi-{{ $syaratP1['rasio']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-dark' }} fs-3 me-3"></i>
                    <div>
                        <strong>Rasio DTPS : Mahasiswa</strong>
                        <div class="text-muted">{{ $syaratP1['rasio']['keterangan'] }}</div>
                        @if(!is_null($syaratP1['rasio']['rasio'] ?? null))
                        <div class="text-muted mt-1" style="font-size:.8rem">
                            DTPS: {{ $syaratP1['rasio']['jumlah_dtps'] }} &middot;
                            Mahasiswa: {{ $syaratP1['rasio']['jumlah_mahasiswa'] ?? '—' }} &middot;
                            Rumpun: <span class="text-capitalize">{{ $syaratP1['rasio']['rumpun'] }}</span>
                        </div>
                        @else
                        <div class="text-warning mt-1" style="font-size:.8rem">
                            <i class="bi bi-exclamation-triangle me-1"></i>Data LKPS P.1/E.2 belum diupload
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Syarat 4: Jabatan --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded h-100">
                    <i class="bi bi-{{ $syaratP1['jabatan']['memenuhi'] ? 'check-circle-fill text-dark' : 'x-circle-fill text-dark' }} fs-3 me-3"></i>
                    <div>
                        <strong>{{ $syaratP1['jabatan']['label_jabatan'] ?? 'Jabatan Valid' }} ≥ {{ $syaratP1['jabatan']['persen_minimum'] ?? 50 }}%</strong>
                        <div class="text-muted">{{ $syaratP1['jabatan']['keterangan'] }}</div>
                        @if(($syaratP1['jabatan']['total_dtps'] ?? 0) > 0)
                        <div class="text-muted mt-1" style="font-size:.8rem">
                            {{ $syaratP1['jabatan']['jumlah_valid'] }} dari {{ $syaratP1['jabatan']['total_dtps'] }} DTPS
                            ({{ $syaratP1['jabatan']['persen_valid'] }}%)
                            @if($syaratP1['jabatan']['filter_dtps_aktif'] ?? false)
                            &middot; <span class="text-success">Filter P.1.3 aktif</span>
                            @endif
                        </div>
                        @else
                        <div class="text-warning mt-1" style="font-size:.8rem">
                            <i class="bi bi-exclamation-triangle me-1"></i>Data LKPS P.1 belum diupload
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Tabel detail per kriteria (pelampauan standar) --}}
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Kriteria</th>
                        <th class="text-center" width="25%">Status Pelampauan Standar</th>
                        <th class="text-center" width="20%">Jumlah Elemen Pelampauan</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ✅ Loop dari array dinamis, bukan konstanta statis --}}
                    @foreach(array_keys($validationSummary['kriteria_status']) as $kode)
                    @php
                    $status = $validationSummary['kriteria_status'][$kode];
                    @endphp
                    <tr>
                        <td class="text-center"><strong>{{ $kode }}</strong></td>
                        <td class="text-center">
                            @if($status['has_pelampauan'])
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-check-circle"></i> Terpenuhi
                            </span>
                            @else
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-x-circle"></i> Belum Terpenuhi
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $status['jumlah_elemen_skor_4'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Peringatan jika tidak semua syarat terpenuhi --}}
        @if(!$validationSummary['dapat_unggul'])
        <div class="alert alert-light alert-permanent mt-3 mb-0">
            <strong>⚠️ Perhatian:</strong> Meskipun skor mencapai ≥ {{ $validationSummary['skor_minimum'] }},
            status akreditasi <strong>TIDAK DAPAT</strong> ditetapkan sebagai UNGGUL karena:
            <ul class="mb-0 mt-2">
                @if(!$validationSummary['pelampauan_memenuhi'])
                <li>
                    Kriteria <strong>{{ implode(', ', $validationSummary['missing_kriteria']) }}</strong>
                    belum memiliki minimal 1 elemen dengan kategori "Melampaui Standar".
                </li>
                @endif
                @if(!$syaratP1['rasio']['memenuhi'])
                <li>{{ $syaratP1['rasio']['keterangan'] }}</li>
                @endif
                @if(!$syaratP1['jabatan']['memenuhi'])
                <li>{{ $syaratP1['jabatan']['keterangan'] }}</li>
                @endif
            </ul>
        </div>
        @endif

    </div>
</div>
@endif

{{-- Detail Skor per Kriteria --}}
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
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
                        <th class="text-center" width="10%">Pelampauan Standar</th>
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
                            <strong class="text-dark">{{ number_format($data['total_skor'], 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($data['has_pelampauan'])
                            <i class="bi bi-check-circle-fill text-dark fs-5"></i>
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
                            <strong class="text-dark fs-5">{{ number_format($hasil->skor_al ?? 0, 2) }}</strong>
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
                        <th width="5%">Kriteria</th>
                        <th width="30%">Pernyataan Elemen</th>
                        <th class="text-center" width="35%">Kategori</th>
                        <th class="text-center" width="10%">Bobot</th>
                        <th class="text-center" width="15%">Skor Tertimbang</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $grouped = collect($elemenList)->groupBy('kode_kriteria');
                    @endphp

                    @forelse($grouped as $kodeKriteria => $items)
                    @php $rowspan = $items->count(); @endphp

                    @foreach($items as $index => $elemen)
                    @php
                    $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'color' => '#e9ecef'];
                    @endphp
                    <tr>
                        {{-- ✅ Rowspan untuk Kriteria --}}
                        @if($index === 0)
                        <td rowspan="{{ $rowspan }}" class="align-middle">
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge bg-secondary fs-6 py-2 px-3">{{ $kodeKriteria }}</span>
                            </div>
                        </td>
                        @endif

                        {{-- Elemen --}}
                        <td>
                            <code class="text-primary fw-bold">
                                {{ $elemen['kode_elemen'] }}
                            </code>
                            <span>
                                {{ Str::limit($elemen['nama_elemen'], 120) }}
                            </span>
                        </td>

                        {{-- Kategori --}}
                        <td class="text-center">
                            <span class="badge text-wrap" style="width: 15rem; background-color: {{ $kategori['color'] }}; color: #222;">
                                {{ $kategori['label'] }}
                            </span>
                            <div class="mt-1">
                                <small class="text-muted">
                                    ({{ number_format($elemen['skor'], 2) }})
                                </small>
                            </div>
                        </td>

                        {{-- Bobot --}}
                        <td class="text-center">
                            {{ number_format($elemen['bobot'], 2) }}
                        </td>

                        {{-- Skor Tertimbang --}}
                        <td class="text-center">
                            <strong>
                                {{ number_format($elemen['skor_tertimbang'], 2) }}
                            </strong>
                        </td>
                    </tr>
                    @endforeach

                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Belum ada data
                        </td>
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
    <div class="card-header bg-secondary text-white">
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
@endsection
