{{-- resources/views/prodi/penerimaan-permohonan/_components/filter-sidebar.blade.php --}}

<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="card-header border-0">
        <h5 class="mb-0">
            <i class="bi bi-funnel"></i> Filter & Pencarian
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('prodi.penerimaan-permohonan') }}">
            <!-- Search -->
            <div class="mb-3">
                <label class="form-label text-white">Cari Permohonan</label>
                <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
            </div>

            <!-- Status Penerimaan -->
            <div class="mb-3">
                <label class="form-label text-white">Status Penerimaan Permohonan Akreditasi</label>
                <select name="status_penerimaan" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status_penerimaan') == 'menunggu' ? 'selected' : '' }}>
                        Menunggu
                    </option>
                    <option value="diterima" {{ request('status_penerimaan') == 'diterima' ? 'selected' : '' }}>
                        Sudah Diterima
                    </option>
                </select>
            </div>

            <!-- Tahun -->
            <div class="mb-3">
                <label class="form-label text-white">Tahun Akreditasi</label>
                <select name="tahun" class="form-select">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $tahun)
                    <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                        {{ $tahun }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort -->
            <div class="mb-3">
                <label class="form-label text-white">Urutkan Berdasarkan</label>
                <select name="sort_by" class="form-select">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>
                        Tanggal Dibuat
                    </option>
                    <option value="tanggal_surat_permohonan_diterima" {{ request('sort_by') == 'tanggal_surat_permohonan_diterima' ? 'selected' : '' }}>
                        Tanggal Diterima DE
                    </option>
                    <option value="tahun_akreditasi" {{ request('sort_by') == 'tahun_akreditasi' ? 'selected' : '' }}>
                        Tahun Akreditasi
                    </option>
                </select>
            </div>

            <!-- Sort Order -->
            <div class="mb-3">
                <label class="form-label text-white">Urutan</label>
                <select name="sort_order" class="form-select">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>
                        Terbaru → Terlama
                    </option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>
                        Terlama → Terbaru
                    </option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-light">
                    <i class="bi bi-search"></i> Terapkan Filter
                </button>
                <a href="{{ route('prodi.penerimaan-permohonan') }}" class="btn btn-outline-light">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quick Stats -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="card-title">Quick Stats</h6>
        <div class="d-flex justify-content-between mb-2">
            <small>Total</small>
            <span class="badge bg-primary">{{ $stats['total'] ?? 0 }}</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <small>Menunggu</small>
            <span class="badge bg-warning">{{ $stats['menunggu'] ?? 0 }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <small>Diterima</small>
            <span class="badge bg-success">{{ $stats['diterima'] ?? 0 }}</span>
        </div>
    </div>
</div>
