@extends('layouts.template.app')

@section('title', 'Permohonan Akreditasi PS')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="mb-4">
        <h2><i class="bi bi-clipboard-data"></i> Permohonan Akreditasi PS</h2>
        <p class="text-muted mb-0">Kelola permohonan akreditasi Program Studi</p>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-files" style="font-size: 2rem; color: #0d6efd;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Permohonan</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history" style="font-size: 2rem; color: #ffc107;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['menunggu_review'] }}</h3>
                    <p class="text-muted mb-0">Menunggu Validasi</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card" style="font-size: 2rem; color: #0dcaf0;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['menunggu_pembayaran'] }}</h3>
                    <p class="text-muted mb-0">Menunggu Pembayaran</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-info-circle" style="font-size: 2rem; color: #28a745;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['siap_lanjut'] }}</h3>
                    <p class="text-muted mb-0">Total Lanjut AK (saat ini)</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-info-circle" style="font-size: 2rem; color: #28a745;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['siap_lanjut'] }}</h3>
                    <p class="text-muted mb-0">Total Lanjut ke AL (saat ini)</p>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['siap_lanjut'] }}</h3>
                    <p class="text-muted mb-0">Total Selesai Pelaporan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor permohonan akreditasi atau prodi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\PengajuanAkreditasi::statusMap() as $key => $status)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                            {{ $status['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('de.pengajuan') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Permohonan</th>
                            <th>Program Studi</th>
                            <th>Jenis</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Review Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $pengajuan)
                        <tr>
                            <td>
                                <p>{{ $pengajuan->judul }}</p>
                                <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                            </td>
                            <td>
                                {{ $pengajuan->studyProgram->degreeLevel->name }} - {{ $pengajuan->studyProgram->name }}
                                <br>
                                <small class="text-muted">{{ $pengajuan->studyProgram->university->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($pengajuan->jenis_akreditasi) }}
                                </span>
                            </td>
                            <td>{{ $pengajuan->tahun_akreditasi }}</td>
                            <td>
                                <span class="badge {{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                            <td>
                                {{-- @if($pengajuan->reviewKesiapan->last())
                                <small>
                                    <i class="bi bi-calendar"></i>
                                    {{ $pengajuan->reviewKesiapan->last()->tanggal_review->format('d/m/Y') }}
                                </small>
                                @else
                                <small class="text-muted">Belum ada review</small>
                                @endif --}}
                            </td>
                            <td>
                                <a href="{{ route('de.pengajuan.show', $pengajuan->id) }}" class="btn btn-sm btn-info my-1">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <form action="{{ route('de.pengajuan.destroy', $pengajuan->id) }}" method="POST" class="d-inline form-delete">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger my-1">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">Belum ada pengajuan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pengajuans->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $pengajuans->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Yakin ingin menghapus?'
                    , text: 'Data yang sudah dihapus tidak dapat dikembalikan.'
                    , icon: 'warning'
                    , showCancelButton: true
                    , confirmButtonColor: '#d33'
                    , cancelButtonColor: '#6c757d'
                    , confirmButtonText: 'Ya, hapus'
                    , cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });

</script>

@endpush
