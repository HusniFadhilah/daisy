<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card warning">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Pengingat Masa Akreditasi</div>
                    <div class="stat-value">{{ $stats['penawaran'] ?? 0 }}</div>
                    <small>PS yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang</small>
                </div>
                <div class="stat-icon">📨</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card info">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penerimaan Dokumen Akreditasi Prodi</div>
                    <div class="stat-value">{{ $stats['penerimaan_dokumen'] ?? 0 }}</div>
                    <small>Jumlah Dokumen yang telah diterima dari prodi</small>
                </div>
                <div class="stat-icon"><i class="bi bi-file-earmark-check text-white"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penawaran Menunggu</div>
                    <div class="stat-value">{{ $stats['penawaran_menunggu'] ?? 0 }}</div>
                    <small>Validator/Asesor yang telah ditugaskan, tetapi statusnya masih menunggu diterima</small>
                </div>
                <div class="stat-icon"><i class="bi bi-hourglass-split text-white"></i></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card success">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Penugasan Aktif</div>
                    <div class="stat-value">{{ $stats['penugasan_aktif'] ?? 0 }}</div>
                    <small>Validator/Asesor yang telah ditugaskan, dan sedang melaksanakan asesmen</small>
                </div>
                <div class="stat-icon">✅</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card warning">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Proses AK Berlangsung</div>
                    <div class="stat-value">{{ $stats['proses_ak'] ?? 0 }}</div>
                    <small>Jumlah Asesmen Kecukupan yang sedang berlangsung</small>
                </div>
                <div class="stat-icon">⏳</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card primary">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Proses AL Berlangsung</div>
                    <div class="stat-value">{{ $stats['proses_al'] ?? 0 }}</div>
                    <small>Jumlah Asesmen Lapangan yang sedang berlangsung</small>
                </div>
                <div class="stat-icon">⏳</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="stat-card info">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Selesai Tahun Ini</div>
                    <div class="stat-value">{{ $stats['total_selesai'] ?? 0 }}</div>
                    <small>Jumlah Permohonan Akreditasi yang selesai di tahun ini</small>
                </div>
                <div class="stat-icon">🎯</div>
            </div>
        </div>
    </div>
</div>
