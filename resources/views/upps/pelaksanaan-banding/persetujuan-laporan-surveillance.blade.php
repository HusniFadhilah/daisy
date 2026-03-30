<!-- Berita Acara Asesmen Lapangan Banding (Read Only) -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Berita Acara Asesmen Lapangan Banding
        </h5>
    </div>
    <div class="card-body">
        @php
        $beritaAcaraList = $pengajuan->asesmen->beritaAcaraALBanding ?? collect([]);
        @endphp

        @if($beritaAcaraList->count() > 0)
        <div class="alert alert-light alert-permanent border mb-3">
            <i class="bi bi-info-circle text-secondary"></i>
            Berikut adalah berita acara pelaksanaan asesmen lapangan banding.
        </div>

        @foreach($beritaAcaraList as $index => $beritaAcara)
        <div class="card mb-3 border">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-start gap-3">
                    <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 40px;"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <strong>{{ $beritaAcara->title }}</strong>
                        </h6>
                        <small class="text-muted">
                            Diupload: {{ $beritaAcara->uploaded_at ? $beritaAcara->uploaded_at->locale('id')->translatedFormat('d M Y H:i') : '-' }}
                        </small>
                    </div>
                    <div>
                        <div class="btn-group-vertical w-100 w-md-auto">
                            <a href="{{ route('al_banding.berkas.documents.preview', ['id' => $pengajuan->asesmen->id, 'docId' => $beritaAcara->id]) }}" class="btn btn-outline-success" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                            </a>

                            <a href="{{ route('al_banding.berkas.documents.download', ['id' => $pengajuan->asesmen->id, 'docId' => $beritaAcara->id]) }}" class="btn btn-outline-primary">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @else
        <div class="text-center py-4">
            <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
            <p class="text-muted mt-2 mb-0">Belum ada berita acara banding yang diupload</p>
        </div>
        @endif
    </div>
</div>

<!-- Laporan Hasil Banding - Dengan Approval -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-check"></i> Laporan Hasil Surveillance Banding
        </h5>
    </div>
    <div class="card-body">
        @php
        $lhaList = $pengajuan->asesmen->documents()
        ->where('type', 'lha_asesor_banding')
        ->where('is_active', true)
        ->latest('uploaded_at')
        ->get();
        @endphp

        @if($lhaList->count() > 0)
        <div class="alert alert-info alert-permanent mb-3">
            <i class="bi bi-info-circle"></i>
            Berikut adalah laporan hasil asesmen lapangan banding yang telah diupload oleh asesor.<br>
            {{ $lhaList->first()->status_persetujuan_prodi == 'approved' ? '' : 'Mohon lakukan peninjauan dan berikan persetujuan.' }}
        </div>

        @foreach($lhaList as $index => $lha)
        <div class="card berita-acara-card mb-3 border-{{
                        $lha->status_persetujuan_prodi === 'approved' ? 'outline-success' :
                        ($lha->status_persetujuan_prodi === 'rejected' ? 'outline-danger' :
                        ($lha->status_persetujuan_prodi === 'revision_required' ? 'warning' : 'secondary'))
                    }}">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex flex-column flex-md-row align-items-start mb-3 gap-3">
                    <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 40px;"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <strong>{{ $lha->title }}</strong>
                        </h6>
                        <small class="text-muted">
                            Diupload: {{ $lha->uploaded_at ? $lha->uploaded_at->locale('id')->translatedFormat('d M Y H:i') : '-' }}
                        </small>
                        <br>
                        Status: <span class="badge {{ $lha->status_prodi_badge_class }} mt-1">
                            {{ $lha->status_prodi_label }}
                        </span>
                    </div>
                    <div>
                        <a href="{{ route('al_banding.berkas.documents.preview', ['id' => $pengajuan->asesmen->id, 'docId' => $lha->id]) }}" class="btn btn-outline-success w-100 w-md-auto" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Lihat File
                        </a>
                    </div>
                </div>

                <!-- Catatan Sebelumnya (jika ada) -->
                @if($lha->catatan_prodi)
                <div class="alert alert-light alert-permanent border mb-3">
                    <strong><i class="bi bi-chat-left-text"></i> Catatan Program Studi:</strong><br>
                    {{ $lha->catatan_prodi }}
                    @if($lha->approved_at_prodi)
                    <br><small class="text-muted">
                        <i class="bi bi-clock"></i> {{ $lha->approved_at_prodi->locale('id')->translatedFormat('d M Y H:i') }}
                    </small>
                    @endif
                </div>
                @endif

                <!-- Approval Section (hanya jika bisa direvisi) -->
                @if($lha->canBeRevised())
                <div class="approval-section">
                    <form action="{{ route('upps.pelaksanaan-banding.lha-banding.approve.process', ['id' => $pengajuan->id, 'docId' => $lha->id]) }}" method="POST" class="approval-form" id="approvalForm{{ $lha->id }}">
                        @csrf

                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-hand-thumbs-up"></i>
                            {{ $lha->status_persetujuan_prodi === 'revision_required' ? 'Tinjau Ulang Laporan' : 'Tinjau & Berikan Persetujuan' }}
                        </h6>

                        <!-- Action Selection -->
                        <div class="row row-cols-1 row-cols-md-2 mb-3">
                            <div class="col-md-6 mb-2">
                                <div class="form-check action-radio p-3 border rounded">
                                    <input class="form-check-input action-input" type="radio" name="action" id="approve{{ $lha->id }}" value="approve" data-form-id="{{ $lha->id }}" required>
                                    <label class="form-check-label w-100" for="approve{{ $lha->id }}">
                                        <i class="bi bi-check-circle text-success"></i>
                                        <strong>Setujui</strong>
                                        <br><small class="text-muted">Laporan sudah sesuai</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-check action-radio p-3 border rounded">
                                    <input class="form-check-input action-input" type="radio" name="action" id="revision{{ $lha->id }}" value="revision" data-form-id="{{ $lha->id }}" required>
                                    <label class="form-check-label w-100" for="revision{{ $lha->id }}">
                                        <i class="bi bi-arrow-repeat text-warning"></i>
                                        <strong>Permintaan Revisi</strong>
                                        <br><small class="text-muted">Perlu perbaikan</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-chat-left-text"></i> Catatan
                            </label>
                            <textarea name="catatan_prodi" id="catatan{{ $lha->id }}" class="form-control catatan-textarea" rows="4" placeholder="Pilih tindakan di atas untuk mengisi catatan otomatis, atau tulis catatan Anda sendiri"></textarea>
                            <small class="text-muted">
                                Catatan akan terlihat oleh asesor banding
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                            <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                <i class="bi bi-send"></i> Kirim Persetujuan
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @else
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-x" style="font-size: 64px; color: #ddd;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada laporan hasil surveillance banding yang diupload</p>
        </div>
        @endif
    </div>
</div>


@push('scripts')
<script>
    $(document).ready(function() {
        const prodiName = '{{ $pengajuan->studyProgram->name ?? "" }} '
        // Templat catatan
        const catatanTemplates = {
            approve: `Program Studi ${prodiName}menyatakan menyetujui laporan hasil surveillance banding`
            , revision: "Program Studi meminta revisi pada Laporan Hasil Surveillance Banding dengan catatan sebagai berikut:\n\n[Jelaskan bagian yang perlu diperbaiki]"
        };

        // Handle action change
        $('.action-input').on('change', function() {
            const formId = $(this).data('form-id');
            const action = $(this).val();
            const catatanField = $(`#catatan${formId}`);

            if (action === 'approve') {
                catatanField.val(catatanTemplates.approve);
            } else if (action === 'revision') {
                catatanField.val(catatanTemplates.revision);
                // Set cursor position after the template
                setTimeout(() => {
                    catatanField.focus();
                    const val = catatanField.val();
                    catatanField[0].setSelectionRange(val.length, val.length);
                }, 100);
            }
        });

        // Handle form submit
        $('.approval-form').on('submit', async function(e) {
            e.preventDefault();

            const form = $(this);
            const action = form.find('input[name="action"]:checked').val();
            const catatan = form.find('textarea[name="catatan_prodi"]').val().trim();

            // Validation
            if (!action) {
                Swal.fire('Perhatian', 'Silakan pilih tindakan terlebih dahulu (Setujui atau Minta Revisi)', 'warning');
                return false;
            }

            if (action === 'revision' && catatan === catatanTemplates.revision.trim()) {
                Swal.fire('Perhatian', 'Harap lengkapi catatan revisi dengan penjelasan yang spesifik', 'warning');
                form.find('textarea[name="catatan_prodi"]').focus();
                return false;
            }

            // Confirmation
            let confirmMsg = '';
            if (action === 'approve') {
                confirmMsg = `Apakah Anda yakin ingin menyetujui laporan hasil surveillance banding ini?<br><br>Setelah disetujui, asesor akan mendapat notifikasi dan status akan berubah.`;
            } else if (action === 'revision') {
                confirmMsg = `Apakah Anda yakin ingin meminta revisi?<br><br>Asesor akan diminta melakukan perbaikan sesuai catatan yang Anda berikan.`;
            }

            if (!(await swalConfirmSubmit('warning', confirmMsg))) {
                return false;
            }

            // Disable button to prevent double submit
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

            // Submit form
            form[0].submit();
        });
    });

</script>
@endpush
