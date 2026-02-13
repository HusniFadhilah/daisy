@extends('layouts.template.app')

@section('title', 'Permohonan Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-file-earmark-text"></i> Permohonan Akreditasi</h2>
            <p class="text-muted mb-0">Kelola permohonan akreditasi program studi</p>
        </div>
        <a href="{{ route('pengajuan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Permohonan Akreditasi
        </a>
    </div>

    {{-- ========== SECTION PENGINGAT AKREDITASI ========== --}}
    @if($pengingatBelumDirespon->count() > 0)
    <div class="alert alert-warning alert-dismissible alert-permanent fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Perhatian!</strong>
                Anda memiliki <strong>{{ $pengingatBelumDirespon->count() }}</strong> pengingat akreditasi yang belum direspon.
                <br>
                <small>Silakan ajukan permohonan akreditasi untuk program studi yang dimaksud.</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-bell"></i> Pengingat Akreditasi
                <span class="badge bg-danger ms-2 urgent-badge">{{ $pengingatBelumDirespon->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="accordion accordion-flush" id="accordionPengingat">
                @foreach($pengingatBelumDirespon as $index => $pengingat)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $pengingat->id }}">
                        <button class="accordion-button {{ $index > 0 ? '' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $pengingat->id }}" aria-expanded="{{ $index === 0 ? 'false' : 'false' }}">
                            <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                <div>
                                    <strong class="text-primary">{{ $pengingat->studyProgram->full_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> {{ $pengingat->studyProgram->university->name }}
                                        •
                                        <i class="bi bi-mortarboard"></i> {{ $pengingat->studyProgram->degreeLevel->name }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning text-dark pengingat-badge">
                                        <i class="bi bi-clock"></i>
                                        {{ $pengingat->tanggal_dikirim->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $pengingat->id }}" class="accordion-collapse collapse {{ $index === 0 ? '' : '' }}" data-bs-parent="#accordionPengingat">
                        <div class="accordion-body">
                            <div class="row">
                                <!-- Detail Pengingat -->
                                <div class="col-lg-8">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-info-circle"></i> Detail Pengingat
                                    </h6>

                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="35%">Program Studi</th>
                                                <td>: {{ $pengingat->studyProgram->full_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Universitas</th>
                                                <td>: {{ $pengingat->studyProgram->university->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jenjang</th>
                                                <td>: {{ $pengingat->studyProgram->degreeLevel->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tahun Akreditasi</th>
                                                <td>: <span class="badge bg-info">{{ $pengingat->tahun_akreditasi }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Dikirim</th>
                                                <td>: {{ $pengingat->tanggal_dikirim->locale('id')->translatedFormat('d F Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dikirim Oleh</th>
                                                <td>: {{ $pengingat->pengirim->name }} (DE)</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Pesan Pengingat -->
                                    <div class="alert alert-info alert-permanent">
                                        <h6 class="fw-bold mb-2">
                                            <i class="bi bi-envelope-open"></i> Pesan Pengingat:
                                        </h6>
                                        <p class="mb-0" style="white-space: pre-wrap;">{{ $pengingat->pesan_pengingat }}</p>
                                    </div>

                                    @if($pengingat->email_terkirim_ke)
                                    <div class="alert alert-success alert-permanent mb-0">
                                        <small>
                                            <i class="bi bi-check-circle"></i>
                                            Email terkirim ke: {{ $pengingat->email_terkirim_ke }}
                                        </small>
                                    </div>
                                    @endif
                                </div>

                                <!-- Form Response -->
                                <div class="col-lg-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-success mb-3">
                                                <i class="bi bi-send-check"></i> Respon Pengingat
                                            </h6>

                                            {{-- ✅ UPDATE: Pakai route store dengan hidden input id_pengingat --}}
                                            <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="id_pengingat" value="{{ $pengingat->id }}">
                                                <input type="hidden" name="id_program_studi" value="{{ $pengingat->id_program_studi }}">
                                                <input type="hidden" name="tahun_akreditasi" value="{{ $pengingat->tahun_akreditasi }}">

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                        Jenis Akreditasi <span class="text-danger">*</span>
                                                    </label>

                                                    <select name="jenis_akreditasi" class="form-select @error('jenis_akreditasi') is-invalid @enderror" required>
                                                        <option value="">-- Pilih Jenis Akreditasi --</option>

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

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                        Surat Permohonan Akreditasi <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" name="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf" required>
                                                    <small class="text-muted">Format: PDF, Max: 5MB</small>
                                                    @error('file_surat_permohonan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="alert alert-warning alert-permanent py-2">
                                                    <small>
                                                        <i class="bi bi-info-circle"></i>
                                                        Dengan mengisi form ini, permohonan akreditasi akan langsung dibuat.
                                                    </small>
                                                </div>

                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-send"></i> Kirim Permohonan
                                                    </button>
                                                    <a href="{{ route('pengajuan.create', ['id_pengingat' => $pengingat->id]) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-pencil-square"></i> Isi Form Lengkap
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    {{-- ========== END SECTION PENGINGAT ========== --}}

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor permohonan akreditasi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\PengajuanAkreditasi::statusMap() as $key => $status)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                            {{ $status['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('pengajuan') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="row">
        @forelse($pengajuans as $pengajuan)
        @php
        $timeline = $pengajuan->timelineItems();
        $currentItem = collect($timeline)->firstWhere('state', 'current');

        // fallback aman
        if(!$currentItem){
        $currentItem = collect($timeline)->lastWhere('state', 'done') ?? collect($timeline)->first();
        }

        $isDone = ($currentItem['state'] ?? null) === 'done';
        $isCurrent = ($currentItem['state'] ?? null) === 'current';
        $itemColor = $currentItem['color'] ?? 'secondary';

        // icon sesuai pola yang kamu mau
        $iconClass = $isDone ? 'bi-check-circle-fill' : ($isCurrent ? 'bi-hourglass-split' : 'bi-circle');
        $iconColor = $isDone ? 'text-success' : ($isCurrent ? 'text-' . $itemColor : 'text-muted');
        $judulPengajuan = $pengajuan->judul;
        @endphp
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 {{ $pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM && is_null($pengajuan->id_user_pengaju) ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="me-2" style="min-width:0;">
                            <h5 class="card-title mb-1 text-wrap" title="{{ $judulPengajuan }}">
                                {{ $judulPengajuan }}
                            </h5>

                            <small class="text-muted d-block text-wrap" title="{{ $pengajuan->studyProgram->university->name ?? '' }}">
                                {{ $pengajuan->studyProgram->university->name ?? '' }}
                            </small>

                            <small class="text-muted d-block">
                                <i class="bi bi-hash"></i> {{ $pengajuan->nomor_pengajuan }}
                            </small>
                        </div>
                    </div>

                    {{-- Badge status: dibatasi supaya tidak keluar kotak --}}
                    <span class="badge bg-{{ $itemColor }} text-wrap text-center" style="white-space: normal; line-height: 1.1;" title="{{ $currentItem['label'] }}">
                        <i class="bi {{ $iconClass }} text-white me-1"></i>
                        {{ $currentItem['label'] }}
                    </span>

                    <div class="my-3">
                        @if($pengajuan->jenis_akreditasi)
                        <small class="text-muted d-block">
                            <i class="bi bi-tag"></i>
                            {{ $pengajuan->jenis_akreditasi_label }}
                        </small>
                        @endif

                        @if($pengajuan->tahun_akreditasi)
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar"></i>
                            Tahun: {{ $pengajuan->tahun_akreditasi }}
                        </small>
                        @endif

                        {{-- Tampilkan tanggal hanya sekali --}}
                        <small class="text-muted d-block">
                            <i class="bi bi-clock"></i>
                            Diajukan: {{ \App\Libraries\Date::tglIndo($pengajuan->created_at) }}
                        </small>

                        {{-- @if($pengajuan->deskEvaluator)
                        <small class="text-muted d-block">
                            <i class="bi bi-person"></i>
                            DE: {{ $pengajuan->deskEvaluator->name }}
                        </small>
                        @endif --}}
                    </div>

                    {{-- ✅ BUTTON BERBEDA UNTUK STATUS PENGINGAT_DIKIRIM --}}
                    @if($pengajuan->status === 'pengingat_dikirim' && is_null($pengajuan->id_user_pengaju))
                    <a href="{{ route('pengajuan.create', ['pengajuan_id' => $pengajuan->id]) }}" class="btn btn-warning btn-sm w-100">
                        <i class="bi bi-pencil-square"></i> Lengkapi Data Permohonan Akreditasi
                    </a>
                    @else
                    <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-eye"></i> Lihat Detail
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted">Belum ada permohonan akreditasi</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($pengajuans->hasPages())
    <div class="d-flex justify-content-center">
        {{ $pengajuans->links() }}
    </div>
    @endif
</div>
@endsection
