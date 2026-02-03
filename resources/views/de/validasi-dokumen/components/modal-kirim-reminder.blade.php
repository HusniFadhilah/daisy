{{-- resources/views/de/validasi-dokumen/components/modal-kirim-reminder.blade.php --}}
@php
$pendingAssignments = \App\Models\AsesmenUserRole::with(['user', 'asesmen.pengajuan.studyProgram'])
->where('jenis_asesmen', 'dokumen')
->whereIn('status_penawaran', ['accepted','pending'])
->whereIn('status_pekerjaan', ['not_started', 'in_progress'])
->get();
$countPendingAssignments = count($pendingAssignments);
@endphp
<div class="modal fade" id="modalKirimReminder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('de.validasi-dokumen.kirim-reminder') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Kirim Pengingat ke Validator
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Validator</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px;">
                            @forelse($pendingAssignments as $assignment)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="id_assignment[]" value="{{ $assignment->id }}" id="reminder{{ $assignment->id }}">
                                <label class="form-check-label" for="reminder{{ $assignment->id }}">
                                    <strong>{{ $assignment->user->name }}</strong> -
                                    {{ $assignment->asesmen->pengajuan->studyProgram->name??'' }}
                                    <br>
                                    <small class="text-muted">
                                        Status: {{ ucwords(str_replace('_', ' ', $assignment->status_pekerjaan)) }}
                                    </small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted py-3">
                                Tidak ada validator yang perlu diingatkan
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($countPendingAssignments > 0)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Reminder</label>
                        <textarea name="pesan_reminder" class="form-control" rows="6" required>Yth. Validator,

Kami mengingatkan untuk segera menyelesaikan validasi dokumen Dokumen yang telah ditugaskan kepada Anda.

Mohon untuk segera menyelesaikan review dan memberikan feedback kepada program studi.

Terima kasih atas kerjasamanya.

Hormat kami,
Sekretariat LAMDEPILAR</textarea>
                    </div>
                    @endif
                </div>
                @if($countPendingAssignments > 0)
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
