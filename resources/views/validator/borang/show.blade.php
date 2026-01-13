{{-- resources/views/validator/borang/show.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Review LED - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .review-card {
        border-left: 4px solid #0d6efd;
    }

    .grade-btn {
        min-width: 100px;
    }

    .grade-btn.active {
        font-weight: bold;
    }

    .progress-circle {
        width: 100px;
        height: 100px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-clipboard-check"></i>
                Review LED
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->tahun_akreditasi }}
            </p>
        </div>
        <a href="{{ route('validator.borang.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Progress Overview --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">LED</h6>
                    <h3 class="mb-0">{{ $validation->reviewed_led }}/{{ $validation->total_elemen_led }}</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-primary" style="width: {{ $progress['led_percentage'] }}%"></div>
                    </div>
                    <small>{{ $progress['led_percentage'] }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Suplemen</h6>
                    <h3 class="mb-0">{{ $validation->reviewed_suplemen }}/{{ $validation->total_elemen_suplemen }}</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-info" style="width: {{ $progress['suplemen_percentage'] }}%"></div>
                    </div>
                    <small>{{ $progress['suplemen_percentage'] }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">LKPS</h6>
                    <h3 class="mb-0">{{ $validation->reviewed_lkps }}/{{ $validation->total_indikator_lkps }}</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: {{ $progress['lkps_percentage'] }}%"></div>
                    </div>
                    <small>{{ $progress['lkps_percentage'] }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Progress</h6>
                    <h3 class="mb-0">{{ $progress['percentage'] }}%</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-warning" style="width: {{ $progress['percentage'] }}%"></div>
                    </div>
                    <small>{{ $progress['reviewed'] }}/{{ $progress['total'] }} item</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Review Content --}}
    <div class="row">
        <div class="col-12">
            @foreach($kriterias as $kriteria)
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        {{ $kriteria->kode_kriteria }} - {{ $kriteria->pernyataan_kriteria }}
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($kriteria->elemenStandar as $elemen)
                    <div class="card review-card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <strong>{{ $elemen->kode_elemen }}</strong> - {{ $elemen->pernyataan_elemen }}
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- LED Review --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary">
                                    <i class="bi bi-file-earmark-text"></i> LED
                                </h6>
                                @include('validator.borang.partials.review-item', [
                                'category' => 'led',
                                'itemId' => $elemen->id,
                                'validation' => $validation,
                                ])
                            </div>

                            {{-- Suplemen Review --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-info">
                                    <i class="bi bi-file-earmark-plus"></i> Suplemen
                                </h6>
                                @include('validator.borang.partials.review-item', [
                                'category' => 'suplemen',
                                'itemId' => $elemen->id,
                                'validation' => $validation,
                                ])
                            </div>

                            {{-- LKPS Review (per indikator kuantitatif) --}}
                            @if($elemen->indikator->count() > 0)
                            <div>
                                <h6 class="fw-bold text-success">
                                    <i class="bi bi-table"></i> LKPS (Indikator Kuantitatif)
                                </h6>
                                @foreach($elemen->indikator as $indikator)
                                <div class="ms-3 mb-3">
                                    <p class="mb-2">
                                        <strong>{{ $indikator->kode_indikator }}</strong> - {{ $indikator->deskripsi_indikator }}
                                    </p>
                                    @include('validator.borang.partials.review-item', [
                                    'category' => 'lkps',
                                    'itemId' => $indikator->id,
                                    'validation' => $validation,
                                    ])
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Final Submission --}}
            @if($validation->isCompletelyReviewed())
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle"></i> Submit Validasi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('validator.borang.submit', $assignment->id) }}" method="POST" id="formSubmit">
                        @csrf

                        {{-- Catatan Umum --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Catatan Umum LED</label>
                                <textarea name="catatan_led" class="form-control" rows="3" placeholder="Catatan umum untuk LED...">{{ $validation->catatan_led }}</textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Catatan Umum Suplemen</label>
                                <textarea name="catatan_suplemen" class="form-control" rows="3" placeholder="Catatan umum untuk Suplemen...">{{ $validation->catatan_suplemen }}</textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Catatan Umum LKPS</label>
                                <textarea name="catatan_lkps" class="form-control" rows="3" placeholder="Catatan umum untuk LKPS...">{{ $validation->catatan_lkps }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Validator (Keseluruhan)</label>
                            <textarea name="catatan_validator" class="form-control" rows="4" placeholder="Catatan keseluruhan untuk prodi...">{{ $validation->catatan_validator }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Approve (Semua Grade C)
                            </button>
                            <button type="submit" name="action" value="revision" class="btn btn-warning btn-lg">
                                <i class="bi bi-exclamation-triangle"></i> Request Revision (Ada Grade A/B)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i>
                <strong>Review belum lengkap.</strong> Mohon review semua item sebelum submit.
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Form submit confirmation
    const formSubmit = document.getElementById('formSubmit')
    if (formSubmit) formSubmit.addEventListener('submit', function(e) {
        const action = e.submitter.value;
        const message = action === 'approve' ?
            'Approve validasi LED? Semua item harus grade C.' :
            'Request revision? Item dengan grade A/B akan dikirim ke prodi.';

        if (!confirm(message)) {
            e.preventDefault();
        }
    });

</script>
@endpush
@endsection
