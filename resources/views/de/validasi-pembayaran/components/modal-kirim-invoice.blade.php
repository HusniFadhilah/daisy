{{-- resources/views/de/validasi-pembayaran/components/modal-kirim-invoice.blade.php --}}
@php
// Ambil pengajuan yang sudah template_led_dikirim tapi belum ada invoice
$pengajuanList = \App\Models\PengajuanAkreditasi::with('studyProgram.degreeLevel', 'studyProgram.university')
->where('status', \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM)
->whereDoesntHave('pembayaran')
->get();
$countPengajuanList = count($pengajuanList);
@endphp
<div class="modal fade" id="modalKirimInvoice" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.validasi-pembayaran.kirim-invoice') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-send"></i> Kirim Invoice Pembayaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Program Studi yang Akan Dikirimi Invoice</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                            @forelse($pengajuanList as $pengajuan)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="id_pengajuan[]" value="{{ $pengajuan->id }}" id="pengajuan{{ $pengajuan->id }}">
                                <label class="form-check-label" for="pengajuan{{ $pengajuan->id }}">
                                    <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->studyProgram->university->name }} -
                                        {{ $pengajuan->studyProgram->degreeLevel->name }}
                                    </small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted py-3">
                                Tidak ada permohonan akreditasi yang perlu untuk dikirimi invoice
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($countPengajuanList > 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jumlah Pembayaran</label>
                                <input type="number" name="jumlah_pembayaran" class="form-control" placeholder="Contoh: 59.500.000" required min="0" step="100000" value="59500000">
                                <small class="text-muted">Dalam Rupiah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tanggal_jatuh_tempo" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ date('Y-m-d', strtotime('+7 day')) }}">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @if($countPengajuanList > 0)
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Invoice
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
