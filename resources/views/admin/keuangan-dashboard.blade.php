<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <a href="{{ route('keuangan.pembayaran.index') }}" class="text-link" style="text-decoration: none;">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Pembayaran Perlu Divalidasi</div>
                        <div class="stat-value">{{ $stats['perlu_diverifikasi'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <a href="{{ route('keuangan.pembayaran.index') }}" class="text-link" style="text-decoration: none;">
            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Pembayaran Selesai Divalidasi</div>
                        <div class="stat-value">{{ $stats['selesai_diverifikasi'] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>
        </a>
    </div>
</div>
