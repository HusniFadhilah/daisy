{{--
    resources/views/de/validasi-pembayaran-banding/components/modal-kirim-invoice.blade.php

    DAPAT DIGUNAKAN DI DUA TEMPAT:
    1. de/validasi-pembayaran-banding/index  → dikirim $pengajuanBelumInvoice dari controller
    2. de/penerimaan-banding/show            → dikirim $pengajuanSingle (array berisi satu item)

    Cara include dari penerimaan-banding/show:
        @include('de.validasi-pembayaran-banding.components.modal-kirim-invoice', [
            'pengajuanBelumInvoice' => collect([$pengajuan]),
            'modeSingle' => true,
        ])
--}}

@php
$modeSingle = $modeSingle ?? false;
$pengajuanInModal = $pengajuanBelumInvoice ?? collect();
@endphp

<div class="modal fade" id="modalKirimInvoiceBanding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.validasi-pembayaran-banding.kirim-invoice') }}" method="POST" id="formKirimInvoiceBanding">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-send"></i>
                        {{ $modeSingle ? 'Kirim Invoice Pembayaran Banding' : 'Kirim Invoice Banding ke PS' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Daftar Permohonan --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            {{ $modeSingle ? 'Permohonan Banding' : 'Pilih Permohonan yang Akan Dikirimi Invoice' }}
                        </label>

                        <div style="max-height: 220px; overflow-y: auto;" class="border rounded p-3 {{ $pengajuanInModal->isEmpty() ? 'bg-light' : '' }}">
                            @forelse($pengajuanInModal as $item)
                            <div class="form-check {{ !$loop->last ? 'mb-2' : '' }}">
                                <input type="checkbox" class="form-check-input" name="id_pengajuan[]" value="{{ $item->id }}" id="vpbPengajuan{{ $item->id }}" {{ $modeSingle ? 'checked' : '' }}>
                                <label class="form-check-label" for="vpbPengajuan{{ $item->id }}">
                                    <strong>{{ $item->nomor_pengajuan }}</strong>
                                    — {{ $item->studyProgram->name }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $item->studyProgram->university->name }}
                                        &bull; {{ $item->studyProgram->degreeLevel->name ?? '-' }}
                                    </small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted mb-0 py-2">
                                <i class="bi bi-inbox"></i>
                                Tidak ada permohonan yang perlu dikirimi invoice banding
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($pengajuanInModal->isNotEmpty())
                    <div class="row">
                        {{-- Jumlah Pembayaran --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Jumlah Pembayaran (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="jumlah_pembayaran" class="form-control" min="1000000" step="100000" value="{{ \App\Models\PengajuanPembayaran::BIAYA_BANDING ?? 30000000 }}" required>
                            <small class="text-muted">
                                Default: Rp {{ number_format(\App\Models\PengajuanPembayaran::BIAYA_BANDING ?? 30000000, 0, ',', '.') }}
                            </small>
                        </div>

                        {{-- Tanggal Jatuh Tempo --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                Tanggal Jatuh Tempo <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_jatuh_tempo" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ date('Y-m-d', strtotime('+7 day')) }}" required>
                            <small class="text-muted">Batas waktu pembayaran oleh PS</small>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="mb-3">
                        <label class="form-label">Keterangan <small class="text-muted">(Opsional)</small></label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Instruksi transfer, nomor rekening, dll..."></textarea>
                    </div>

                    <div class="alert alert-info alert-permanent mb-0">
                        <i class="bi bi-info-circle"></i>
                        <small>
                            Invoice banding dikirim per permohonan.<br>
                            Penugasan asesor banding dilakukan setelah pembayaran tervalidasi.
                        </small>
                    </div>
                    @endif

                </div>

                @if($pengajuanInModal->isNotEmpty())
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnKirimInvoice">
                        <i class="bi bi-send"></i> Kirim Invoice Banding
                    </button>
                </div>
                @endif

            </form>
        </div>
    </div>
</div>

{{-- Modal Reminder Keuangan --}}
<x-modal-kirim-reminder-pembayaran modal-id="modalReminderKeuanganBanding" form-action="{{ route('de.validasi-pembayaran-banding.kirim-reminder-keuangan') }}" title="Ingatkan Keuangan untuk Segera Validasi Pembayaran Banding" :pembayarans="$pendingValidasi" input-name="id_pembayaran" item-description="Menunggu validasi keuangan" default-message="Yth. Bagian Keuangan LAMDEPILAR,

Terdapat bukti pembayaran banding yang masih menunggu validasi. Mohon segera diproses agar tidak menghambat proses banding program studi terkait.

Terima kasih.
Sekretariat LAMDEPILAR" />

{{-- Modal Reminder UPPS --}}
<x-modal-kirim-reminder-pembayaran modal-id="modalReminderUPPSBanding" form-action="{{ route('de.validasi-pembayaran-banding.kirim-reminder-upps') }}" title="Ingatkan UPPS untuk Segera Melakukan Pembayaran Banding" :pembayarans="$pendingPembayaran" input-name="id_pembayaran" item-description="Menunggu pembayaran / upload ulang bukti" default-message="Yth. Unit Pengelola Program Studi,

Kami mengingatkan bahwa pembayaran banding Anda masih belum diselesaikan. Mohon segera lakukan pembayaran sebelum melewati tanggal jatuh tempo.

Terima kasih atas perhatiannya.
Sekretariat LAMDEPILAR" />

@push('scripts')
<script>
    const formKirimInvoiceBanding = document.getElementById('formKirimInvoiceBanding')
    if (formKirimInvoiceBanding) formKirimInvoiceBanding.addEventListener('submit', function() {
        const btn = document.getElementById('btnKirimInvoice');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        }
    });

</script>
@endpush
