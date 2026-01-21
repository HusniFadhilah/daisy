<div class="card mb-4">
    <div class="card-header bg-light d-flex flex-column gap-2">

        <!-- ROW 1: Title kiri, Toggle kanan -->
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i> Ringkasan Hasil Validasi
            </h5>

            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#monitoringCollapse" aria-expanded="true" aria-controls="monitoringCollapse" id="btnToggleMonitoring">
                <i class="bi bi-chevron-down" id="iconToggleMonitoring"></i>
                <span class="d-none d-sm-inline">Sembunyikan</span>
            </button>
        </div>

        <!-- ROW 2: Badge + Legenda -->
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span class="ms-2 text-muted small">Legenda:</span>
            <span class="legend-badge small">
                <span class="legend-dot bg-primary"></span> A (Sudah Tepat)
            </span>
            <span class="legend-badge small">
                <span class="legend-dot bg-warning"></span> B (Kurang Lengkap)
            </span>
            <span class="legend-badge small">
                <span class="legend-dot bg-danger"></span> C (Perlu Diperbaiki)
            </span>
        </div>

    </div>

    <!-- DEFAULT COLLAPSED -->
    <div class="collapse show" id="monitoringCollapse">
        <div class="card-body">
            <ul class="nav nav-pills mb-3" id="monitoringTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="mt-led" data-bs-toggle="tab" data-bs-target="#mp-led" type="button" role="tab">
                        LED
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="mt-suplemen" data-bs-toggle="tab" data-bs-target="#mp-suplemen" type="button" role="tab">
                        Suplemen
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="mt-lkps" data-bs-toggle="tab" data-bs-target="#mp-lkps" type="button" role="tab">
                        LKPS
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- LED --}}
                <div class="tab-pane fade show active" id="mp-led" role="tabpanel" aria-labelledby="mt-led">
                    <div class="table-responsive monitoring-scroll">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Kode</th>
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th>Kategori</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="monitoringBodyLed">
                                <tr>
                                    <td colspan="7" class="text-muted">Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- SUPLEMEN --}}
                <div class="tab-pane fade" id="mp-suplemen" role="tabpanel" aria-labelledby="mt-suplemen">
                    <div class="table-responsive monitoring-scroll">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Kode</th>
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="monitoringBodySuplemen">
                                <tr>
                                    <td colspan="7" class="text-muted">Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- LKPS --}}
                <div class="tab-pane fade" id="mp-lkps" role="tabpanel" aria-labelledby="mt-lkps">
                    <div class="table-responsive monitoring-scroll">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Kode</th>
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="monitoringBodyLkps">
                                <tr>
                                    <td colspan="7" class="text-muted">Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
