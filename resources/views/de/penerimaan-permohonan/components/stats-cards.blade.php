{{-- resources/views/de/penerimaan-permohonan/_components/stats-cards.blade.php --}}

<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <h6 class="mb-2 opacity-75">Total Surat Permohonan yang Diterima</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                        <small class="opacity-75">Surat Permohonan Akreditasi yang telah diterima LAMDEPILAR</small>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <h6 class="mb-1 opacity-75">Surat Penerimaan Belum Terkirim</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0 fw-bold">{{ $stats['belum_terkirim'] }}</h2>
                        <small class="opacity-75">Perlu segera mengirim surat penerimaan permohonan akreditasi</small>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body text-white">
                <h6 class="mb-1 opacity-75">Surat Penerimaan Sudah Terkirim</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0 fw-bold">{{ $stats['terkirim'] }}</h2>
                        <small class="opacity-75">Surat penerimaan permohonan akreditasi telah dikirim ke PS</small>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
