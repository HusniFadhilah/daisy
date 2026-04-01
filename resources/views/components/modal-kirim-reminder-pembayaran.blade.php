@props([
'modalId' => 'modalReminderPembayaran',
'formAction' => '',
'title' => 'Kirim Pengingat',
'pembayarans' => collect(),
'inputName' => 'id_pembayaran',
'itemDescription'=> '',
'defaultMessage' => '',
])

@php
$checkAllId = 'checkAll_' . $modalId;
$itemClass = 'reminder-item-' . $modalId;
$count = $pembayarans->count();
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
                        <label class="form-label fw-bold">Pilih Pembayaran</label>

                        @if($count > 0)
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="{{ $checkAllId }}">
                            <label class="form-check-label" for="{{ $checkAllId }}">Pilih Semua</label>
                        </div>
                        @endif

                        <div style="max-height:260px;overflow-y:auto;border:1px solid #dee2e6;
                                    padding:10px;border-radius:4px;">
                            @forelse($pembayarans as $pembayaran)
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input {{ $itemClass }}" name="{{ $inputName }}[]" value="{{ $pembayaran->id }}" id="{{ $modalId }}_{{ $pembayaran->id }}">
                                <label class="form-check-label" for="{{ $modalId }}_{{ $pembayaran->id }}">
                                    <strong>
                                        {{ $pembayaran->pengajuan->studyProgram->name ?? '-' }}
                                    </strong>
                                    —
                                    {{ $pembayaran->pengajuan->studyProgram->university->name ?? '-' }}
                                    <br>
                                    <small class="text-muted">
                                        Invoice: {{ $pembayaran->nomor_invoice }}
                                        @if($pembayaran->tanggal_jatuh_tempo)
                                        · Jatuh Tempo:
                                        {{ \Carbon\Carbon::parse($pembayaran->tanggal_jatuh_tempo)
                                                ->translatedFormat('d M Y') }}
                                        @endif
                                        · {{ $itemDescription }}
                                    </small>
                                </label>
                            </div>
                            @empty
                            <p class="text-center text-muted py-3 mb-0">
                                Tidak ada data yang perlu diingatkan
                            </p>
                            @endforelse
                        </div>
                    </div>

                    @if($count > 0)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Pengingat</label>
                        <textarea name="pesan_reminder" class="form-control" rows="7" required>{{ $defaultMessage }}</textarea>
                    </div>
                    @endif
                </div>

                @if($count > 0)
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
