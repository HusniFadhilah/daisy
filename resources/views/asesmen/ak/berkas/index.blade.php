{{-- resources/views/asesmen/ak/berkas/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Daftar Berkas Penilaian AK')

@section('content')

@push('styles')

<div class="container-fluid py-3">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-clipboard-check"></i> Daftar Berkas Penilaian AK
                    </h2>
                    <p class="mb-0 opacity-75">
                        Kelola dan lakukan penilaian asesmen kecukupan akreditasi program studi
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
                            <th width="5%">No</th>
                            <th width="20%">Program Studi</th>
                            <th width="30%">Dokumen AK</th>
                            <th width="25%">Aksi Penilaian</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($asesmens as $index => $asesmen)
                        @php
                        $statusInfo = $asesmen->statusInfo;
                        $assignment = $asesmen->userRoles->where('jenis_asesmen','ak')->first();
                        $pengajuan = $asesmen->pengajuan;

                        $suratTugas = $pengajuan?->dokumen
                        ->whereIn('jenis_dokumen',['surat_tugas_asesor_ak'])
                        ->where('is_latest', true)->first();

                        $dokumenLED = $pengajuan?->dokumen
                        ->whereIn('jenis_dokumen',['data_kualitatif','draft_borang','borang_final'])
                        ->where('is_latest', true)->first();

                        $dokumenSuplemen = $pengajuan?->dokumen
                        ->where('jenis_dokumen','data_suplemen')
                        ->where('is_latest', true)->first();

                        $dokumenLKPS = $pengajuan?->dokumen
                        ->whereIn('jenis_dokumen',['data_kuantitatif','kuantitatif'])
                        ->where('is_latest', true)->first();
                        @endphp

                        <tr>
                            {{-- No --}}
                            <td class="text-center">
                                {{ $asesmens->firstItem() + $index }}
                            </td>

                            {{-- Program Studi --}}
                            <td>
                                <strong>{{ $asesmen->studyProgram->name ?? '-' }}</strong><br>
                                <small class="text-muted">
                                    <i class="bi bi-building"></i>
                                    {{ $asesmen->studyProgram->university->name ?? '-' }}
                                </small>
                            </td>

                            {{-- Dokumen AK --}}
                            <td class="text-center">
                                <div class="btn-stack">

                                    @if($suratTugas)
                                    <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $suratTugas->id) }}" class="btn btn-sm btn-success btn-fixed-lg mb-3" target="_blank">
                                        <i class="bi bi-download me-1"></i>
                                        Surat Tugas Asesor AK
                                    </a>
                                    @else
                                    <div class="btn-fixed-lg text-muted">
                                        <i class="bi bi-file-earmark-x me-1"></i>
                                        Surat Tugas belum tersedia
                                    </div>
                                    @endif

                                    {{-- LED --}}
                                    @if($dokumenLED)
                                    <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumenLED->id) }}" class="btn btn-sm btn-info btn-fixed-lg" target="_blank">
                                        <i class="bi bi-download me-1"></i>
                                        Laporan Evaluasi Diri (LED)
                                    </a>
                                    @else
                                    <div class="btn-fixed-lg text-muted">
                                        <i class="bi bi-file-earmark-x me-1"></i>
                                        LED belum tersedia
                                    </div>
                                    @endif

                                    {{-- Suplemen --}}
                                    @if($dokumenSuplemen)
                                    <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumenSuplemen->id) }}" class="btn btn-sm btn-light btn-fixed-lg" target="_blank">
                                        <i class="bi bi-download"></i> Suplemen LED
                                    </a>
                                    @else
                                    <div class="btn-fixed-lg text-muted">
                                        <i class="bi bi-file-earmark-x me-1"></i>
                                        Suplemen belum tersedia
                                    </div>
                                    @endif

                                    {{-- LKPS --}}
                                    @if($dokumenLKPS)
                                    <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumenLKPS->id) }}" class="btn btn-sm btn-success btn-fixed-lg" target="_blank">
                                        <i class="bi bi-download me-1"></i>
                                        Laporan Kinerja Program Studi (LKPS)
                                    </a>
                                    @else
                                    <div class="btn-fixed-lg text-muted">
                                        <i class="bi bi-file-earmark-x me-1"></i>
                                        LKPS belum tersedia
                                    </div>
                                    @endif

                                    {{-- Template --}}
                                    <div class="border-top pt-2">
                                        <a href="{{ route('ak.berkas.export',['idAsesmen'=>$asesmen->id,'mode'=>'template']) }}" class="btn btn-outline-primary btn-sm btn-fixed-lg" target="_blank">
                                            <i class="bi bi-file-earmark-excel me-1"></i>
                                            Download Template Penilaian AK
                                        </a>
                                    </div>

                                </div>
                            </td>

                            {{-- Aksi Penilaian --}}
                            <td class="text-center">
                                <div class="btn-stack">

                                    @if($statusInfo['button_route'] ?? false)
                                    <a href="{{ route($statusInfo['button_route'], ['idAsesmen'=>$asesmen->id,'jenisAsesmen'=>$assignment->jenis_asesmen]) }}" class="btn {{ $statusInfo['button_class'] }} btn-fixed" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="{{ $statusInfo['button_icon'] }}"></i>
                                        {{ $statusInfo['button_text'] }}
                                    </a>
                                    @else
                                    <a href="{{ route('ak.berkas.show',$asesmen->id) }}" class="btn btn-primary btn-fixed" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-pencil-square"></i>
                                        Penilaian <i>by System</i>
                                    </a>

                                    <div class="text-muted">— atau —</div>

                                    <a href="{{ route('ak.berkas.upload-excel', $asesmen->id) }}" class="btn btn-info btn-fixed" @if($statusInfo['button_disabled']) disabled @endif>
                                        <i class="bi bi-upload"></i>
                                        Penilaian Manual Excel
                                    </a>
                                    @endif

                                    <div class="border-top pt-2">
                                        @if($assignment->status_penawaran === 'accepted')
                                        <a href="{{ route('ak.berkas.export', ['idAsesmen'=>$asesmen->id,'mode'=>'full','color'=>false]) }}" class="btn btn-secondary btn-sm btn-fixed" target="_blank">
                                            <i class="bi bi-eye"></i>
                                            Cek Penilaian / Split
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <h5 class="mt-3 text-muted">Belum Ada Asesmen</h5>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-2">
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
        <div class="card-footer">
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
