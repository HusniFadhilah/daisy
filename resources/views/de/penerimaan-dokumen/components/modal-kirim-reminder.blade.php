{{-- resources/views/de/penerimaan-dokumen/components/modal-kirim-reminder.blade.php --}}
<div class="modal fade" id="modalKirimReminder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.penerimaan-dokumen.kirim-reminder') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Kirim Reminder Upload Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Program Studi</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                            @forelse($pengajuanMenunggu as $pengajuan)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="id_pengajuan[]" value="{{ $pengajuan->id }}" id="reminder{{ $pengajuan->id }}">
                                <label class="form-check-label" for="reminder{{ $pengajuan->id }}">
                                    <p class="mb-1">{{ $pengajuan->judul }}</p>
                                    <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->studyProgram->university->name }} -
                                        {{ $pengajuan->nomor_pengajuan }}
                                    </small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted py-3">
                                Tidak ada permohonan akreditasi dari PS yang menunggu upload dokumen
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($countPengajuanMenunggu > 0)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Reminder</label>
                        <textarea name="pesan_reminder" class="form-control" rows="6" required>Yth. Unit Pengelola Program Studi,

Pembayaran Anda telah terverifikasi. Kami mengingatkan untuk segera mengunggah dokumen LED+Suplemen dan LKPS melalui sistem DAISY.

Dokumen yang perlu diunggah:
1. Laporan Evaluasi Diri (LED) + Suplemen
2. Laporan Kinerja Program Studi (LKPS)

Terima kasih atas perhatiannya.

Hormat kami,
Dewan Eksekutif (DE) LAMDEPILAR</textarea>
                    </div>
                    @endif
                </div>
                @if($countPengajuanMenunggu > 0)
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Reminder
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
