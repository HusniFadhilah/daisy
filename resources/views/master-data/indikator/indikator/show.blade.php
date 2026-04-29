@extends('layouts.template.app')

@section('title', 'Detail Indikator')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Indikator</h5>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode</dt>
                <dd class="col-sm-9">{{ $indikator->kode_indikator }}</dd>
                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9">{{ $indikator->deskripsi_indikator }}</dd>
                <dt class="col-sm-3">Elemen</dt>
                <dd class="col-sm-9">{{ $indikator->elemenStandar->kode_elemen ?? '-' }}</dd>
                <dt class="col-sm-3">Jenis</dt>
                <dd class="col-sm-9">{{ $indikator->jenisIndikator->nama_jenis ?? '-' }}</dd>
            </dl>
            <a href="{{ route('indikator.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
