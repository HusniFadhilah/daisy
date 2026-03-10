// public/assets/js/pelaporan.js

/**
 * Pelaporan Module
 * Handle upload dan finalisasi laporan akreditasi
 */

const PelaporanModule = (() => {
    // Private variables
    let csrfToken = '';
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_TYPES = ['application/pdf'];

    // Configuration untuk setiap jenis pelaporan
    const REPORT_TYPES = {
        dokumen: {
            title: 'Pelaporan Validasi Dokumen',
            label: 'Laporan Kesiapan LED Program Studi (LKLED)',
            description: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
- Surat Permohonan PS untuk Akreditasi
- Surat Balasan DE untuk menyusun LED
- Bukti Pembayaran Akreditasi
- Dokumen LED yang telah memenuhi standar untuk dilakukan Penilaian Kecukupan (AK)`,
        },
        ak: {
            title: 'Pelaporan Validasi Asesmen Kecukupan',
            label: 'Laporan Penilaian Kecukupan LED Program Studi (LHK)',
            description: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
- Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
- Proses penilaian LED oleh Asesor
- Validasi Penilaian Kecukupan Asesor oleh Validator
- Penyampaian Informasi Kepada DE untuk dilakukan tahap Asesmen Lapangan`,
        },
        al: {
            title: 'Rekap AL dan Pelaporan AL',
            label: 'Laporan Hasil Asesmen Lapangan',
            description: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
- Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
- Proses penilaian LED oleh Asesor
- Validasi Penilaian Kecukupan Asesor Oleh Validator
- Penyampaian Informasi Kepada DE tentang pelaksanaan AL
- Berita Acara yang menyatakan AL telah dilaksanakan dan disepakati
- Rekomendasi Penetapan Hasil Akreditasi`,
        },
        ak_banding: {
            title: 'Pelaporan Validasi Asesmen Kecukupan Banding',
            label: 'Laporan Penilaian Kecukupan LED Program Studi (LHK)',
            description: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
- Penunjukan tugas Asesor Banding untuk melaksanakan Penilaian LED
- Proses penilaian LED oleh Asesor Banding
- Validasi Penilaian Kecukupan Asesor Banding oleh Validator
- Penyampaian Informasi Kepada DE untuk dilakukan tahap Asesmen Lapangan`,
        },
        al_banding: {
            title: 'Rekap AL dan Pelaporan AL Banding',
            label: 'Laporan Hasil Asesmen Lapangan Banding',
            description: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
- Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
- Proses penilaian LED oleh Asesor
- Validasi Penilaian Kecukupan Asesor Oleh Validator
- Penyampaian Informasi Kepada DE tentang pelaksanaan AL Banding
- Berita Acara yang menyatakan AL Bandingtelah dilaksanakan dan disepakati
- Rekomendasi Penetapan Hasil Akreditasi`,
        },
    };

    /**
     * Initialize module
     */
    function init(token) {
        csrfToken = token;
        attachEventListeners();
    }

    /**
     * Attach event listeners
     */
    function attachEventListeners() {
        document.addEventListener('click', handlePelaporanClick);
    }

    /**
     * Main handler untuk button pelaporan
     */
    async function handlePelaporanClick(e) {
        const btn = e.target.closest('.js-open-pelaporan');
        if (!btn) return;

        e.preventDefault();

        const { assignmentId, type, nomor } = btn.dataset;

        if (!REPORT_TYPES[type]) {
            showError('Jenis pelaporan tidak valid');
            return;
        }

        await processReporting(assignmentId, type, nomor);
    }

    /**
     * Main reporting flow
     */
    async function processReporting(assignmentId, type, nomor) {
        try {
            // Step 1: Pick and validate file
            const file = await pickFile(type);
            if (!file) return;

            // Step 2: Upload file
            const uploadResult = await uploadWithRetry(assignmentId, type, file);
            if (!uploadResult.success) {
                showError(uploadResult.message);
                return;
            }

            // Step 3: Confirm finalization
            const shouldFinalize = await confirmFinalize(
                uploadResult.filename,
                nomor,
                type
            );

            if (!shouldFinalize) {
                await Swal.fire({
                    icon: 'info',
                    title: 'File Tersimpan',
                    text: 'File sudah diunggah. Anda bisa finalisasi nanti.',
                });
                return;
            }

            // Step 4: Finalize
            const finalizeResult = await finalize(assignmentId, type);
            if (!finalizeResult.success) {
                showError(finalizeResult.message);
                return;
            }

            // Success
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: finalizeResult.message,
            });

            window.location.reload();
        } catch (error) {
            console.error('Reporting error:', error);
            showError('Terjadi kesalahan sistem');
        }
    }

    /**
     * Show file picker dialog
     */
    async function pickFile(type) {
        const config = REPORT_TYPES[type];

        const result = await Swal.fire({
            title: config.title,
            html: `
                <div class="text-start">
                    <p class="mb-3">
                        Upload <strong>${config.label}</strong>
                        <small class="text-muted">(PDF, maksimal 5MB)</small>
                    </p>

                    <input id="pelFile"
                           type="file"
                           class="form-control"
                           accept="application/pdf">

                    ${config.description ? `
                        <div class="alert alert-info mt-3" style="white-space: pre-line;">
                            <small><i class="bi bi-info-circle"></i> ${config.description}</small>
                        </div>
                    ` : ''}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-upload"></i> Upload',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary',
            },
            preConfirm: () => {
                const input = document.getElementById('pelFile');
                const file = input?.files?.[0];

                if (!file) {
                    Swal.showValidationMessage('Silakan pilih file PDF');
                    return false;
                }

                if (!ALLOWED_TYPES.includes(file.type)) {
                    Swal.showValidationMessage('File harus berupa PDF');
                    return false;
                }

                if (file.size > MAX_FILE_SIZE) {
                    Swal.showValidationMessage(
                        `Ukuran file maksimal ${MAX_FILE_SIZE / 1024 / 1024}MB`
                    );
                    return false;
                }

                return file;
            },
        });

        return result.value || null;
    }

    /**
     * Upload with retry mechanism
     */
    async function uploadWithRetry(assignmentId, type, file, maxRetries = 3) {
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            const result = await uploadFile(assignmentId, type, file);

            if (result.success) {
                return result;
            }

            // Last attempt
            if (attempt === maxRetries) {
                return result;
            }

            // Ask for retry
            const retry = await Swal.fire({
                icon: 'error',
                title: 'Upload Gagal',
                html: `
                    <p>${result.message}</p>
                    <small class="text-muted">Percobaan ${attempt} dari ${maxRetries}</small>
                `,
                showCancelButton: true,
                confirmButtonText: 'Coba Lagi',
                cancelButtonText: 'Batalkan',
            });

            if (!retry.isConfirmed) {
                return { success: false, message: 'Dibatalkan oleh pengguna' };
            }
        }
    }

    /**
     * Upload file dengan progress tracking
     */
    function uploadFile(assignmentId, type, file) {
        return new Promise((resolve) => {
            const url = getUploadUrl(assignmentId, type);
            const formData = new FormData();
            formData.append('file', file);

            // Show progress dialog
            Swal.fire({
                title: 'Mengunggah File...',
                html: `
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             style="width: 0%"
                             id="uploadProgress">
                            0%
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Mohon tunggu, jangan tutup halaman ini
                    </small>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            const xhr = new XMLHttpRequest();

            // Progress handler
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    const progressBar = document.getElementById('uploadProgress');
                    if (progressBar) {
                        progressBar.style.width = `${percent}%`;
                        progressBar.textContent = `${percent}%`;
                    }
                }
            });

            // Success handler
            xhr.addEventListener('load', () => {
                Swal.close();

                try {
                    const response = JSON.parse(xhr.responseText);

                    if (xhr.status === 200 && response.success) {
                        resolve({
                            success: true,
                            message: response.message || 'Upload berhasil',
                            filename: response.doc?.original_name || 'laporan.pdf',
                        });
                    } else {
                        resolve({
                            success: false,
                            message: response.message || 'Upload gagal',
                            filename: null,
                        });
                    }
                } catch (error) {
                    resolve({
                        success: false,
                        message: 'Respons server tidak valid',
                        filename: null,
                    });
                }
            });

            // Error handler
            xhr.addEventListener('error', () => {
                Swal.close();
                resolve({
                    success: false,
                    message: 'Koneksi terputus. Periksa jaringan Anda.',
                    filename: null,
                });
            });

            // Timeout handler (30 seconds)
            xhr.addEventListener('timeout', () => {
                Swal.close();
                resolve({
                    success: false,
                    message: 'Upload timeout. Coba lagi dengan koneksi lebih stabil.',
                    filename: null,
                });
            });

            xhr.open('POST', url);
            xhr.timeout = 30000; // 30 seconds
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        });
    }

    /**
     * Confirm finalization
     */
    async function confirmFinalize(filename, nomor, type) {
        const config = REPORT_TYPES[type];

        const result = await Swal.fire({
            icon: 'question',
            title: 'Finalisasi Pelaporan?',
            html: `
                <div class="text-start">
                    <p class="mb-2">
                        <i class="bi bi-file-pdf text-danger"></i>
                        File <strong>${filename}</strong> berhasil diunggah.
                    </p>
                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            Setelah difinalisasi, Anda tidak dapat mengubah file.
                        </small>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-lg"></i> Finalisasi Sekarang',
            cancelButtonText: 'Nanti Saja',
            confirmButtonColor: '#198754',
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary',
            },
        });

        return result.isConfirmed;
    }

    /**
     * Finalize report
     */
    async function finalize(assignmentId, type) {
        const url = getFinalizeUrl(assignmentId, type);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            return {
                success: response.ok && data.success,
                message: data.message || 'Finalisasi gagal',
            };
        } catch (error) {
            return {
                success: false,
                message: 'Koneksi terputus',
            };
        }
    }

    /**
     * Get upload URL based on type
     */
    function getUploadUrl(assignmentId, type) {
        const routes = {
            dokumen: `/pelaporan/${assignmentId}/dokumen/upload`,
            ak: `/pelaporan/${assignmentId}/validasi-ak/upload`,
            al: `/pelaporan/${assignmentId}/al/upload`,
            ak_banding: `/pelaporan/banding/${assignmentId}/validasi-ak-banding/upload`,
            al_banding: `/pelaporan/banding/${assignmentId}/al-banding/upload`,
        };
        return routes[type] || '';
    }

    /**
     * Get finalize URL based on type
     */
    function getFinalizeUrl(assignmentId, type) {
        const routes = {
            dokumen: `/pelaporan/${assignmentId}/dokumen/finalize`,
            ak: `/pelaporan/${assignmentId}/validasi-ak/finalize`,
            al: `/pelaporan/${assignmentId}/al/finalize`,
            ak_banding: `/pelaporan/banding/${assignmentId}/validasi-ak-banding/finalize`,
            al_banding: `/pelaporan/banding/${assignmentId}/al-banding/finalize`,
        };
        return routes[type] || '';
    }

    /**
     * Show error message
     */
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: message,
        });
    }

    // Public API
    return {
        init,
    };
})();

// Auto-initialize if CSRF token available
if (document.querySelector('meta[name="csrf-token"]')) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    PelaporanModule.init(token);
}
