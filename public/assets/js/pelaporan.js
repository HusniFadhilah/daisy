(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function buildUrl(tmpl, id) {
        return (tmpl || '').replace('__ID__', id);
    }

    async function postUpload(url, file) {
        const fd = new FormData();
        fd.append('file', file);

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd
        });

        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
    }

    async function postFinalize(url) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });

        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-open-pelaporan');
        if (!btn) return;

        const type = btn.dataset.type;
        const assignmentId = btn.dataset.assignmentId;
        const nomor = btn.dataset.nomor || '';

        const cfgAll = window.PELAPORAN_CFG || {};
        const cfg = cfgAll[type];

        if (!cfg) return Swal.fire('Error', 'Konfigurasi pelaporan tidak ditemukan.', 'error');

        const { value: file } = await Swal.fire({
            title: cfg.title,
            html: `
        <div class="text-start">
          <p class="mb-2">Upload <b>${cfg.fileLabel}</b> <small class="text-muted">(PDF, max 5MB)</small></p>
          <input id="pvFile" type="file" class="form-control" accept="application/pdf">
          <div class="form-text mt-2">Setelah upload berhasil, Anda bisa finalisasi.</div>
        </div>
      `,
            showCancelButton: true,
            confirmButtonText: 'Upload',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const f = document.getElementById('pvFile')?.files?.[0];
                if (!f) return Swal.showValidationMessage('Silakan pilih file PDF.');
                if (f.type !== 'application/pdf') return Swal.showValidationMessage('File harus PDF.');
                if (f.size > 5 * 1024 * 1024) return Swal.showValidationMessage('Ukuran file maksimal 5MB.');
                return f;
            }
        });

        if (!file) return;

        try {
            const uploadUrl = buildUrl(cfg.upload, assignmentId);
            const up = await postUpload(uploadUrl, file);

            if (!up.ok || !up.data?.success) {
                return Swal.fire('Gagal', up.data?.message || 'Gagal upload laporan.', 'error');
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Finalisasi?',
                html: `
          <div class="text-start">
            <p>File <b>${up.data?.doc?.original_name || 'laporan'}</b> berhasil diunggah.</p>
            <p class="mb-0">Jika finalisasi, status pengajuan <b>${nomor}</b> menjadi <b>${cfg.finalizeLabel}</b>.</p>
          </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Finalisasi',
                cancelButtonText: 'Nanti dulu'
            });

            if (!confirm.isConfirmed) {
                await Swal.fire('Tersimpan', 'Laporan sudah diunggah. Anda bisa finalisasi kapan saja.', 'info');
                window.location.reload();
                return;
            }

            const finUrl = buildUrl(cfg.finalize, assignmentId);
            const fin = await postFinalize(finUrl);

            if (!fin.ok || !fin.data?.success) {
                return Swal.fire('Gagal', fin.data?.message || 'Gagal finalisasi.', 'error');
            }

            await Swal.fire('Berhasil', fin.data?.message || 'Berhasil.', 'success');
            window.location.reload();
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Terjadi error saat memproses pelaporan.', 'error');
        }
    });
})();
