@props([
'modalId' => 'modalKirimReminderPelaporan',
'formAction' => '',
'title' => 'Kirim Pengingat Pelaporan',
'assignments' => collect(),
'countPending' => 0,
'itemDescription' => 'Belum upload laporan',
'defaultMessage' => null,
])

@php
$defaultMsg = $defaultMessage ?? "Yth. Validator,\n\nKami mengingatkan untuk segera mengunggah laporan yang menjadi penugasan Anda.\nMohon untuk segera menyelesaikan dan mengunggah laporan agar proses dapat dilanjutkan.\n\nTerima kasih atas kerjasamanya.\n\nHormat kami,\nSekretariat LAMDEPILAR";
$checkAllId = 'checkAll_' . $modalId;
$itemClass = 'reminder-item-' . $modalId;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ $formAction }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> {{ $title }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Validator</label>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="{{ $checkAllId }}">
                            <label class="form-check-label" for="{{ $checkAllId }}">Pilih Semua</label>
                        </div>
                        <div style="max-height:260px;overflow-y:auto;border:1px solid #dee2e6;padding:10px;border-radius:4px;">
                            @forelse($assignments as $assignment)
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input {{ $itemClass }}" name="id_assignment[]" value="{{ $assignment->id }}" id="{{ $modalId }}_{{ $assignment->id }}">
                                <label class="form-check-label" for="{{ $modalId }}_{{ $assignment->id }}">
                                    <strong>{{ $assignment->user->name }}</strong>
                                    — {{ $assignment->asesmen->pengajuan->studyProgram->name ?? '-' }}
                                    <br>
                                    <small class="text-muted">{{ $itemDescription }}</small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted py-3 mb-0">
                                Tidak ada validator yang perlu diingatkan
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($countPending > 0)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Pengingat</label>
                        <textarea name="pesan_reminder" class="form-control" rows="6" required>{{ $defaultMsg }}</textarea>
                    </div>
                    @endif
                </div>

                @if($countPending > 0)
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

{{-- Script scoped per modal agar tidak konflik antar halaman --}}
<script>
    (function() {
        const checkAll = document.getElementById('{{ $checkAllId }}');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                document.querySelectorAll('.{{ $itemClass }}')
                    .forEach(cb => cb.checked = this.checked);
            });
        }
    })();

</script>
