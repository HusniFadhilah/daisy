@if(in_array($authUser->role_selected,['admin_prodi','admin_univ']))

<!-- Informasi Detail -->


<!-- SECTION: AKREDITASI INDIVIDUAL -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold mb-3">
            <i class="bi bi-person-check text-info me-2"></i>
            Akreditasi Program Studi (Individual)
        </h5>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Permohonan Individual Berjalan -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Total Permohonan Berjalan</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_individual_berjalan'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #dc3545;">
                        <i class="bi bi-calendar-check text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Semua permohonan akreditasi yang sedang berjalan</p>
            </div>
        </div>
    </div>

    <!-- Permohonan Individual Selesai -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Total Permohonan Selesai</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_individual_selesai'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #198754;">
                        <i class="bi bi-check-circle text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Semua permohonan akreditasi yang telah selesai</p>
            </div>
        </div>
    </div>

    <!-- Akreditasi Individual (Berjalan) -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Akreditasi Individual</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_individual_berjalan'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #0dcaf0;">
                        <i class="bi bi-person-badge text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Permohonan akreditasi program studi (individual) yang sedang berjalan</p>
            </div>
        </div>
    </div>

    <!-- Individual Selesai -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #721c24 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Individual Selesai</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_individual_selesai'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #721c24;">
                        <i class="bi bi-check-all text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Permohonan akreditasi individual yang telah selesai</p>
            </div>
        </div>
    </div>
</div>

<!-- SECTION: AKREDITASI KELOMPOK -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold mb-3">
            <i class="bi bi-people-fill text-warning me-2"></i>
            Akreditasi Kelompok Program Studi (Cluster)
        </h5>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Permohonan Kelompok Berjalan -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Total Permohonan Berjalan</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_kelompok_berjalan'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #dc3545;">
                        <i class="bi bi-calendar-check text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Semua permohonan akreditasi yang sedang berjalan</p>
            </div>
        </div>
    </div>

    <!-- Permohonan Kelompok Selesai -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Total Permohonan Selesai</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_kelompok_selesai'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #198754;">
                        <i class="bi bi-check-circle text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Semua permohonan akreditasi yang telah selesai</p>
            </div>
        </div>
    </div>

    <!-- Akreditasi Kelompok (Berjalan) -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Akreditasi Kelompok</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_kelompok_berjalan'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #ffc107;">
                        <i class="bi bi-people text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Permohonan akreditasi kelompok program studi yang sedang berjalan</p>
            </div>
        </div>
    </div>

    <!-- Kelompok Selesai -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #721c24 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small text-uppercase">Kelompok Selesai</p>
                        <h2 class="mb-0 fw-bold">{{ $stats['permohonan_kelompok_selesai'] ?? 0 }}</h2>
                    </div>
                    <div class="rounded-3 p-3" style="background-color: #721c24;">
                        <i class="bi bi-check-all text-white fs-3"></i>
                    </div>
                </div>
                <p class="text-muted small mb-0">Permohonan akreditasi kelompok yang telah selesai</p>
            </div>
        </div>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Informasi Detail
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Program Studi</div>
                            <div class="h2 mb-0 fw-bold">{{ $additionalStats['total_prodi'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Pengajuan</div>
                            <div class="h2 mb-0 fw-bold">{{ $additionalStats['total_pengajuan'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Individual</div>
                            <div class="h2 mb-0 fw-bold text-info">{{ $additionalStats['total_individual'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Kelompok</div>
                            <div class="h2 mb-0 fw-bold text-warning">{{ $additionalStats['total_kelompok'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
