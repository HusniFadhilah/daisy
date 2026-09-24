{{-- resources/views/asesmen/al/berkas/hasil.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Hasil Penilaian AL - ' . $asesmen->getName(false))

@section('content')
@php
    $isFinalized = $hasil->exists && $hasil->isAlFinalized();
    $isSubmitted = in_array($assignment->status_pekerjaan ?? '', ['submitted', 'approved'], true);
    $displaySkor = $displaySkor ?? $hasil->skor_al;
    $skorIsPreview = $skorIsPreview ?? false;
    $isPenilaianComplete = $isPenilaianComplete ?? false;
    $totalElemen = $totalElemen ?? 0;
    $assessedElemen = $assessedElemen ?? 0;
    $canSubmit = $canEdit && !$isSubmitted && !$isFinalized && $isPenilaianComplete;
    $peringkatDraft = $displaySkor
        ? $hasil->getPeringkatFromSkor((float) $displaySkor, 'al')
        : null;
@endphp
<div class="container-fluid py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('al.berkas') }}">Berkas Penilaian AL</a></li>
            <li class="breadcrumb-item active">Hasil Penilaian AL</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Hasil Penilaian AL
            </h5>
            <small class="text-muted">{{ $asesmen->getName(false) }}</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($canSubmit)
            <button type="button" class="btn btn-success" id="btnKirimPenilaian">
                <i class="bi bi-send"></i> Kirim Penilaian
            </button>
            @endif
            @if($canEdit && !$isFinalized)
            <a href="{{ route('al.berkas.show', $asesmen->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Kembali ke Penilaian
            </a>
            @endif
            <a href="{{ route('al.berkas') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if($calcError)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ $calcError }}
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Skor AL</h6>
                    @if($displaySkor)
                    <h1 class="mb-0 text-dark display-4">{{ number_format($displaySkor, 0) }}</h1>
                    <small class="text-muted">dari 400{{ $skorIsPreview ? ' (pratinjau draft)' : '' }}</small>
                    @else
                    <h2 class="mb-0 text-muted">-</h2>
                    <small class="text-muted">Belum dihitung</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Status Akreditasi (draft)</h6>
                    @if($hasil->peringkat_akreditasi_hasil)
                    <span class="badge p-2 px-3 my-3 fs-6" style="background-color: {{ $hasil->getPeringkatColor(null, 'hasil') }}; color:#222">
                        {{ $hasil->peringkat_akreditasi_hasil }}
                    </span>
                    @elseif($peringkatDraft)
                    <span class="badge p-2 px-3 my-3 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkatDraft, 'al') }}; color:#222">
                        {{ $peringkatDraft }}
                    </span>
                    <div><small class="text-muted">Belum dikunci sekretariat</small></div>
                    @else
                    <h3 class="mb-0 text-muted">Belum dihitung</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 my-1">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-4">Status Hasil</h6>
                    @if($isFinalized)
                    <span class="badge bg-light text-dark rounded-pill px-4 py-2 my-3 fs-6">
                        <i class="bi bi-check-circle"></i> Difinalisasi Sekretariat
                    </span>
                    @if($hasil->tanggal_finalisasi_al)
                    <div class="mt-2">
                        <small class="text-muted">{{ $hasil->tanggal_finalisasi_al->locale('id')->translatedFormat('d M Y') }}</small>
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

    @if($isFinalized)
    <div class="alert alert-light alert-permanent">
        <i class="bi bi-lock-fill me-2"></i>
        Hasil telah dikunci oleh sekretariat. Anda hanya dapat melihat data ini.
        Unduh file penilaian di
        <a href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}" class="alert-link">link ini</a>,
        lalu upload file yang sudah ditandatangani di
        <a href="{{ route('al.berkas.documents.page', ['id' => $asesmen->id]) }}" class="alert-link">Berita Acara AL</a>.
    </div>
    @elseif($isSubmitted && $canEdit)
    <div class="alert alert-info alert-permanent">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                Penilaian sudah dikirim. Isi atau perbarui draft resume di bawah.
                Jika nilai belum sesuai, batalkan dulu lalu edit lewat halaman penilaian.
                Elemen yang tidak diubah tetap terbaca dari penilaian terakhir.
                Kunci final tetap pada sekretariat.
                <div class="mt-2">
                    Silakan unduh file hasil penilaian lengkap di
                    <a href="{{ route('al.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal']) }}" class="alert-link">link ini</a>.
                    Tandatangani, lalu upload ulang di halaman
                    <a href="{{ route('al.berkas.documents.page', ['id' => $asesmen->id]) }}" class="alert-link">Berita Acara AL</a>.
                </div>
            </div>
            <button type="button" class="btn btn-outline-warning btn-sm flex-shrink-0" id="btnUnsubmitHasil">
                <i class="bi bi-arrow-counterclockwise"></i> Batalkan Penilaian
            </button>
        </div>
    </div>
    @elseif(!$isSubmitted && $canEdit && $isPenilaianComplete)
    <div class="alert alert-warning alert-permanent">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div>
                <i class="bi bi-clipboard-data me-2"></i>
                Ini pratinjau skor dari penilaian Anda. Periksa angka di bawah, lengkapi draft resume jika perlu, lalu <strong>kirim penilaian</strong>.
                Skor resmi sekretariat hanya menghitung penilaian yang sudah dikirim. Kunci final tetap pada sekretariat.
            </div>
            <button type="button" class="btn btn-success btn-sm flex-shrink-0" id="btnKirimPenilaianAlert">
                <i class="bi bi-send"></i> Kirim Penilaian
            </button>
        </div>
    </div>
    @elseif(!$isSubmitted && $canEdit)
    <div class="alert alert-light alert-permanent">
        <i class="bi bi-pencil me-2"></i>
        Ini pratinjau skor dari penilaian draft Anda
        ({{ $assessedElemen }} dari {{ $totalElemen }} elemen).
        Selesaikan elemen yang masih kosong di
        <a href="{{ route('al.berkas.show', $asesmen->id) }}" class="alert-link">halaman penilaian</a>
        sebelum bisa mengirim.
    </div>
    @endif

    @php
        $resumeBabs = $resume['bab'] ?? \App\Models\HasilAkreditasi::resumeAsesmenSkeleton()['bab'];
    @endphp

    <div class="card mb-4" id="cardResume">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-file-richtext"></i>
                Draft Resume Asesmen Akreditasi
            </h5>
            <span id="resumeStatusBadge">
                @if($resumeSaved)
                <span class="badge bg-success">Tersimpan</span>
                @else
                <span class="badge bg-warning text-dark">Belum disimpan</span>
                @endif
            </span>
        </div>
        <div class="card-body">
            <p class="text-muted small">Isi judul dan konten tiap BAB. Nomor sertifikat dan finalisasi tetap diisi sekretariat.</p>
            <div id="babContainer">
                @foreach($resumeBabs as $idx => $bab)
                @php
                    $babTitle = $bab['title'] ?? '';
                    $babContent = $bab['content'] ?? '';
                @endphp
                <div class="border rounded p-3 mb-3 bab-item">
                    <label class="form-label small fw-semibold">Judul BAB</label>
                    <input type="text" class="form-control form-control-sm mb-2 bab-title" value="{{ $babTitle }}" maxlength="120" {{ $canEdit ? '' : 'readonly' }}>

                    <label class="form-label small fw-semibold">Isi BAB</label>
                    <textarea class="form-control bab-content" rows="5" {{ $canEdit ? '' : 'readonly' }}>{!! $babContent !!}</textarea>
                </div>
                @endforeach
            </div>

            @if($canEdit)
            <div class="d-flex gap-2">
                <button type="button" id="btnSaveResume" class="btn btn-sm btn-primary">
                    <i class="bi bi-floppy"></i> Simpan Draft Resume
                </button>
            </div>
            <span id="saveStatus" class="small text-muted ms-1"></span>
            @endif
        </div>
    </div>

    @include('asesmen.components.batasan-skor-akreditasi', [
        'rentangSkor' => $rentangSkor ?? [],
        'skorMinimum' => $skorMinimumUnggul ?? null,
        'kriteriaKode' => $kriteriaRequired ?? ['D', 'E', 'P', 'I', 'L', 'A', 'R'],
        'showLkpsLink' => false,
        'currentSkor' => $displaySkor,
    ])

    @include('asesmen.components.validasi-syarat-unggul', [
        'validationSummary' => $validationSummary ?? [],
    ])

    @if(!empty($kriteriaList))
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
                        @foreach($kriteriaList as $kode => $data)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $kode }}</span></td>
                            <td>{{ $data['nama'] }}</td>
                            <td class="text-center">{{ $data['elemen_count'] }}</td>
                            <td class="text-center">{{ number_format($data['total_bobot'], 2) }}</td>
                            <td class="text-center">
                                <strong>{{ number_format($data['total_skor'], 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($data['has_pelampauan'])
                                <i class="bi bi-check-circle-fill text-dark fs-5"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted fs-5"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($elemenList))
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Detail Skor per Elemen Standar
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
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
                        @php $grouped = collect($elemenList)->groupBy('kode_kriteria'); @endphp
                        @foreach($grouped as $kodeKriteria => $items)
                        @php $rowspan = $items->count(); @endphp
                        @foreach($items as $index => $elemen)
                        @php $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'color' => '#e9ecef']; @endphp
                        <tr>
                            @if($index === 0)
                            <td rowspan="{{ $rowspan }}" class="align-middle text-center">
                                <span class="badge bg-secondary fs-6 py-2 px-3">{{ $kodeKriteria }}</span>
                            </td>
                            @endif
                            <td>
                                <code class="text-primary fw-bold">{{ $elemen['kode_elemen'] }}</code>
                                <span>{{ \Illuminate\Support\Str::limit($elemen['nama_elemen'], 120) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-wrap" style="width: 15rem; background-color: {{ $kategori['color'] }}; color: #222;">
                                    {{ $kategori['label'] === 'Melampaui Standar' ? 'Melampaui' : $kategori['label'] }}
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
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const idAsesmen = {{ (int) $asesmen->id }};

    @if($canEdit)
    const btnSave = document.getElementById('btnSaveResume');
    if (btnSave) {
        btnSave.addEventListener('click', async function() {
            const btn = this;
            const status = document.getElementById('saveStatus');
            btn.disabled = true;
            status.innerText = 'Menyimpan...';

            const bab = [];
            document.querySelectorAll('.bab-item').forEach(function(item) {
                const titleInput = item.querySelector('.bab-title');
                const textarea = item.querySelector('.bab-content');
                bab.push({
                    title: titleInput ? titleInput.value : '',
                    content: textarea ? textarea.value : ''
                });
            });

            try {
                const response = await fetch("{{ route('al.berkas.hasil.save-resume', $asesmen->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ bab })
                });
                const result = await response.json();
                if (!response.ok || !result.ok) {
                    throw new Error(result.message || 'Gagal menyimpan resume.');
                }
                status.innerText = result.message;
                setTimeout(function() {
                    window.location.reload();
                }, 700);
            } catch (e) {
                status.innerText = e.message;
                btn.disabled = false;
            }
        });
    }
    @endif

    async function kirimPenilaian() {
        const completed = {{ (int) $assessedElemen }};
        const total = {{ (int) $totalElemen }};

        if (completed < total) {
            await Swal.fire({
                icon: 'warning',
                title: 'Penilaian Belum Lengkap',
                html: `<p>Anda baru menilai <strong>${completed} dari ${total}</strong> elemen.</p>
                       <p class="text-danger">Selesaikan semua elemen sebelum mengirim.</p>`,
                confirmButtonText: 'Ke halaman penilaian',
                confirmButtonColor: '#932136'
            });
            window.location.href = "{{ route('al.berkas.show', $asesmen->id) }}";
            return;
        }

        const confirmed = await Swal.fire({
            icon: 'question',
            title: 'Kirim Penilaian?',
            html: `
                <div class="text-start">
                    <p>Anda akan mengirim penilaian ke sekretariat.</p>
                    <ul>
                        <li>Penilaian tidak bisa diedit sampai Anda membatalkannya</li>
                        <li>Kunci final tetap pada sekretariat</li>
                    </ul>
                    <p class="text-primary mb-0">
                        <i class="bi bi-info-circle"></i>
                        Total: <strong>${total} elemen</strong> telah dinilai
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-send"></i> Ya, Kirim Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            width: '600px'
        });

        if (!confirmed.isConfirmed) {
            return;
        }

        try {
            const response = await fetch("{{ route('al.berkas.submit', $asesmen->id) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Gagal mengirim penilaian');
            }
            await Swal.fire({
                icon: 'success',
                title: 'Penilaian Dikirim',
                text: data.message,
                confirmButtonColor: '#28a745'
            });
            window.location.reload();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengirim',
                text: error.message,
                confirmButtonColor: '#d33'
            });
        }
    }

    document.querySelectorAll('#btnKirimPenilaian, #btnKirimPenilaianAlert').forEach(function(btn) {
        btn.addEventListener('click', kirimPenilaian);
    });

    const btnUnsubmit = document.getElementById('btnUnsubmitHasil');
    if (btnUnsubmit) {
        btnUnsubmit.addEventListener('click', async function() {
            const confirmed = await Swal.fire({
                icon: 'warning',
                title: 'Batalkan penilaian?',
                html: '<p>Penilaian kembali ke draft. Setelah itu edit lewat tombol penilaian yang sudah ada.</p>',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            });
            if (!confirmed.isConfirmed) {
                return;
            }

            try {
                const response = await fetch("{{ route('al.berkas.unsubmit', $asesmen->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Gagal membatalkan penilaian.');
                }
                await Swal.fire({
                    icon: 'success',
                    title: 'Dibatalkan',
                    text: data.message,
                    confirmButtonColor: '#28a745'
                });
                window.location.href = "{{ route('al.berkas.show', $asesmen->id) }}";
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Batalkan',
                    text: error.message,
                    confirmButtonColor: '#d33'
                });
            }
        });
    }
</script>
@endpush
