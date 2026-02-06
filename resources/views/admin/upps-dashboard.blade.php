<!-- ============================================ -->
<!-- SECTION: AKREDITASI INDIVIDUAL -->
<!-- ============================================ -->
<div class="alert alert-light alert-permanent">
    <i class="bi bi-info-circle me-1"></i>
    Tahapan akreditasi program studi <strong>LAMDEPILAR</strong> terdiri dari kurang lebih <strong>20 langkah</strong><br>
    Anda dapat mengikuti <strong>permohonan akreditasi</strong> Anda dengan mengunjungi menu yang tersedia
</div>

{{-- <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-gradient-info text-dark py-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-person-check me-2"></i>
            Akreditasi Program Studi (Individual)
        </h5>
        <small>Akreditasi per program studi secara individual</small>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <!-- Permohonan Individual Berjalan -->
            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase">Permohonan Berlangsung</p>
                                <h2 class="mb-0 fw-bold">{{ $stats['permohonan_individual_berjalan'] ?? 0 }}</h2>
</div>
<div class="rounded-3 p-3" style="background-color: #dc3545;">
    <i class="bi bi-calendar-check text-white fs-3"></i>
</div>
</div>
<p class="text-muted small mb-0">Akreditasi individual yang sedang berlangsung</p>
</div>
</div>
</div>

<!-- Permohonan Individual Selesai -->
<div class="col-12 col-md-6 col-lg-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="text-muted mb-1 small text-uppercase">Permohonan Selesai</p>
                    <h2 class="mb-0 fw-bold">{{ $stats['permohonan_individual_selesai'] ?? 0 }}</h2>
                </div>
                <div class="rounded-3 p-3" style="background-color: #198754;">
                    <i class="bi bi-check-circle text-white fs-3"></i>
                </div>
            </div>
            <p class="text-muted small mb-0">Akreditasi individual yang telah selesai</p>
        </div>
    </div>
</div>
<!-- Total Individual -->
<div class="col-12 col-md-6 col-lg-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="text-muted mb-1 small text-uppercase">Total Akreditasi</p>
                    <h2 class="mb-0 fw-bold">{{ $additionalStats['total_individual'] ?? 0 }}</h2>
                </div>
                <div class="rounded-3 p-3" style="background-color: #6c757d;">
                    <i class="bi bi-check-all text-white fs-3"></i>
                </div>
            </div>
            <p class="text-muted small mb-0">Total semua akreditasi individual</p>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- ============================================ -->
<!-- SECTION: AKREDITASI KELOMPOK -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-gradient-warning text-dark py-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-people-fill me-2"></i>
            Akreditasi Kelompok Program Studi (Cluster)
        </h5>
        <small>Akreditasi beberapa program studi secara bersamaan dalam satu kelompok</small>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <!-- Permohonan Kelompok Berlangsung -->
            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase">Permohonan Berlangsung</p>
                                <h2 class="mb-0 fw-bold">{{ $stats['permohonan_kelompok_berjalan'] ?? 0 }}</h2>
                            </div>
                            <div class="rounded-3 p-3" style="background-color: #dc3545;">
                                <i class="bi bi-calendar-check text-white fs-3"></i>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Akreditasi kelompok yang sedang berlangsung</p>
                    </div>
                </div>
            </div>

            <!-- Permohonan Kelompok Selesai -->
            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase">Permohonan Selesai</p>
                                <h2 class="mb-0 fw-bold">{{ $stats['permohonan_kelompok_selesai'] ?? 0 }}</h2>
                            </div>
                            <div class="rounded-3 p-3" style="background-color: #198754;">
                                <i class="bi bi-check-circle text-white fs-3"></i>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Akreditasi kelompok yang telah selesai</p>
                    </div>
                </div>
            </div>

            <!-- Total Kelompok -->
            <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-muted mb-1 small text-uppercase">Total Akreditasi</p>
                                <h2 class="mb-0 fw-bold">{{ $additionalStats['total_kelompok'] ?? 0 }}</h2>
                            </div>
                            <div class="rounded-3 p-3" style="background-color: #6c757d;">
                                <i class="bi bi-check-all text-white fs-3"></i>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Total semua akreditasi kelompok</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Detail -->
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
                    <!-- Total Program Studi -->
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Program Studi</div>
                            <div class="h2 mb-0 fw-bold">{{ $additionalStats['total_prodi'] ?? 0 }}</div>

                            @if($authUser->role_selected === 'admin_prodi' && isset($additionalStats['prodi_list']) && count($additionalStats['prodi_list']) > 0)
                            <div class="mt-3 text-start">
                                <small class="text-muted d-block mb-1 fw-semibold">Program Studi di dalam UPPS ini:</small>
                                <div style="max-height: 150px; overflow-y: auto;">
                                    @foreach($additionalStats['prodi_list'] as $prodi)
                                    <small class="d-block text-muted" style="font-size: 0.75rem; line-height: 1.4;">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.4rem;"></i>
                                        {{ $prodi->name }}
                                    </small>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Total Pengajuan -->
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Permohonan Akreditasi</div>
                            <div class="h2 mb-0 fw-bold">{{ $additionalStats['total_pengajuan'] ?? 0 }}</div>
                        </div>
                    </div>

                    <!-- Total Individual -->
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Permohonan Akreditasi Individual</div>
                            <div class="h2 mb-0 fw-bold text-info">{{ $additionalStats['total_individual'] ?? 0 }}</div>
                        </div>
                    </div>

                    <!-- Total Kelompok -->
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="p-3">
                            <div class="text-muted small mb-2">Total Permohonan Akreditasi Kelompok</div>
                            <div class="h2 mb-0 fw-bold text-warning">{{ $additionalStats['total_kelompok'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}
