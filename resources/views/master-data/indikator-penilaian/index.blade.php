@extends('layouts.template.app')

@section('title', 'Indikator Penilaian Elemen - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2>Indikator Penilaian Elemen</h2>
            <p class="text-muted">Kelola indikator penilaian untuk setiap elemen standar</p>
        </div>
        <a href="{{ route('indikator-penilaian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Indikator
        </a>
    </div>

    <!-- Accordion untuk setiap Elemen Standar -->
    <div class="accordion" id="indikatorAccordion">
        @forelse($elemenStandars as $elemen)
        @php
        $indikatorsByElemen = $groupedIndikators->get($elemen->id) ?? collect();
        @endphp

        <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="heading{{ $elemen->id }}">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $elemen->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                    <strong>{{ $elemen->kode_elemen }}</strong>
                    <span class="ms-2">{{ $elemen->pernyataan_elemen }}</span>
                    <span class="badge bg-info ms-auto me-2">{{ $indikatorsByElemen->count() }} indikator</span>
                </button>
            </h2>
            <div id="collapse{{ $elemen->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#indikatorAccordion">
                <div class="accordion-body">
                    @if($indikatorsByElemen->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Skor</th>
                                    <th style="width: 150px;">Jenjang</th>
                                    <th>Deskripsi Penilaian</th>
                                    <th>Keterangan</th>
                                    <th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($indikatorsByElemen->sortBy('jenjangPenilaian.skor') as $indikator)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $indikator->jenjangPenilaian->skor }}</span>
                                    </td>
                                    <td>{{ $indikator->jenjangPenilaian->name }}</td>
                                    <td>{{ Str::limit($indikator->deskripsi_penilaian, 100) }}</td>
                                    <td>{{ $indikator->keterangan ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('indikator-penilaian.edit', $indikator->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form id="form-indikator" action="{{ route('indikator-penilaian.destroy', $indikator->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger tombol-hapus" data-id-form="form-indikator" data-text="indikator" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2">Belum ada indikator untuk elemen ini</p>
                        <a href="{{ route('indikator-penilaian.create') }}?elemen={{ $elemen->id }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i> Tambah Indikator
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada elemen standar. Silakan tambahkan elemen standar terlebih dahulu.
        </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0c63e4;
    }

    .accordion-button {
        font-size: 0.95rem;
    }

    .accordion-body {
        padding: 1rem;
    }

</style>
@endpush
@endsection
