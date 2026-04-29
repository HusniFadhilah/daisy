@extends('layouts.template.app')

@section('title', 'Detail Indikator Penilaian')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Indikator Penilaian</h5>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Elemen Standar</dt>
                <dd class="col-sm-9">{{ $indikator->elemenStandar->kode_elemen ?? '-' }} - {{ $indikator->elemenStandar->pernyataan_elemen ?? '-' }}</dd>
                <dt class="col-sm-3">Jenjang</dt>
                <dd class="col-sm-9">Skor {{ $indikator->jenjangPenilaian->skor ?? '-' }} - {{ $indikator->jenjangPenilaian->name ?? '-' }}</dd>
                <dt class="col-sm-3">Deskripsi Penilaian</dt>
                <dd class="col-sm-9">{{ $indikator->deskripsi_penilaian }}</dd>
                <dt class="col-sm-3">Keterangan</dt>
                <dd class="col-sm-9">{{ $indikator->keterangan ?? '-' }}</dd>
            </dl>
            <a href="{{ route('indikator-penilaian.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
