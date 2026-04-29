@extends('layouts.template.app')

@section('title', 'Sinkronisasi Jenjang Penilaian')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Sinkronisasi Hasil Akreditasi</h5>
        </div>
        <div class="card-body">
            <p class="mb-3">Total hasil akreditasi yang dapat disinkronkan: <strong>{{ $totalHasil }}</strong></p>
            <form action="{{ route('jenjang-penilaian.sync-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Sinkronkan Semua
                </button>
            </form>
            <a href="{{ route('jenjang-penilaian.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
