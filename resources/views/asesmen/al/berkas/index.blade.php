{{-- resources/views/asesmen/al/berkas/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Daftar Berkas Penilaian AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-clipboard-check"></i> Daftar Berkas Penilaian AL
                    </h2>
                    <p class="mb-0 opacity-75">
                        Kelola dan lakukan penilaian asesmen lapangan akreditasi program studi
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
                            <th width="5%" class="text-center">No</th>
                            <th width="15%">Program Studi</th>
                            <th width="25%">Dokumen AL</th>
                            {{-- <th width="20%" class="text-center">Progress Penilaian</th> --}}
                            <th width="25%" class="text-center">Aksi Penilaian</th>
                            <th width="25%" class="text-center">Laporan Asesmen Lapangan (LHA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asesmens as $index => $asesmen)
                        @php
                        $statusInfo = $asesmen->statusInfo;
                        $assignment = $asesmen->userRoles->where('jenis_asesmen','al')->first();
                        $pengajuan = $asesmen->pengajuan;

                        // Get dokumen berkas akreditasi
                        $dokumenLED = $pengajuan->dokumen
                        ->whereIn('jenis_dokumen', ['data_kualitatif', 'draft_borang', 'borang_final'])
                        ->where('is_latest', true)
                        ->first();

                        // ✅ FIX: Suplemen menggunakan data_suplemen
                        $dokumenSuplemen = $pengajuan->dokumen
                        ->where('jenis_dokumen', 'data_suplemen')
                        ->where('is_latest', true)
                        ->first();

                        $dokumenLKPS = $pengajuan->dokumen
                        ->whereIn('jenis_dokumen', ['data_kuantitatif', 'kuantitatif'])
                        ->where('is_latest', true)
                        ->first();
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

                            {{-- Daftar Berkas Akreditasi --}}
                            <td class="text-center">
                                <div class="btn-stack">

                                    <a href="{{ route('ak.berkas.export', ['idAsesmen'=>$asesmen->id,'mode'=>'full','color'=>false]) }}" class="btn btn-outline-success btn-sm btn-fixed-sm" target="_blank">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Hasil Penilaian AK
                                    </a>

                                    <a href="{{ route('al.berkas.export', ['idAsesmen'=>$asesmen->id,'mode'=>'template']) }}" class="btn btn-outline-primary btn-sm btn-fixed-sm" target="_blank">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Download Template Penilaian AL
                                    </a>

                                </div>
                            </td>

                            {{-- Aksi Penilaian --}}
                            <td class="text-center">
                                <div class="btn-stack">

                                    @if($statusInfo['button_route'] ?? false)
                                    <a href="{{ route($statusInfo['button_route'], ['idAsesmen'=>$asesmen->id,'jenisAsesmen'=>$assignment->jenis_asesmen]) }}" class="btn {{ $statusInfo['button_class'] }} btn-fixed" @if($statusInfo['button_disabled']) disabled @endif> <i class="{{ $statusInfo['button_icon'] }}"></i> {{ $statusInfo['button_text'] }}
                                    </a>

                                    @else
                                    <a href="{{ route('al.berkas.show',$asesmen->id) }}" class="btn btn-primary btn-fixed" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-pencil-square"></i>
                                        Penilaian <i>by System</i>
                                    </a>

                                    <div class="text-muted fw-semibold">— atau —</div>

                                    <a href="{{ route('al.berkas.upload-excel', $asesmen->id) }}" class="btn btn-info btn-fixed" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-upload"></i>
                                        Penilaian dengan Form Excel
                                    </a>

                                    <a href="{{ route('al.berkas.show',$asesmen->id) }}" class="btn btn-success btn-fixed" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-file-earmark-text"></i>
                                        Berita Acara AL
                                    </a>
                                    @endif

                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-stack">
                                    <a href="{{ route('al.berkas.show',$asesmen->id) }}" class="btn btn-blue btn-fixed-sm" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-file-earmark-text me-1"></i>
                                        Laporan Hasil Asesmen
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
