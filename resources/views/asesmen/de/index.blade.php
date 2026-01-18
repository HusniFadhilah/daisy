@extends('layouts.template.app')

@section('title', 'Dashboard Pengajuan Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="mb-4">
        <h2><i class="bi bi-clipboard-data"></i> Dashboard Pengajuan Akreditasi</h2>
        <p class="text-muted mb-0">Kelola pengajuan akreditasi dan review kesiapan program studi</p>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-files" style="font-size: 2rem; color: #0d6efd;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Pengajuan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history" style="font-size: 2rem; color: #ffc107;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['menunggu_review'] }}</h3>
                    <p class="text-muted mb-0">Menunggu Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card" style="font-size: 2rem; color: #0dcaf0;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['menunggu_pembayaran'] }}</h3>
                    <p class="text-muted mb-0">Menunggu Pembayaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['siap_lanjut'] }}</h3>
                    <p class="text-muted mb-0">Siap Lanjut AK</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalKirimPengingat">
                <i class="bi bi-bell"></i> Kirim Pengingat Akreditasi
            </button>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor pengajuan atau prodi..." value="{{ request('search') }}">
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
                            <th>Pengajuan</th>
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
                                <a href="{{ route('de.pengajuan.show', $pengajuan->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
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

<!-- Modal Kirim Pengingat -->
<div class="modal fade" id="modalKirimPengingat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.pengajuan.kirim-pengingat') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Kirim Pengingat Akreditasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Program Studi</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                            @foreach(\App\Models\StudyProgram::with('degreeLevel')->get() as $prodi)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="id_program_studi[]" value="{{ $prodi->id }}" id="prodi{{ $prodi->id }}">
                                <label class="form-check-label" for="prodi{{ $prodi->id }}">
                                    {{ $prodi->full_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Pengingat</label>
                        <textarea name="pesan_pengingat" class="form-control" rows="5" required>Kepada Yth. Program Studi,

Masa akreditasi program studi Anda akan segera berakhir. Kami mengingatkan untuk segera mempersiapkan dan mengajukan permohonan akreditasi.

Terima kasih atas perhatiannya.

Hormat kami,
Dewan Eksekutif (DE) LAMDEPILAR</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Pengingat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
