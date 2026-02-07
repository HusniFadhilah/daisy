<!-- Modal Terima Surat -->
<div class="modal fade" id="modalTerimaSurat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formTerimaSurat" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Tanggapi Permohonan Akreditasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menanggapi permohonan akreditasi:</p>
                    <div class="alert alert-info alert-permanent">
                        <strong id="nomorPengajuan"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle"></i>
                        Status akan berubah menjadi "Permohonan Akreditasi Ditanggapi" dan dapat dilanjutkan ke tahap berikutnya.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Tanggapi Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function terimaSurat(id, nomorPengajuan) {
        const modal = new bootstrap.Modal(document.getElementById('modalTerimaSurat'));
        const form = document.getElementById('formTerimaSurat');

        form.action = `{{ route('de.surat-permohonan') }}/${id}/terima`;
        document.getElementById('nomorPengajuan').textContent = nomorPengajuan;

        modal.show();
    }

</script>
@endpush
