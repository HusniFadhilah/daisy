{{-- resources/views/asesmen/ak/berkas/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Daftar Berkas Penilaian AK')

@section('content')
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
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="15%">Program Studi</th>
                            <th width="30%">Dokumen AK</th>
                            {{-- <th width="20%" class="text-center">Progress Penilaian</th> --}}
                            <th width="15%" class="text-left">Aksi Penilaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asesmens as $index => $asesmen)
                        @php
                        $statusInfo = $asesmen->statusInfo;
                        $assignment = $asesmen->userRoles->where('jenis_asesmen','ak')->first();
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
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    {{-- LED --}}
                                    @if($dokumenLED)
                                    <div class="d-flex align-items-center justify-content-between pt-2 rounded">
                                        <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumenLED->id) }}" class="btn btn-sm btn-success w-5" target="_blank" title="Download LED">
                                            <i class="bi bi-download"></i> Laporan Evaluasi Diri (LED)
                                        </a>
                                    </div>
                                    @else
                                    <div class="pt-2 rounded text-center w-5">
                                        <small class="text-muted">
                                            <i class="bi bi-file-earmark-x"></i> LED belum tersedia
                                        </small>
                                    </div>
                                    @endif

                                    {{-- Suplemen --}}
                                    @if($dokumenSuplemen)
                                    <div class="d-flex align-items-center justify-content-between pt-2 rounded">
                                        <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumenSuplemen->id) }}" class="btn btn-sm btn-success w-5" target="_blank" title="Download Suplemen">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                    @else
                                    <div class="pt-2 rounded text-center w-5">
                                        <small class="text-muted">
                                            <i class="bi bi-file-earmark-x"></i> Suplemen belum tersedia
                                        </small>
                                    </div>
                                    @endif

                                    {{-- LKPS --}}
                                    @if($dokumenLKPS)
                                    <div class="d-flex align-items-center justify-content-between pt-2 rounded">
                                        <a href="{{ route('upps.penerimaan-dokumen.dokumen.download', $dokumenLKPS->id) }}" class="btn btn-sm btn-success w-5" target="_blank" title="Download LKPS">
                                            <i class="bi bi-download"></i> Laporan Kinerja Program Studi (LKPS)
                                        </a>
                                    </div>
                                    @else
                                    <div class="pt-2 rounded text-center w-5">
                                        <small class="text-muted">
                                            <i class="bi bi-file-earmark-x"></i> LKPS belum tersedia
                                        </small>
                                    </div>
                                    @endif

                                    {{-- ✅ NEW: Download Template Penilaian --}}
                                    <div class="border-top pt-2 mt-1">
                                        <a href="{{ route('ak.berkas.export', ['idAsesmen' => $asesmen->id, 'mode' => 'template']) }}" class="btn btn-outline-primary btn-sm w-5" target="_blank">
                                            <i class="bi bi-file-earmark-excel"></i>
                                            Download Template Penilaian AK
                                        </a>
                                    </div>
                                </div>
                            </td>

                            {{-- Aksi Penilaian --}}
                            <td class="text-center">
                                @if($statusInfo['button_route'] ?? false)
                                {{-- Button ke route khusus (penawaran) --}}
                                <a href="{{ route($statusInfo['button_route'], ['idAsesmen'=>$asesmen->id,'jenisAsesmen'=>$assignment->jenis_asesmen]) }}" class="btn {{ $statusInfo['button_class'] }} w-100 mb-2" @if($statusInfo['button_disabled']) disabled @endif>
                                    <i class="{{ $statusInfo['button_icon'] }}"></i>
                                    {{ $statusInfo['button_text'] }}
                                </a>
                                @else
                                {{-- Button ke berkas show --}}
                                <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="btn btn-primary w-100 mb-2" @if($statusInfo['button_disabled']) disabled @endif>
                                    <i class="bi bi-pencil-square"></i>
                                    Penilaian <i>by System</i>
                                </a>
                                <small class="text-center text-muted mb-2 fw-semibold">
                                    — atau —
                                </small>
                                <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="btn btn-info w-100 mb-2" @if($statusInfo['button_disabled']) disabled @endif>
                                    <i class="bi bi-upload"></i>
                                    Penilaian Manual Excel
                                </a>
                                @endif

                                {{-- Secondary Action --}}
                                @if($assignment->status_penawaran === 'accepted')
                                <a href="{{ route('ak.berkas.show', $asesmen->id) }}" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-eye"></i>
                                    Cek Penilaian/Split
                                </a>
                                @endif
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
