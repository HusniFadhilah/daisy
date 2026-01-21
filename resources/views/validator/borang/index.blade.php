{{-- resources/views/validator/borang/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Validasi Dokumen Akreditasi</h3>
                    <p class="text-muted mb-0">Daftar Permohonan akreditasi yang Anda validasi sebagai Validator Dokumen</p>
                </div>
                <a href="{{ route('penawaran') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 mb-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Menunggu Validasi</h6>
                            <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Sedang Review</h6>
                            <h2 class="mb-0">{{ $stats['in_review'] }}</h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-eye text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Perlu Revisi</h6>
                            <h2 class="mb-0">{{ $stats['revision'] }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Disetujui</h6>
                            <h2 class="mb-0">{{ $stats['approved'] }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment List --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Daftar Dokumen</h5>
        </div>
        <div class="card-body">
            @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Pengajuan</th>
                            <th>Program Studi</th>
                            <th>Jenjang</th>
                            <th>Status Dokumen</th>
                            <th>Status Validasi Dokumen</th>
                            <th>Tanggal Ditugaskan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                        @php
                        $pengajuan = $assignment->pengajuan;
                        $statusBadge = [
                        'not_started' => '<span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Belum Dimulai</span>',
                        'in_progress' => '<span class="badge bg-info"><i class="bi bi-eye"></i> Sedang Review</span>',
                        'revision_required' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Prodi Perlu Revisi</span>',
                        'approved' => '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Disetujui Validator</span>',
                        ];
                        $badgePelaporan = $pengajuan? $pengajuan->getPelaporanBadge($assignment->jenis_asesmen): null;
                        @endphp
                        <tr>
                            <td>
                                {{ $pengajuan->judul }}
                                <small>No: {{ $pengajuan->nomor_pengajuan }}</small>
                            </td>
                            <td>
                                {{ $pengajuan->studyProgram->name }}
                                <br>
                                <small class="text-muted">{{ $pengajuan->studyProgram->university->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $pengajuan->studyProgram->degreeLevel->name }}</span>
                            </td>
                            <td>
                                @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI)
                                <span class="badge bg-success">Selesai Diisi</span>
                                @elseif($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
                                <span class="badge bg-warning text-dark text-wrap">Dokumen LED+Suplemen dan LKPS Perlu Revisi</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($pengajuan->status_label) }}</span>
                                @endif
                            </td>
                            <td>
                                {!! $statusBadge[$assignment->status_pekerjaan] !!}
                                @if($badgePelaporan)
                                <span class="badge bg-success text-wrap mt-2">
                                    <i class="bi bi-check-circle"></i>
                                    {{ $badgePelaporan }}
                                </span>
                                @endif
                            </td>
                            <td>
                                {{ $assignment->created_at ? $assignment->created_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td>
                                @if(in_array($pengajuan->status,[\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING]))
                                <a href="{{ route('penawaran.show', ['token' => $assignment->token]) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Lihat Penawaran
                                </a>
                                @else
                                <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Lihat Validasi
                                </a>
                                @endif

                                @if(in_array($pengajuan->status, [
                                \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                                \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_FINAL_DITERIMA,
                                ]))
                                @if(is_null($pengajuan->tanggal_pelaporan_validasi_borang))
                                <button type="button" class="btn btn-sm btn-success mt-2 js-open-pelaporan" data-type="borang" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $pengajuan->nomor_pengajuan }}"> <i class="bi bi-file-earmark-text"></i> Pelaporan Validasi </button>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $assignments->links() }}
            </div>
            @else
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-info-circle"></i> Belum ada dokumen yang ditugaskan untuk Anda validasi.
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
            title: 'Pelaporan Validasi LED+Suplemen, dan LKPS'
            , label: 'Laporan Kesiapan LED Program Studi (LKLED)'
            , upload: @json(route('pelaporan.borang.upload', ['assignment' => '__ID__']))
            , finalize: @json(route('pelaporan.borang.finalize', ['assignment' => '__ID__']))
            , fileLabel: 'Laporan Kesiapan LED Program Studi (LKLED)'
            , finalizeLabel: 'Laporan Kesiapan LED Program Studi (LKLED)'
            , additionalDescription: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
            • Surat Permohonan PS untuk Akreditasi
            • Surat Balasan DE untuk menyusun LED
            • Bukti Pembayaran Akreditasi
            • Dokumen LED yang telah memenuhi standar untuk dilakukan Penilaian Kecukupan (AK)`
        }
    };

</script>
@endpush
