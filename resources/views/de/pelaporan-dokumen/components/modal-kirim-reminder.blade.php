<div class="modal fade" id="modalKirimReminderPelaporan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.pelaporan-dokumen.kirim-reminder') }}" method="POST">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Kirim Pengingat Pelaporan Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Validator</label>

                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="checkAllReminderPelaporan">
                            <label class="form-check-label" for="checkAllReminderPelaporan">
                                Pilih Semua
                            </label>
                        </div>

                        <div style="max-height: 260px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                            @forelse($pendingReminderAssignments as $assignment)
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input reminder-pelaporan-item" name="id_assignment[]" value="{{ $assignment->id }}" id="reminderPelaporan{{ $assignment->id }}">
                                <label class="form-check-label" for="reminderPelaporan{{ $assignment->id }}">
                                    <strong>{{ $assignment->user->name }}</strong> -
                                    {{ $assignment->asesmen->pengajuan->studyProgram->name ?? '-' }}
                                    <br>
                                    <small class="text-muted">
                                        Belum upload laporan validasi dokumen
                                    </small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted py-3 mb-0">
                                Tidak ada validator yang perlu diingatkan
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($countPendingReminderAssignments > 0)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Pengingat</label>
                        <textarea name="pesan_reminder" class="form-control" rows="6" required>Yth. Validator,

Kami mengingatkan untuk segera mengunggah pelaporan validasi dokumen yang menjadi penugasan Anda.

Mohon untuk segera menyelesaikan dan mengunggah laporan validasi dokumen agar proses dapat dilanjutkan.

Terima kasih atas kerjasamanya.

Hormat kami,
Sekretariat LAMDEPILAR</textarea>
                    </div>
                    @endif
                </div>

                @if($countPendingReminderAssignments > 0)
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Kirim Pengingat
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAllReminderPelaporan');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                document.querySelectorAll('.reminder-pelaporan-item').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }
    });

</script>
