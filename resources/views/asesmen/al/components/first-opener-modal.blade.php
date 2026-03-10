{{--
    resources/views/asesmen/al/components/first-opener-modal.blade.php
    Include di show.blade.php dan upload-excel.blade.php dengan:
        @include('asesmen.al.components.first-opener-modal')
    Letakkan di dalam @push('scripts') atau setelah DOMContentLoaded
--}}

@push('scripts')
@php
$firstOpenerName = $firstOpenerUser?->name ?? null;
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isFirstVisit = @json($isFirstVisitForMe);
        const isFirstOpener = @json($isFirstOpener);
        const firstOpenerName = @json($firstOpenerName);
        const currentPage = '{{ str_contains(request()->route()->getName(), "upload") ? "upload" : "show" }}';

        // ── Tampilkan modal hanya saat pertama kali buka halaman ───
        if (isFirstVisit) {

            if (isFirstOpener) {
                // ── KASUS 1: Saya adalah asesor pertama yang membuka ─
                Swal.fire({
                    icon: 'info'
                    , title: '<i class="bi bi-person-check-fill text-success"></i> Anda Membuka Pertama Kali'
                    , html: `
                    <div class="text-start">
                        <p>
                            Selamat! Anda adalah <strong>asesor pertama</strong> yang membuka halaman
                            penilaian AL ini.
                        </p>
                        <div class="alert alert-success text-start mb-3" style="border-left:4px solid #198754;">
                            <i class="bi bi-shield-check me-2"></i>
                            <strong>Hak Finalisasi Diberikan kepada Anda</strong><br>
                            <small>
                                Mulai saat ini, hanya Anda yang dapat mengisi, mengupload, dan
                                <strong>memfinalisasi</strong> penilaian AL ini.
                            </small>
                        </div>
                        <div class="alert alert-warning text-start mb-0" style="border-left:4px solid #ffc107;">
                            <i class="bi bi-people me-2"></i>
                            <strong>Asesor Lain</strong><br>
                            <small>
                                Asesor lain dalam tim tetap dapat <em>melihat</em> halaman penilaian,
                                namun <strong>tidak dapat mengubah data atau memfinalisasi</strong>.
                            </small>
                        </div>
                    </div>
                `
                    , confirmButtonText: '<i class="bi bi-check-circle"></i> Mengerti, Lanjutkan'
                    , confirmButtonColor: '#198754'
                    , allowOutsideClick: false
                    , width: '560px'
                    , customClass: {
                        popup: 'text-start'
                    }
                , });

            } else {
                // ── KASUS 2: Asesor lain sudah lebih dulu membuka ───
                Swal.fire({
                    icon: 'warning'
                    , title: '<i class="bi bi-exclamation-triangle-fill text-warning"></i> Penilaian Sudah Diklaim'
                    , html: `
                    <div class="text-start">
                        <p>
                            Asesor <strong>${firstOpenerName ?? 'lain'}</strong> sudah lebih dulu
                            membuka halaman penilaian AL ini dan menjadi <strong>pemegang hak finalisasi</strong>.
                        </p>
                        <div class="alert alert-danger text-start mb-3" style="border-left:4px solid #dc3545;">
                            <i class="bi bi-lock-fill me-2"></i>
                            <strong>Anda Tidak Dapat Memfinalisasi</strong><br>
                            <small>
                                Hanya <strong>${firstOpenerName ?? 'asesor pertama'}</strong> yang dapat
                                mengisi, mengupload, dan memfinalisasi penilaian AL ini.
                            </small>
                        </div>
                        <div class="alert alert-info text-start mb-0" style="border-left:4px solid #0dcaf0;">
                            <i class="bi bi-eye me-2"></i>
                            <strong>Yang Dapat Anda Lakukan</strong><br>
                            <small>
                                Anda tetap dapat <em>melihat</em> seluruh data penilaian dan
                                mendownload hasilnya, namun tidak dapat mengubah atau mengirim penilaian.
                            </small>
                        </div>
                    </div>
                `
                    , confirmButtonText: '<i class="bi bi-eye"></i> Mengerti, Lanjutkan Melihat'
                    , confirmButtonColor: '#0d6efd'
                    , allowOutsideClick: false
                    , width: '560px'
                    , customClass: {
                        popup: 'text-start'
                    }
                , });
            }

        } else if (!isFirstOpener) {
            // ── KASUS 3: Bukan first visit, tapi bukan editor ───────
            // Tidak perlu modal, cukup persistent alert (sudah ada di blade)
        }

    });

</script>
@endpush
