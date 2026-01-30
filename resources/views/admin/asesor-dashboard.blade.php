<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penawaran Menunggu</div>
                    <div class="stat-value">{{ $stats['penawaran'] ?? 0 }}</div>
                </div>
                <div class="stat-icon">📨</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card info">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penugasan Aktif</div>
                    <div class="stat-value">{{ $stats['penugasan_aktif'] ?? 0 }}</div>
                </div>
                <div class="stat-icon">⏳</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card success">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penugasan Selesai</div>
                    <div class="stat-value">{{ $stats['penugasan_selesai'] ?? 0 }}</div>
                </div>
                <div class="stat-icon">✅</div>
            </div>
        </div>
    </div>
</div>
