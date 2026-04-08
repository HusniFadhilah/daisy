{{-- resources/views/asesmen/banding/al-banding/berkas/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Daftar Berkas Penilaian AL Banding')


@push('styles')
<style>
    .status-checklist {
        font-size: 0.8rem;
    }

    .status-checklist .item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 3px 0;
        color: #6c757d;
    }

    .status-checklist .item.done {
        color: #198754;
    }

    .status-checklist .item.active {
        color: #0d6efd;
    }

    .status-checklist .item i {
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .status-checklist .divider {
        border-top: 1px dashed #dee2e6;
        margin: 4px 0;
    }

    .owner-badge {
        font-size: 0.72rem;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-clipboard-check"></i> Daftar Berkas Penilaian AL Banding
                    </h2>
                    <p class="mb-0 opacity-75">
                        Kelola dan lakukan penilaian asesmen lapangan banding dari program studi
                    </p>
                </div>
                <div class="text-end">
                    <h3 class="mb-0">{{ $asesmens->total() }}</h3>
                    <small>Total Asesmen</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="4%" class="text-center">No</th>
                            <th width="14%">Program Studi</th>
                            <th width="22%">Status Penilaian</th>
                            <th width="20%">Dokumen AL Banding</th>
                            <th width="22%" class="text-center">Aksi Penilaian</th>
                            <th width="18%" class="text-center">Laporan Asesmen Lapangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asesmens as $index => $asesmen)
                        @php
                        $statusInfo = $asesmen->statusInfo;
                        $assignment = $asesmen->userRoles->where('jenis_asesmen','al_banding')->first();
                        $firstOpener = $asesmen->firstOpenerRole;
                        $myUserId = Auth::id();
                        $iAmTheOpener = $firstOpener && $firstOpener->id_user == $myUserId;
                        $nobodyYet = is_null($firstOpener);
                        $blockMe = !$nobodyYet && !$iAmTheOpener;

                        // Status penilaian — hanya dari first opener
                        $penilaianStatus = $asesmen->penilaian_status; // belum/on_progress/selesai

                        // Status dokumen
                        $hasBeritaAcara = $asesmen->has_berita_acara;
                        $lhaStatus = $asesmen->lha_status; // null/revision/pending/approved

                        // Semua tahap wajib selesai?
                        $allDone = $penilaianStatus === 'selesai'
                        && $hasBeritaAcara
                        && $lhaStatus === 'approved';
                        @endphp
                        <tr>
                            {{-- No --}}
                            <td class="text-center">
                                {{ $asesmens->firstItem() + $index }}
                            </td>

                            {{-- Program Studi --}}
                            <td>
                                <strong>{{ $asesmen->studyProgram->name ?? '-' }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-building"></i>
                                    {{ $asesmen->studyProgram->university->name ?? '-' }}
                                </small>
                            </td>

                            {{-- Status Penilaian (kolom utama yang diperluas) --}}
                            <td>
                                {{-- Siapa pengisi --}}
                                <div class="mb-2 text-center">
                                    @if($nobodyYet)
                                    <span class="badge bg-light text-dark owner-badge">
                                        <i class="bi bi-hourglass-split"></i> Belum Ada Penilaian
                                    </span>
                                    @elseif($iAmTheOpener)
                                    <small>Penilaian diupload oleh:</small>
                                    <span class="badge bg-light text-dark owner-badge">
                                        <i class="bi bi-person-check-fill"></i> Anda — Pengisi Penilaian
                                    </span>
                                    @else
                                    <small>Penilaian diupload oleh:</small>
                                    <span class="badge bg-light text-dark owner-badge">
                                        <i class="bi bi-lock-fill"></i> {{ $firstOpener->user->name ?? 'Asesor Lain' }}
                                    </span>
                                    @endif
                                </div>

                                {{-- Checklist status tahapan --}}
                                <div class="status-checklist">

                                    {{-- 1. Penilaian Elemen --}}
                                    @php
                                    $penilaianClass = match($penilaianStatus) {
                                    'selesai' => 'done',
                                    'on_progress' => 'active',
                                    default => '',
                                    };
                                    $penilaianIcon = match($penilaianStatus) {
                                    'selesai' => 'bi-check-circle-fill text-success',
                                    'on_progress' => 'bi-arrow-repeat text-primary',
                                    default => 'bi-circle text-secondary',
                                    };
                                    $penilaianLabel = match($penilaianStatus) {
                                    'selesai' => 'Penilaian Selesai',
                                    'on_progress' => 'Penilaian Sedang Berjalan',
                                    default => 'Penilaian Belum Dimulai',
                                    };
                                    @endphp
                                    <div class="item {{ $penilaianClass }}">
                                        <i class="bi {{ $penilaianIcon }}"></i>
                                        <span>{{ $penilaianLabel }}</span>
                                    </div>

                                    <div class="divider"></div>

                                    {{-- 2. Berita Acara AL --}}
                                    <div class="item {{ $hasBeritaAcara ? 'done' : '' }}">
                                        <i class="bi {{ $hasBeritaAcara ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }}"></i>
                                        <span>Berita Acara AL Banding</span>
                                    </div>

                                    {{-- 3. LHA (Laporan Hasil Surveillance Banding) --}}
                                    @php
                                    [$lhaItemClass, $lhaIcon, $lhaLabel] = match($lhaStatus) {
                                    'approved' => [
                                    'done',
                                    'bi-check-circle-fill text-success',
                                    'Laporan Hasil Surveillance Banding',
                                    ],
                                    'revision_required' => [
                                    'active text-dark',
                                    'bi-exclamation-circle-fill text-warning',
                                    'Laporan Hasil Surveillance Banding — Permintaan Revisi',
                                    ],
                                    'rejected' => [
                                    'active text-dark',
                                    'bi-x-circle-fill text-danger',
                                    'Laporan Hasil Surveillance Banding — Ditolak',
                                    ],
                                    'pending' => [
                                    'active text-dark',
                                    'bi-clock-fill text-info',
                                    'Laporan Hasil Surveillance Banding — Menunggu Persetujuan Prodi',
                                    ],
                                    default => [ // null = belum ada dokumen
                                    '',
                                    'bi-circle text-secondary',
                                    'Laporan Hasil Surveillance Banding',
                                    ],
                                    };
                                    @endphp
                                    <div class="item {{ $lhaItemClass }}">
                                        <i class="bi {{ $lhaIcon }}"></i>
                                        <span>{{ $lhaLabel }}</span>
                                    </div>

                                </div>

                                {{-- Badge ringkasan "Semua Selesai" --}}
                                @if($allDone)
                                <div class="mt-2 text-center">
                                    <span class="badge bg-success">
                                        <i class="bi bi-patch-check-fill me-1"></i>Semua Tahap Selesai
                                    </span>
                                </div>
                                @endif
                            </td>

                            {{-- Dokumen AL --}}
                            <td class="text-center">
                                <div class="btn-stack">
                                    <a href="{{ route('ak_banding.berkas.export', ['idAsesmen'=>$asesmen->id,'mode'=>'full','color'=>false]) }}" class="btn btn-outline-success btn-sm btn-width-180" target="_blank">
                                        <i class="bi bi-file-earmark-excel me-1"></i>Hasil Penilaian AK Banding
                                    </a>
                                    <a href="{{ route('al_banding.berkas.export', ['idAsesmen'=>$asesmen->id,'mode'=>'template']) }}" class="btn btn-outline-primary btn-sm btn-width-180" target="_blank">
                                        <i class="bi bi-file-earmark-excel me-1"></i>Download Templat Penilaian AL Banding
                                    </a>
                                </div>
                            </td>

                            {{-- Aksi Penilaian --}}
                            <td class="text-center">
                                <div class="btn-stack">
                                    @if($blockMe)
                                    <button class="btn btn-secondary btn-sm btn-width-180" disabled>
                                        <i class="bi bi-lock"></i> Penilaian Dikunci
                                    </button>
                                    <small class="text-muted">
                                        Diisi oleh {{ $firstOpener->user->name ?? 'asesor lain' }} <a href="{{ route('al_banding.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'personal_al_banding', 'color' => false]) }}" target="_blank"><i class="bi bi-eye"></i></a>
                                    </small>
                                    <a href="{{ route('al_banding.berkas.documents.page', ['id' => $asesmen->id]) }}" class="btn btn-outline-secondary btn-sm btn-width-180">
                                        <i class="bi bi-file-earmark-text"></i> Lihat Berita Acara Banding
                                    </a>
                                    @elseif($statusInfo['button_route'] ?? false)
                                    <a href="{{ route($statusInfo['button_route'], ['idAsesmen'=>$asesmen->id,'jenisAsesmen'=>$assignment->jenis_asesmen]) }}" class="btn {{ $statusInfo['button_class'] }} btn-sm btn-width-180" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="{{ $statusInfo['button_icon'] }}"></i>
                                        {{ $statusInfo['button_text'] }}
                                    </a>
                                    @else
                                    <a href="{{ route('al_banding.berkas.show', $asesmen->id) }}" class="btn btn-primary btn-sm btn-width-180" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-pencil-square"></i> Penilaian <i>by System</i>
                                    </a>
                                    <div class="text-muted fw-semibold">— atau —</div>
                                    <a href="{{ route('al_banding.berkas.upload-excel', $asesmen->id) }}" class="btn btn-info btn-sm btn-width-180" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-upload"></i> Penilaian dengan Form Excel
                                    </a>
                                    <a href="{{ route('al_banding.berkas.documents.page', ['id' => $asesmen->id]) }}" class="btn btn-success btn-sm btn-width-180" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-file-earmark-text"></i> Berita Acara AL Banding
                                    </a>
                                    @endif
                                </div>
                            </td>

                            {{-- Laporan --}}
                            <td class="text-center">
                                <div class="btn-stack mb-4">
                                    <a href="{{ route('al_banding.berkas.lha-asesor.page', ['idAsesmen' => $asesmen->id]) }}" class="btn btn-blue btn-sm btn-width-180" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-file-earmark-text me-1"></i>Laporan Surveilance Penanganan Banding
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #e0e0e0;"></i>
                                <h5 class="mt-3 text-muted">Belum Ada Asesmen</h5>
                                <p class="text-muted mb-3">
                                    Belum ada berkas penilaian yang ditugaskan kepada Anda.
                                </p>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                    <i class="bi bi-house"></i> Kembali ke Dashboard
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination in Card Footer --}}
        @if($asesmens->hasPages())
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Menampilkan {{ $asesmens->firstItem() }} - {{ $asesmens->lastItem() }} dari {{ $asesmens->total() }} asesmen
                </div>
                <div>
                    {{ $asesmens->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
