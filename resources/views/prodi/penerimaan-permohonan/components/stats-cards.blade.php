{{-- resources/views/prodi/penerimaan-permohonan/_components/stats-cards.blade.php --}}

<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <h6 class="mb-2 opacity-75">Total Permohonan Akreditasi</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                        <small class="opacity-75">Permohonan akreditasi yang telah diajukan</small>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <h6 class="mb-2 opacity-75">Menunggu Penerimaan Permohonan Akreditasi</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0 fw-bold">{{ $stats['menunggu'] }}</h2>
                        <small class="opacity-75">Belum mendapat penerimaan permohonan akreditasi</small>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body text-white">
                <h6 class="mb-2 opacity-75">Penerimaan Akreditasi Telah Diperoleh</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0 fw-bold">{{ $stats['diterima'] }}</h2>
                        <small class="opacity-75">Telah mendapatkan penerimaan akreditasi</small>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
