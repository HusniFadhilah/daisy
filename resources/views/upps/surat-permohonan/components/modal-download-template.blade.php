<!-- Modal Download Templat -->
<div class="modal fade" id="modalDownloadTemplate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-download"></i> Download Templat Permohonan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Silahkan download templat permohonan akreditasi pada tombol berikut:</p>

                <div class="list-group">
                    <a href="{{ route('upps.surat-permohonan.download-template-surat') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-word text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Templat Permohonan Akreditasi</h6>
                                <small class="text-muted">Format: DOCX</small>
                            </div>
                            <i class="bi bi-download ms-auto"></i>
                        </div>
                    </a>
                </div>

                <div class="alert alert-warning alert-permanent mt-3 mb-0">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        Templat harus diisi lengkap dan ditandatangani oleh pejabat berwenang
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
