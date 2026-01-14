@extends('layouts.template.app')

@section('title', 'Detail Pengajuan - ' . $pengajuan->nomor_pengajuan)

@push('styles')
<style>
    .action-card {
        border-left: 4px solid #0d6efd;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #932136, #870820);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
    }

    .timeline-step {
        position: relative;
        padding-left: 30px;
    }

    .timeline-step::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 30px;
        bottom: -10px;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-step:last-child::before {
        display: none;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-file-earmark-text"></i>
                {{ $pengajuan->nomor_pengajuan }}
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->studyProgram->name }} - {{ $pengajuan->tahun_akreditasi }}
            </p>
        </div>
        <div>
            <span class="badge {{ $pengajuan->status_badge_class }} fs-6">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-12 col-lg-8">

            {{-- Step 3: Kirim Template LED --}}
            @if($pengajuan->status === App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA)
            @include('asesmen.de.partials.kirim-template')
            @endif

            {{-- Step 6-7: Validasi LED --}}
            @include('asesmen.de.partials.validasi-led')

            {{-- Assign Validator Section --}}
            @include('asesmen.de.partials.assign-validator')

            {{-- Step 7: Lapor Hasil Validasi --}}
            @if($pengajuan->status === App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED)
            @include('asesmen.de.partials.lapor-validasi')
            @endif

            {{-- Step 8: Approve ke AK --}}
            @if(in_array($pengajuan->status, [
            App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN
            ]))
            @include('asesmen.de.partials.approve-ak')
            @endif

            {{-- Step 4: Pembayaran --}}
            @include('asesmen.de.partials.pembayaran')

            {{-- Informasi Pengajuan --}}
            @include('asesmen.de.partials.info-pengajuan')

            {{-- Info Pembayaran --}}
            @if($pengajuan->pembayaran)
            @include('asesmen.de.partials.info-pembayaran')
            @endif

            {{-- Dokumen --}}
            @include('asesmen.de.partials.dokumen')
        </div>

        <!-- Sidebar -->
        <div class="col-md-12 col-lg-4">
            {{-- Timeline (20 Steps) --}}
            @include('asesmen.de.partials.timeline')

            {{-- Log Aktivitas --}}
            @include('asesmen.de.partials.log-aktivitas')
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle between link and upload
    const metodeKirim = document.querySelectorAll('input[name="metode_kirim"]')
    if (metodeKirim) metodeKirim.forEach(radio => {
        radio.addEventListener('change', function() {
            const divLink = document.getElementById('divLink');
            const divUpload = document.getElementById('divUpload');
            const templateLink = document.getElementById('template_link');
            const borangTemplate = document.getElementById('borang_template');
            const infoMetode = document.getElementById('infoMetode');

            if (this.value === 'link') {
                divLink.style.display = 'block';
                divUpload.style.display = 'none';
                templateLink.required = true;
                borangTemplate.required = false;
                infoMetode.textContent = 'Link template akan dikirim ke email prodi';
            } else {
                divLink.style.display = 'none';
                divUpload.style.display = 'block';
                templateLink.required = false;
                borangTemplate.required = true;
                infoMetode.textContent = 'File template akan diupload dan dapat didownload oleh prodi';
            }
        });
    });

    // Use default link
    function useDefaultLink() {
        const template_link = document.getElementById('template_link');
        if (template_link) {
            template_link.value = "{{ url('pengajuan/' . $pengajuan->id . '/borang/download-template') }}";
        }
    }

    // Clear link
    function clearLink() {
        const template_link = document.getElementById('template_link');
        if (template_link) {
            template_link.value = '';
        }
    }

    // Form validation - Kirim Template LED
    const formKirimBorang = document.getElementById('formKirimBorang');
    if (formKirimBorang) {
        formKirimBorang.addEventListener('submit', function(e) {
            const metodeKirimChecked = document.querySelector('input[name="metode_kirim"]:checked')
            const metode = metodeKirimChecked ? metodeKirimChecked.value : '';

            if (metode === 'link') {
                const link = document.getElementById('template_link').value;
                if (!link) {
                    e.preventDefault();
                    alert('Mohon masukkan URL template!');
                    return false;
                }

                // Validate URL format
                try {
                    new URL(link);
                } catch (error) {
                    e.preventDefault();
                    alert('Format URL tidak valid!');
                    return false;
                }
            } else if (metode === 'upload') {
                const borangTemplate = document.getElementById('borang_template')
                const file = borangTemplate ? borangTemplate.files[0] : null;
                if (!file) {
                    e.preventDefault();
                    alert('Mohon pilih file template!');
                    return false;
                }

                // Validate file size (10 MB)
                if (file.size > 10 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar! Maksimal 10 MB.');
                    return false;
                }
            }

            return true;
        });
    }

    // Auto-fill default link on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('template_link')) {
            useDefaultLink();
        }
    });

    // ============================================
    // ASSIGN VALIDATOR
    // ============================================

    // Validator preview on select
    const validatorSelect = document.getElementById('id_validator');
    if (validatorSelect) {
        validatorSelect.addEventListener('change', function() {
            const preview = document.getElementById('validatorPreview');
            const selectedOption = this.options[this.selectedIndex];

            if (this.value) {
                document.getElementById('previewName').textContent = selectedOption.text.split('(')[0].trim();
                document.getElementById('previewEmail').textContent = selectedOption.dataset.email || '';
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    }

    // Assign validator with confirmation
    async function assignValidator() {
        const form = document.getElementById('formAssignValidator');
        const validatorId = document.getElementById('id_validator').value;
        const validatorName = document.getElementById('id_validator').options[document.getElementById('id_validator').selectedIndex].text.split('(')[0].trim();
        const catatan = document.getElementById('catatan_de').value;

        if (!validatorId) {
            Swal.fire({
                icon: 'warning'
                , title: 'Pilih Validator'
                , text: 'Mohon pilih validator terlebih dahulu!'
            });
            return;
        }

        const result = await Swal.fire({
            icon: 'question'
            , title: 'Konfirmasi Assignment'
            , html: `Assign validator <strong>${validatorName}</strong> untuk review LED?`
            , showCancelButton: true
            , confirmButtonText: 'Ya, Assign!'
            , cancelButtonText: 'Batal'
            , confirmButtonColor: '#0d6efd'
        , });

        if (result.isConfirmed) {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                    , body: JSON.stringify({
                        id_validator: validatorId
                        , catatan_de: catatan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: data.message
                        , timer: 2000
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal!'
                        , text: data.message
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-person-check"></i> Assign Validator';
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Error!'
                    , text: 'Terjadi kesalahan saat assign validator.'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-person-check"></i> Assign Validator';
            }
        }
    }

    // Cancel assignment
    async function cancelAssignment(assignmentId) {
        const result = await Swal.fire({
            icon: 'warning'
            , title: 'Batalkan Assignment?'
            , text: 'Validator akan dihapus dari assignment ini.'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Batalkan!'
            , cancelButtonText: 'Tidak'
            , confirmButtonColor: '#dc3545'
        , });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/de/pengajuan/validation/${assignmentId}/cancel`, {
                    method: 'DELETE'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: data.message
                        , timer: 2000
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal!'
                        , text: data.message
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Error!'
                    , text: 'Terjadi kesalahan saat membatalkan assignment.'
                });
            }
        }
    }

    // Reassign validator
    async function reassignValidator(assignmentId) {
        const {
            value: formValues
        } = await Swal.fire({
            title: 'Reassign Validator'
            , html: `
                <div class="mb-3 text-start">
                    <label class="form-label">Pilih Validator Baru</label>
                    <select id="swal-validator" class="form-select">
                        <option value="">-- Pilih Validator --</option>
                        @foreach($validators ?? [] as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Catatan (Opsional)</label>
                    <textarea id="swal-catatan" class="form-control" rows="3" placeholder="Catatan untuk validator baru..."></textarea>
                </div>
            `
            , showCancelButton: true
            , confirmButtonText: 'Reassign'
            , cancelButtonText: 'Batal'
            , preConfirm: () => {
                const validatorId = document.getElementById('swal-validator').value;
                const catatan = document.getElementById('swal-catatan').value;

                if (!validatorId) {
                    Swal.showValidationMessage('Pilih validator terlebih dahulu!');
                    return false;
                }

                return {
                    validatorId
                    , catatan
                };
            }
        });

        if (formValues) {
            try {
                const response = await fetch(`/de/pengajuan/validation/${assignmentId}/reassign`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                    , body: JSON.stringify({
                        id_validator: formValues.validatorId
                        , catatan_de: formValues.catatan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: data.message
                        , timer: 2000
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal!'
                        , text: data.message
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Error!'
                    , text: 'Terjadi kesalahan saat reassign validator.'
                });
            }
        }
    }

    // ============================================
    // BORANG REVISION HANDLING
    // ============================================

    // Send revision notification to prodi
    async function handleRevision(pengajuanId) {
        const result = await Swal.fire({
            icon: 'question'
            , title: 'Kirim Notifikasi Revisi?'
            , text: 'Email dengan poin revisi akan dikirim ke prodi.'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Kirim!'
            , cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/de/pengajuan/${pengajuanId}/handle-borang-revision`, {
                    method: 'GET'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: 'Notifikasi revisi telah dikirim ke prodi.'
                        , timer: 2000
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal!'
                        , text: data.message || 'Gagal mengirim notifikasi'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Error!'
                    , text: 'Terjadi kesalahan saat mengirim notifikasi.'
                });
            }
        }
    }

    // Reassign after prodi revision
    async function reassignAfterRevision(pengajuanId) {
        const result = await Swal.fire({
            icon: 'question'
            , title: 'Kembalikan ke Validator?'
            , html: 'LED yang sudah direvisi akan dikirim kembali ke validator untuk review ulang.'
            , showCancelButton: true
            , confirmButtonText: 'Ya, Kembalikan!'
            , cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/de/pengajuan/${pengajuanId}/reassign-after-revision`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: 'LED telah dikembalikan ke validator.'
                        , timer: 2000
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal!'
                        , text: data.message || 'Gagal reassign'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Error!'
                    , text: 'Terjadi kesalahan saat reassign.'
                });
            }
        }
    }

</script>
@endpush
@endsection
