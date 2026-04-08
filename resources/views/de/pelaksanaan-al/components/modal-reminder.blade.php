{{-- Modal Reminder Berita Acara ke Asesor --}}
<div class="modal fade" id="modalReminderBeritaAcara" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.pelaksanaan-al.kirim-reminder-berita-acara', $pengajuan->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Ingatkan Asesor Upload Berita Acara AL
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning alert-permanent mb-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        Pengingat akan dikirim ke <strong>semua asesor & validator AL</strong>
                        yang bertugas pada program studi ini.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Reminder</label>
                        <textarea name="pesan_reminder" class="form-control" rows="7" required>Kami mengingatkan untuk segera mengunggah Berita Acara Asesmen Lapangan untuk program studi {{ $pengajuan->studyProgram->name ?? '-' }}.

Berita Acara merupakan dokumen penting yang diperlukan untuk melanjutkan proses pelaporan AL. Mohon segera upload dokumen tersebut melalui sistem.

Terima kasih atas kerjasamanya.

Hormat kami,
Sekretariat LAMDEPILAR</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send"></i> Kirim Pengingat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reminder LHA ke Asesor --}}
<div class="modal fade" id="modalReminderAsesor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.pelaksanaan-al.kirim-reminder-asesor', $pengajuan->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i>
                        @php
                        $isRevisiModal = $pengajuan->asesmen?->documents()
                        ->where('type', 'lha_asesor')
                        ->where('is_active', true)
                        ->where('status_persetujuan_prodi', 'revision_required')
                        ->exists();
                        @endphp
                        {{ $isRevisiModal ? 'Ingatkan Asesor untuk Perbaiki LHA' : 'Ingatkan Asesor untuk Finalisasi LHA' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-people"></i>
                        Pengingat akan dikirim ke <strong>semua asesor AL</strong>
                        yang bertugas pada program studi ini.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Reminder</label>
                        <textarea name="pesan_reminder" class="form-control" rows="7" required>@if($isRevisiModal)Kami mengingatkan bahwa terdapat permintaan revisi pada Laporan Hasil Asesmen Lapangan (LHA) untuk program studi {{ $pengajuan->studyProgram->name ?? '-' }} ({{ $pengajuan->studyProgram->university->name ?? '-' }}).

Mohon segera lakukan perbaikan sesuai catatan yang diberikan oleh Program Studi, kemudian finalisasi ulang dokumen.
@else
Kami mengingatkan untuk segera menyelesaikan dan memfinalisasi Laporan Hasil Asesmen Lapangan (LHA) untuk program studi {{ $pengajuan->studyProgram->name ?? '-' }}.

LHA yang telah difinalisasi akan dikirimkan ke Program Studi untuk mendapat persetujuan.
@endif

Terima kasih atas kerjasamanya.

Hormat kami,
Sekretariat LAMDEPILAR</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send"></i> Kirim Pengingat ke Asesor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reminder LHA ke UPPS --}}
<div class="modal fade" id="modalReminderUPPS" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.pelaksanaan-al.kirim-reminder-upps', $pengajuan->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Ingatkan UPPS untuk Tinjau LHA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info alert-permanent mb-3">
                        <i class="bi bi-building"></i>
                        Pengingat akan dikirim ke <strong>Program Studi / UPPS</strong>
                        agar segera melakukan peninjauan LHA.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Reminder</label>
                        <textarea name="pesan_reminder" class="form-control" rows="8" required>Tim asesor lapangan telah menyelesaikan Laporan Hasil Asesmen Lapangan (LHA) untuk program studi {{ $pengajuan->studyProgram->name ?? '-' }}.

LHA ini masih menunggu peninjauan dari Program Studi. Mohon segera:
1. Buka dan baca LHA yang telah disiapkan
2. Berikan persetujuan jika laporan sudah sesuai
3. Ajukan permintaan revisi jika ada yang perlu diperbaiki

Terima kasih atas kerjasamanya.

Hormat kami,
Sekretariat LAMDEPILAR</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Pengingat ke UPPS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showModalReminderBeritaAcara() {
        new bootstrap.Modal(document.getElementById('modalReminderBeritaAcara')).show();
    }

    function showModalReminderAsesor() {
        new bootstrap.Modal(document.getElementById('modalReminderAsesor')).show();
    }

    function showModalReminderUPPS() {
        new bootstrap.Modal(document.getElementById('modalReminderUPPS')).show();
    }

</script>
@endpush
