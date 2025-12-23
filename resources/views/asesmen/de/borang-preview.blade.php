@extends('layouts.template.app')

@section('title', 'Preview Data Borang - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .completeness-bar {
        height: 30px;
        border-radius: 15px;
        overflow: hidden;
    }

    .section-card {
        border-left: 4px solid #0d6efd;
    }

    .field-row {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .field-row:last-child {
        border-bottom: none;
    }

    .field-empty {
        background: #fff3cd;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Preview Data Borang
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->nomor_pengajuan }} - {{ $pengajuan->studyProgram->name }}
            </p>
        </div>
        <a href="{{ route('de.pengajuan.show', $pengajuan->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Completeness Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        Kelengkapan Data:
                        <span class="badge {{ $borangData->status_badge_class }}">
                            {{ $borangData->completeness_label }}
                        </span>
                    </h5>
                    <div class="progress completeness-bar">
                        <div class="progress-bar
                            {{ $borangData->completeness_percentage >= 80 ? 'bg-success' :
                               ($borangData->completeness_percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $borangData->completeness_percentage }}%">
                            {{ $borangData->completeness_percentage }}%
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block">
                        <i class="bi bi-clock"></i>
                        Diproses: {{ $borangData->parsed_at->format('d M Y H:i') }}
                    </small>
                    <small class="text-muted d-block">
                        <i class="bi bi-person"></i>
                        Oleh: {{ $borangData->parser->name }}
                    </small>
                </div>
            </div>

            @if($borangData->missing_fields && count($borangData->missing_fields) > 0)
            <div class="alert alert-warning alert-permanent mt-3 mb-0">
                <strong><i class="bi bi-exclamation-triangle"></i> Field yang Belum Diisi:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($borangData->missing_fields as $field)
                    <li>{{ ucwords(str_replace('_', ' ', $field)) }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Identitas -->
            <div class="card section-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Identitas Program Studi</h5>
                </div>
                <div class="card-body">
                    <div class="field-row {{ empty($borangData->nama_program_studi) ? 'field-empty' : '' }}">
                        <strong>Nama Program Studi:</strong>
                        <p class="mb-0">{{ $borangData->nama_program_studi ?? '-' }}</p>
                    </div>
                    <div class="field-row {{ empty($borangData->universitas) ? 'field-empty' : '' }}">
                        <strong>Universitas:</strong>
                        <p class="mb-0">{{ $borangData->universitas ?? '-' }}</p>
                    </div>
                    <div class="field-row {{ empty($borangData->bulan_tahun) ? 'field-empty' : '' }}">
                        <strong>Bulan, Tahun:</strong>
                        <p class="mb-0">{{ $borangData->bulan_tahun ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Institusi Penjaminan Mutu -->
            <div class="card section-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Institusi Penjaminan Mutu</h5>
                </div>
                <div class="card-body">
                    <div class="field-row">
                        <strong>Nama Institusi:</strong>
                        <p class="mb-0">{{ $borangData->nama_institusi_penjaminan_mutu ?? '-' }}</p>
                    </div>
                    <div class="field-row">
                        <strong>Telp:</strong>
                        <p class="mb-0">{{ $borangData->telp_institusi ?? '-' }}</p>
                    </div>
                    <div class="field-row">
                        <strong>Mobile/WA:</strong>
                        <p class="mb-0">{{ $borangData->mobile_institusi ?? '-' }}</p>
                    </div>
                    <div class="field-row">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $borangData->email_institusi ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- PIC Akreditasi -->
            <div class="card section-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> PIC Akreditasi</h5>
                </div>
                <div class="card-body">
                    <div class="field-row {{ empty($borangData->nama_pic) ? 'field-empty' : '' }}">
                        <strong>Nama PIC:</strong>
                        <p class="mb-0">{{ $borangData->nama_pic ?? '-' }}</p>
                    </div>
                    <div class="field-row">
                        <strong>Telp:</strong>
                        <p class="mb-0">{{ $borangData->telp_pic ?? '-' }}</p>
                    </div>
                    <div class="field-row">
                        <strong>Mobile/WA:</strong>
                        <p class="mb-0">{{ $borangData->mobile_pic ?? '-' }}</p>
                    </div>
                    <div class="field-row {{ empty($borangData->email_pic) ? 'field-empty' : '' }}">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $borangData->email_pic ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Konten Evaluasi -->
            <div class="card section-card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Konten Evaluasi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Kata Pengantar:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->kata_pengantar) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->kata_pengantar ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Ringkasan:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->ringkasan) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->ringkasan ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                    <div class="mb-0">
                        <strong>Diferensiasi Misi:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->diferensiasi_misi) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->diferensiasi_misi ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visi, Misi, Tujuan -->
            <div class="card section-card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-bullseye"></i> Visi, Misi, Tujuan, Strategi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Visi:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->visi) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->visi ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Misi:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->misi) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->misi ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Tujuan:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->tujuan) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->tujuan ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                    <div class="mb-0">
                        <strong>Strategi:</strong>
                        <div class="border rounded p-2 mt-1 {{ empty($borangData->strategi) ? 'bg-light' : '' }}">
                            <small>{{ $borangData->strategi ?? 'Belum diisi' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
