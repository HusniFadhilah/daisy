@extends('layouts.template.app')

@section('title', 'Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2>
                <i class="bi bi-file-earmark-check text-white"></i>
                Validasi Dokumen
            </h2>
            <p class="mb-0">Daftar permohonan akreditasi yang Anda validasi sebagai Validator Dokumen</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="text-link">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active">Validasi Dokumen</li>
        </ol>
    </nav>

    <!-- Stats Cards -->
    <div class="row mb-4 row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <x-stat-card title="Menunggu Validasi" :value="$stats['pending']" icon="clock-history" mode="white" description="" color="warning" />
        </div>

        <div class="col">
            <x-stat-card title="Sedang Divalidasi" :value="$stats['in_review']" icon="eye" mode="white" description="" color="info" />
        </div>

        <div class="col">
            <x-stat-card title="Perlu Revisi" :value="$stats['revision']" icon="exclamation-triangle" mode="white" description="" color="danger" />
        </div>

        <div class="col">
            <x-stat-card title="Disetujui" :value="$stats['approved']" icon="check-circle" mode="white" description="" color="success" />
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Daftar Validasi Dokumen
                </h5>
                <small class="text-muted">Total: {{ $assignments->total() }}</small>
            </div>
        </div>

        <div class="card-body p-0">
            @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Permohonan Akreditasi</th>
                            <th width="25%">Program Studi</th>
                            <th width="20%">Status Validasi</th>
                            <th width="15%">Tanggal Validasi</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($assignments as $index => $assignment)
                        @php
                        $pengajuan = $assignment->pengajuan;

                        $judul = '-';
                        $nomor = '-';
                        $createdAt = null;

                        if ($pengajuan) {
                        $judul = $pengajuan->judul_short;
                        $nomor = $pengajuan->nomor_pengajuan;
                        $createdAt = $pengajuan->created_at;
                        }

                        $prodi = '-';
                        $univ = '-';
                        if ($pengajuan && isset($pengajuan->studyProgram) && $pengajuan->studyProgram) {
                        $prodi = $pengajuan->studyProgram->name;
                        if (isset($pengajuan->studyProgram->university) && $pengajuan->studyProgram->university) {
                        $univ = $pengajuan->studyProgram->university->name;
                        }
                        }

                        $tglValidasi = null;
                        if ($pengajuan && !empty($pengajuan->tanggal_validasi_borang_selesai)) {
                        $tglValidasi = $pengajuan->tanggal_validasi_borang_selesai;
                        }
                        @endphp

                        <tr>
                            <td>{{ $assignments->firstItem() + $index }}</td>

                            <td>
                                <p class="mb-0">{{ $judul }}</p>
                                <small class="text-muted">{{ $nomor }}</small>
                                <br>
                                <small class="text-muted">
                                    Dibuat pada:
                                    {{ $createdAt ? \App\Libraries\Date::tglIndo($createdAt) : '-' }}
                                </small>
                            </td>

                            <td>
                                <p class="mb-0">{{ $prodi }}</p>
                                <small class="text-muted">{{ $univ }}</small>
                            </td>

                            <td>
                                @if($pengajuan)
                                {!! $pengajuan->getCustomBadgeLastStatus('validasi_dokumen', 'upps', 'label_short_for') !!}
                                @else
                                <span class="badge bg-secondary">-</span>
                                @endif
                            </td>

                            <td>
                                @if($tglValidasi)
                                <small>{{ $tglValidasi->format('d M Y H:i') }}</small>
                                <br>
                                <small class="text-muted">{{ $tglValidasi->diffForHumans() }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-sm btn-primary" title="Lihat Validasi">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white">
                {{ $assignments->links() }}
            </div>

            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size:64px;color:#ddd;"></i>
                <p class="text-muted mt-3 mb-0">Belum ada dokumen yang ditugaskan untuk Anda validasi.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/pelaporan.js') }}"></script>
<script>
    window.PELAPORAN_CFG = {
        borang: {
            title: 'Pelaporan Validasi Dokumen'
            , label: 'Laporan Kesiapan LED Program Studi (LKLED)'
            , upload: @json(route('pelaporan.borang.upload', ['assignment' => '__ID__']))
            , finalize: @json(route('pelaporan.borang.finalize', ['assignment' => '__ID__']))
            , fileLabel: 'Laporan Kesiapan LED Program Studi (LKLED)'
            , finalizeLabel: 'Laporan Kesiapan LED Program Studi (LKLED)'
            , additionalDescription: `Dokumen yang telah digabungkan, yang diperlukan isinya adalah:
• Surat Permohonan PS untuk Akreditasi
• Surat Balasan DE untuk menyusun LED
• Bukti Pembayaran Akreditasi
• Dokumen LED yang telah memenuhi standar untuk dilakukan Penilaian Kecukupan (AK)`
        }
    };

</script>
@endpush
