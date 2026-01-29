{{-- VERIFIKASI PEMBAYARAN --}}
@if($pengajuan->status === 'pembayaran_diterima' && $pengajuan->pembayaran && $pengajuan->pembayaran->status_pembayaran === 'menunggu_verifikasi')
<div class="card action-card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-credit-card"></i>
            Aksi Diperlukan: Validasi Pembayaran
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info alert-permanent">
            <strong>Invoice:</strong> {{ $pengajuan->pembayaran->nomor_invoice }}<br>
            <strong>Jumlah:</strong> Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
            <strong>Tanggal Pembayaran:</strong> {{ \App\Libraries\Date::tglIndo($pengajuan->pembayaran->tanggal_pembayaran) }}
        </div>

        <form action="{{ route('de.pengajuan.verifikasi-pembayaran', $pengajuan->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Catatan Validasi</label>
                <textarea name="catatan_verifikasi" class="form-control" rows="3" required></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="status" value="verified" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Setujui
                </button>
                <button type="submit" name="status" value="ditolak" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Tolak Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endif
