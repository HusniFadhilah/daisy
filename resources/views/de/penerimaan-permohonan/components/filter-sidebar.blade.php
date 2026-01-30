{{-- resources/views/de/penerimaan-permohonan/_components/filter-sidebar.blade.php --}}

<div class="card filter-card">
    <div class="card-header border-0">
        <h5 class="mb-0">
            <i class="bi bi-funnel"></i> Filter & Pencarian
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('de.penerimaan-permohonan') }}">
            <!-- Search -->
            <div class="mb-3">
                <label class="form-label text-white">Cari Permohonan</label>
                <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
            </div>

            <!-- Status Penerimaan -->
            <div class="mb-3">
                <label class="form-label text-white">Status Surat Penerimaan</label>
                <select name="status_penerimaan" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="belum_terkirim" {{ request('status_penerimaan') == 'belum_terkirim' ? 'selected' : '' }}>
                        Belum Terkirim
                    </option>
                    <option value="terkirim" {{ request('status_penerimaan') == 'terkirim' ? 'selected' : '' }}>
                        Sudah Terkirim
                    </option>
                </select>
            </div>

            <!-- University -->
            <div class="mb-3">
                <label class="form-label text-white">Universitas</label>
                <select name="university_id" class="form-select">
                    <option value="">Semua Universitas</option>
                    @foreach($universities as $id => $name)
                    <option value="{{ $id }}" {{ request('university_id') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                    @endforeach
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
                    <option value="tanggal_surat_permohonan_diterima" {{ request('sort_by') == 'tanggal_surat_permohonan_diterima' ? 'selected' : '' }}>
                        Tanggal Diterima
                    </option>
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>
                        Tanggal Dibuat
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
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>
                        Terlama → Terbaru
                    </option>
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>
                        Terbaru → Terlama
                    </option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-light">
                    <i class="bi bi-search"></i> Terapkan Filter
                </button>
                <a href="{{ route('de.penerimaan-permohonan') }}" class="btn btn-outline-light">
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
            <small>Belum Terkirim</small>
            <span class="badge bg-warning">{{ $stats['belum_terkirim'] ?? 0 }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <small>Sudah Terkirim</small>
            <span class="badge bg-success">{{ $stats['terkirim'] ?? 0 }}</span>
        </div>
    </div>
</div>
