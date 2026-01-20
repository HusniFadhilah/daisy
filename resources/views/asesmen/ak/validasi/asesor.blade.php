{{-- resources/views/asesmen/ak/validasi/asesor.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Penilaian Asesor - ' . $asesmen->name)

@section('content')
<div class="container-fluid py-3">
    {{-- Header Card --}}
    <div class="card mb-4 header-card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">

                <!-- Judul + badge -->
                <div class="flex-grow-1">
                    <h3 class="mb-1 text-wrap text-break">Validasi Penilaian Asesor</h3>
                    <p class="text-muted mb-0 text-wrap text-break">
                        {{ $asesmen->name }}
                        <span class="badge bg-primary ms-2">
                            {{ strtoupper($jenisAsesmen) }}
                        </span>
                    </p>
                </div>

                <!-- Tombol -->
                <a href="{{ route('ak.validasi.index') }}" class="btn btn-outline-secondary mt-2 mt-md-0">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

            {{-- Dynamic Asesor Info Cards --}}
            <div class="row mt-4">
                @foreach($asesors as $index => $asesor)
                @php
                $colors = [
                ['border' => 'primary', 'bg' => 'primary'],
                ['border' => 'warning', 'bg' => 'warning'],
                ['border' => 'success', 'bg' => 'success'],
                ['border' => 'info', 'bg' => 'info'],
                ['border' => 'purple', 'bg' => 'purple'],
                ];
                $color = $colors[$index % count($colors)];
                $progress = $asesorProgress[$asesor->id_user] ?? ['completed' => 0, 'total' => 0, 'completion_percentage' => 0];
                @endphp

                <div class="col-md-12 col-lg-6 mb-3">
                    <div class="card border-{{ $color['border'] }}">

                        <!-- Card Header -->
                        <div class="card-header bg-{{ $color['bg'] }} text-white
                            d-flex flex-column flex-md-row
                            align-items-start align-items-md-center gap-2">

                            <h6 class="mb-0 flex-grow-1 text-wrap text-break">
                                <i class="bi bi-person"></i> Asesor {{ $asesor->urutan_asesor }}
                            </h6>

                            @if($asesor->status_pekerjaan === 'revision_required')
                            <span class="badge bg-danger ms-md-auto">
                                <i class="bi bi-exclamation-triangle"></i> Revisi
                            </span>
                            @elseif($asesor->status_pekerjaan === 'submitted')
                            <span class="badge bg-success ms-md-auto">
                                <i class="bi bi-check-circle"></i> Submitted
                            </span>
                            @elseif($asesor->status_pekerjaan === 'approved')
                            <span class="badge bg-success ms-md-auto">
                                <i class="bi bi-patch-check"></i> Approved
                            </span>
                            @endif
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <!-- Avatar -->
                                <div class="avatar-circle me-3 bg-{{ $color['bg'] }}">
                                    {{ substr($asesor->user->name, 0, 2) }}
                                </div>

                                <!-- Nama + Email -->
                                <div>
                                    <h6 class="mb-0 text-truncate">{{ $asesor->user->name }}</h6>
                                    <span class="badge bg-light text-dark d-block text-break">
                                        {{ $asesor->user->email }}
                                    </span>
                                </div>
                            </div>

                            <!-- Progress -->
                            <div class="mt-3">
                                <div class="d-flex justify-content-between">
                                    <span>Progress Penilaian:</span>
                                    <strong>{{ $progress['completed'] }}/{{ $progress['total'] }}</strong>
                                </div>
                                <div class="progress mt-2" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $color['bg'] }}" style="width: {{ $progress['completion_percentage'] }}%">
                                        {{ $progress['completion_percentage'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card-footer bg-white py-3">
            @php
            $jenisPelaporan = $jenisAsesmen; // ak | al
            $assignmentIdPelaporan = $assignment->id;

            $typeDoc = $jenisPelaporan === 'ak' ? 'laporan_validasi_ak' : 'laporan_al';

            $sudahDilaporkan = \App\Models\AsesmenDocument::where('id_asesmen', $assignment->id_asesmen)
            ->where('type', $typeDoc)
            ->where('is_active', true)
            ->exists();

            $nomorTampil = '';
            if ($asesmen->pengajuan && $asesmen->pengajuan->nomor_pengajuan) $nomorTampil = $asesmen->pengajuan->nomor_pengajuan;
            if (!$nomorTampil && isset($asesmen->code)) $nomorTampil = $asesmen->code;
            if (!$nomorTampil) $nomorTampil = $asesmen->name;

            $labelBtn = $jenisPelaporan === 'ak' ? 'Laporan Penilaian Kecukupan LED Program Studi (LHK)' : 'Pelaporan AL';
            @endphp

            @if($isApproved)
            <div class="alert alert-success alert-permanent mb-3">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Penilaian Telah Disetujui!</strong>
                <p class="mb-0">Validasi {{ $sudahDilaporkan ? 'dan Pelaporan AK ' : '' }}telah diselesaikan dan lolos untuk tahap selanjutnya (Asesmen Lapangan/AL). {{ !$sudahDilaporkan ? 'Silahkan buat & finalisasi Laporan Penilaian Kecukupan LED Program Studi (LHK) pada tombol di bawah berikut' : '' }}</p>

                @if($sudahDilaporkan)
                <div class="mt-2">
                    <span class="badge bg-success text-wrap">
                        <i class="bi bi-check-circle"></i> {{ $labelBtn }} telah Dibuat
                    </span>
                </div>
                @endif

                @if(!$sudahDilaporkan)
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-success js-open-pelaporan" data-type="{{ $jenisPelaporan }}" data-assignment-id="{{ $assignmentIdPelaporan }}" data-nomor="{{ $nomorTampil }}">
                        <i class="bi bi-file-earmark-text"></i> {{ $labelBtn }}
                    </button>
                </div>
                @endif
            </div>
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    @if($allValidated && !$isApproved)
                    <button type="button" class="btn btn-success btn-md" id="btnApproveAll">
                        <i class="bi bi-check-all"></i> Setujui Semua Penilaian
                    </button>
                    @elseif($isApproved)
                    <button type="button" class="btn btn-success btn-md" disabled>
                        <i class="bi bi-check-circle"></i> Sudah Disetujui
                    </button>
                    @else
                    <button type="button" class="btn btn-secondary btn-md" disabled>
                        <i class="bi bi-hourglass-split"></i> Validasi Belum Lengkap
                    </button>
                    @endif
                </div>

                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">
                        Progress Validasi:
                        <strong>{{ $validatedCount }}/{{ $totalElemen }}</strong>
                    </span>
                    <div class="progress" style="height: 20px; width: 200px;">
                        <div class="progress-bar bg-success" style="width: {{ $validationPercentage }}%">
                            {{ $validationPercentage }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validator Matrix --}}
    @include('asesmen.ak.components.validator-matrix', ['asesors' => $asesors,'kriterias' => $kriterias,'jenjangs' =>$jenjangs])

    {{-- Quick Actions Panel --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="bi bi-lightning"></i> Quick Actions
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if (app()->environment('local'))
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-success w-100" id="btnValidateAllAgreed" {{ $isApproved ? 'disabled' : '' }}>
                        <i class="bi bi-check-circle"></i>
                        <div>Validasi Semua yang Sama</div>
                        <small>Otomatis approve nilai yang sama dari semua asesor</small>
                    </button>
                </div>
                @endif
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-warning w-100" id="btnReviewDifferences">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>Review Perbedaan</div>
                        <small>Lihat hanya penilaian yang berbeda antar asesor</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Approve All Confirmation --}}
<div class="modal fade" id="approveAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-all"></i> Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin <strong>menyetujui semua penilaian</strong>?</p>
                <div class="alert alert-warning alert-permanent">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Setelah disetujui, asesor tidak dapat mengubah penilaian mereka.
                </div>
                <p>Total yang akan disetujui: <strong>{{ $totalElemen }} elemen</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="confirmApproveAll">
                    <i class="bi bi-check-circle"></i> Ya, Setujui Semua
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include Modals --}}
@include('asesmen.ak.components.modal-validasi-detail')
@include('asesmen.ak.components.modal-detail-validasi')

@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #932136;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
    }

    .avatar-circle.bg-warning {
        background: #ff9800;
    }

    .avatar-circle.bg-success {
        background: #4caf50;
    }

    .avatar-circle.bg-info {
        background: #00bcd4;
    }

    .avatar-circle.bg-purple {
        background: #9c27b0;
    }

    .header-card {
        border: none;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    /* ✅ Quick Select Average Button */
    .quick-select-average {
        position: relative;
        border: 2px dashed #2196f3 !important;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        color: #1976d2 !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .quick-select-average:hover {
        background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    }

    .quick-select-average.active {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%) !important;
        color: white !important;
        border-color: #1565c0 !important;
    }

    .quick-select-average i {
        font-size: 1.2rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    /* ✅ Quick Select Buttons Group */
    #quickSelectButtons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    #quickSelectButtons button {
        flex: 1;
        min-width: 100px;
        padding: 0.75rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    #quickSelectButtons button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #quickSelectButtons button.active {
        box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.3);
        transform: scale(1.05);
    }

    #quickSelectButtons button strong {
        font-size: 1.5rem;
        display: block;
    }

    #quickSelectButtons button small {
        font-size: 0.75rem;
        opacity: 0.8;
    }

</style>
@endpush

@push('scripts')
<script>
    window.PELAPORAN_CFG = window.PELAPORAN_CFG || {};

    window.PELAPORAN_CFG.ak = {
        title: 'Rekap AK dan Validasi AK'
        , label: 'Laporan Penilaian Kecukupan LED Program Studi (LHK)'
        , upload: @json(route('pelaporan.validasiAk.upload', ['assignment' => '__ID__']))
        , finalize: @json(route('pelaporan.validasiAk.finalize', ['assignment' => '__ID__']))
        , fileLabel: 'Laporan Penilaian Kecukupan LED Program Studi (LHK) telah selesai'
        , finalizeLabel: 'Asesmen Kecukupan telah Dilaporkan'
        , additionalDescription: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
        •	Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
        •	Proses penilaian LED oleh Asesor.
        •	Validasi Penilaian Kecukupan Asesor oleh Validator
        •	Penyampaian Informasi Kepada DE untuk dilakukan tahap Asesmen Lapangan`
    };

    window.PELAPORAN_CFG.al = {
        title: 'Rekap AL dan Pelaporan AL'
        , label: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)'
        , upload: @json(route('pelaporan.al.upload', ['assignment' => '__ID__']))
        , finalize: @json(route('pelaporan.al.finalize', ['assignment' => '__ID__']))
        , fileLabel: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)'
        , finalizeLabel: 'Pelaporan AL Telah Selesai'
        , additionalDescription: `Dokumen yang sudah digabungkan, yang diperlukan isinya adalah:
        •	Penunjukan tugas Asesor untuk melaksanakan Penilaian LED
        •	Proses peneliaan LED oleh Asesor.
        •	Validasi Penilaian Kecukupan Asesor Oleh Validator
        •	Penyampaian Informasi Kepada DE tentang:
            o	Lokasi AL
            o	Perjalan asesor ke lokasi AL
            o	Berita Acara yang menyatakan AL telah dilaksanakan dan disepakati
        •	Rekomendasi Penetapan Hasil Akreditasi`
    };

</script>

<script src="{{ asset('assets/js/pelaporan.js') }}"></script>
<script>
    const envIsLocal = "{{ app()->environment('local') }}"
    const idAsesmen = "{{ $asesmen->id }}";
    const jenisAsesmen = "{{ $jenisAsesmen }}";

    // Store asesor IDs dynamically
    const asesorIds = @json($asesorIds);
    const asesorData = @json($asesorData);

    /**
     * ============================================
     * POPULATE MODAL CONTENT (GLOBAL)
     * ============================================
     */
    function populateModalContent(data) {
        const {
            elemen
            , penilaian_data
            , hasDifference
            , total_asesors
        } = data;

        // ✅ 1. Populate Elemen Info
        document.getElementById('detailKriteria').textContent = elemen.kriteria ?
            `${elemen.kriteria.kode_kriteria} - ${elemen.kriteria.nama_kriteria}` :
            '-';
        document.getElementById('detailKodeElemen').textContent = elemen.kode_elemen;
        document.getElementById('detailElemenStandar').textContent = elemen.pernyataan_elemen;

        // Indikator
        let indikatorHtml = '';
        if (elemen.indikator && elemen.indikator.length > 0) {
            indikatorHtml = '<ul class="mb-0 ps-3">';
            elemen.indikator.forEach(ind => {
                indikatorHtml += `
                <li class="small">
                    <strong>${ind.kode_indikator}:</strong> ${ind.deskripsi_indikator}
                </li>
            `;
            });
            indikatorHtml += '</ul>';
        } else {
            indikatorHtml = '<small class="text-muted">-</small>';
        }
        document.getElementById('detailIndikator').innerHTML = indikatorHtml;

        // ✅ 2. Set hidden elemen ID
        document.getElementById('validasiElemenId').value = elemen.id;

        // ✅ 3. Populate Penilaian Asesors (Dynamic Grid)
        const colors = ['#2196f3', '#ff9800', '#4caf50', '#9c27b0', '#00bcd4'];
        let htmlAsesors = '';
        let skorList = [];

        penilaian_data.forEach((item, index) => {
            const asesor = item.asesor;
            const penilaian = item.penilaian;
            const color = colors[index % colors.length];
            const skor = penilaian.skor;

            if (skor !== null && skor !== undefined) {
                skorList.push(skor);
            }

            htmlAsesors += `
            <div class="col-md-12 col-lg-6 mb-3">
                <div class="card asesor-card border-2" style="border-left: 4px solid ${color};" data-asesor-id="${asesor.id}">
                    <div class="card-header asesor-card-header" style="background: ${color};">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle-modal me-2" style="background: white; color: ${color};">
                                    ${asesor.name.substring(0, 2).toUpperCase()}
                                </div>
                                <div class="text-white">
                                    <h6 class="mb-0">${asesor.name}</h6>
                                    <small>Asesor ${index + 1}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Kategori:</small>
                            <div>
                                <span class="badge ${getSkorBadgeClass(skor)}">
                                    ${getSkorLabel(skor)}
                                </span>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Komentar:</small>
                            <p class="small mb-0 mt-1">${penilaian.komentar || '<em class="text-muted">Tidak ada komentar</em>'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        });

        document.getElementById('penilaianAsesorsContainer').innerHTML = htmlAsesors;

        // ✅ 4. Show/Hide Difference Alert
        const rowDifference = document.getElementById('rowDifference');
        if (rowDifference) {
            if (hasDifference) {
                const uniqueScores = [...new Set(skorList)];
                document.getElementById('differenceMessage').textContent =
                    `Terdapat perbedaan penilaian: Kategori ${uniqueScores.join(', ')}`;
                rowDifference.style.display = 'block';
            } else {
                rowDifference.style.display = 'none';
            }
        }

        // ✅ 5. Generate Quick Select Buttons (WITH AVERAGE)
        const quickSelectContainer = document.getElementById('quickSelectButtons');
        if (quickSelectContainer) {
            const uniqueScores = [...new Set(skorList)].sort();
            let quickBtns = '';

            // Add unique scores
            uniqueScores.forEach(skor => {
                quickBtns += `
                <button type="button" class="btn btn-outline-${getSkorButtonClass(skor)}"
                        onclick="selectSkor(${skor})">
                    <strong>${skor}</strong>
                    <small class="d-block">${getSkorLabelShort(skor)}</small>
                </button>
            `;
            });

            // ✅ ADD AVERAGE SCORE BUTTON
            if (skorList.length > 0) {
                const average = skorList.reduce((a, b) => a + b, 0) / skorList.length;
                const averageRounded = Math.round(average); // Bulatkan ke integer terdekat
                const averageDisplay = average.toFixed(1); // Tampilkan dengan 1 desimal

                quickBtns += `
                <button type="button" class="btn btn-outline-primary quick-select-average"
                        onclick="selectSkor(${averageRounded})"
                        title="Rata-rata dari ${skorList.join(', ')}">
                    <i class="bi bi-calculator"></i>
                    <strong>${averageDisplay}</strong>
                    <small class="d-block">Rata-rata (≈${averageRounded})</small>
                </button>
            `;
            }

            quickSelectContainer.innerHTML = quickBtns;
        }

        // ✅ 6. Generate Asesor Checkboxes for Revision
        const asesorCheckboxes = document.getElementById('asesorCheckboxes');
        if (asesorCheckboxes) {
            let checkboxHtml = '';

            penilaian_data.forEach((item, index) => {
                const asesor = item.asesor;
                const color = colors[index % colors.length];

                checkboxHtml += `
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="asesor_target_revisi[]"
                               value="${asesor.id}"
                               id="revisi_asesor_${asesor.id}" data-nama="${asesor.name}">
                        <label class="form-check-label" for="revisi_asesor_${asesor.id}">
                            <span class="badge" style="background: ${color};">Asesor ${index + 1}</span>
                            ${asesor.name}
                        </label>
                    </div>
                </div>
            `;
            });

            asesorCheckboxes.innerHTML = checkboxHtml;
        }
    }

    /**
     * ============================================
     * HELPER: SELECT SKOR
     * ============================================
     */
    function selectSkor(skor) {
        document.getElementById('skorFinal').value = skor;

        // Highlight selected button
        document.querySelectorAll('#quickSelectButtons button').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.closest('button').classList.add('active');
    }

    /**
     * ============================================
     * OPEN VALIDATION MODAL
     * ============================================
     */
    async function openValidationModal(elemenId) {
        const modal = showModalById('modalValidasiDetail');

        document.getElementById('loadingDetail').style.display = 'block';
        document.getElementById('detailContainer').style.display = 'none';

        try {
            const response = await fetch(`/ak/validasi/${idAsesmen}/elemen/${elemenId}`, {
                headers: {
                    'Accept': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal memuat data');
            }

            populateModalContent(result.data);

            document.getElementById('loadingDetail').style.display = 'none';
            document.getElementById('detailContainer').style.display = 'block';

        } catch (error) {
            console.error('Error loading elemen detail:', error);
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message || 'Gagal memuat detail penilaian'
            });
            modal.hide();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeQuickActions();
        initializeApproveAll();
        initializeValidationModal();

        /**
         * ============================================
         * QUICK ACTIONS
         * ============================================
         */
        function initializeQuickActions() {
            // Validate all agreed
            const btnValidateAllAgreed = document.getElementById('btnValidateAllAgreed');
            if (btnValidateAllAgreed) {
                btnValidateAllAgreed.addEventListener('click', async function() {
                    const confirmed = await Swal.fire({
                        icon: 'question'
                        , title: 'Validasi Otomatis?'
                        , text: 'Sistem akan otomatis menyetujui semua penilaian yang nilainya sama dari semua asesor.'
                        , showCancelButton: true
                        , confirmButtonText: 'Ya, Lanjutkan'
                        , cancelButtonText: 'Batal'
                    });

                    if (confirmed.isConfirmed) {
                        validateAllAgreed();
                    }
                });
            }

            // Review differences
            const btnReviewDifferences = document.getElementById('btnReviewDifferences');
            let reviewDiffActive = false;

            if (btnReviewDifferences) {
                btnReviewDifferences.addEventListener('click', function() {
                    const allRows = document.querySelectorAll('.validator-row');
                    const diffRows = document.querySelectorAll('.validator-row[data-has-diff="true"]');

                    if (!reviewDiffActive) {
                        allRows.forEach(row => {
                            if (row.dataset.hasDiff === 'true') {
                                row.classList.add('review-diff-focus');
                                row.classList.remove('review-diff-muted');
                            } else {
                                row.classList.add('review-diff-muted');
                                row.classList.remove('review-diff-focus');
                            }
                        });

                        reviewDiffActive = true;
                        this.innerHTML = '<i class="bi bi-x-circle"></i><div>Matikan Filter</div><small>Tampilkan semua elemen</small>';

                        Swal.fire({
                            icon: 'info'
                            , title: 'Review Perbedaan'
                            , text: `Menyorot ${diffRows.length} elemen dengan perbedaan penilaian.`
                            , timer: 2000
                        });
                    } else {
                        allRows.forEach(row => {
                            row.classList.remove('review-diff-muted', 'review-diff-focus');
                        });

                        reviewDiffActive = false;
                        this.innerHTML = '<i class="bi bi-exclamation-triangle"></i><div>Review Perbedaan</div><small>Lihat hanya penilaian yang berbeda antar asesor</small>';

                        Swal.fire({
                            icon: 'info'
                            , title: 'Filter Dimatikan'
                            , text: 'Semua elemen ditampilkan normal kembali.'
                            , timer: 1500
                        });
                    }
                });
            }
        }

        /**
         * ============================================
         * APPROVE ALL
         * ============================================
         */
        function initializeApproveAll() {
            const btnApproveAll = document.getElementById('btnApproveAll');
            if (btnApproveAll) {
                btnApproveAll.addEventListener('click', function() {
                    const modal = showModalById('approveAllModal');
                });
            }

            const confirmApproveAll = document.getElementById('confirmApproveAll');
            if (confirmApproveAll) {
                confirmApproveAll.addEventListener('click', async function() {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('approveAllModal'));
                    modal.hide();

                    showLoading();

                    try {
                        const response = await fetch(`/ak/validasi/${idAsesmen}/asesor/approve`, {
                            method: 'POST'
                            , headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                , 'Accept': 'application/json'
                                , 'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();
                        hideLoading();

                        if (data.success) {
                            await Swal.fire({
                                icon: 'success'
                                , title: 'Berhasil!'
                                , text: data.message
                                , confirmButtonColor: '#28a745'
                            });

                            window.location.reload();
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (error) {
                        hideLoading();
                        Swal.fire({
                            icon: 'error'
                            , title: 'Gagal'
                            , text: error.message
                        });
                    }
                });
            }
        }

        /**
         * ============================================
         * VALIDATION MODAL
         * ============================================
         */
        function initializeValidationModal() {
            document.querySelectorAll('.btn-validate').forEach(btn => {
                btn.addEventListener('click', function() {
                    const elemenId = this.dataset.elemenId;
                    openValidationModal(elemenId);
                });
            });

            setupModalEventListeners();
        }

        /**
         * ============================================
         * SETUP MODAL EVENT LISTENERS
         * ============================================
         */
        function setupModalEventListeners() {
            const statusValidasi = document.getElementById('statusValidasi');
            if (statusValidasi) {
                ['change', 'keyup'].forEach(event => {
                    statusValidasi.addEventListener(event, function() {
                        const btnRevision = document.getElementById('btnSaveRevision');
                        const btnValidasi = document.getElementById('btnSaveValidasi');
                        const revisionSection = document.getElementById('revisionSection');
                        const labelRequired = document.getElementById('labelCatatanRequired');
                        const quickSelectKategori = document.getElementById('quickSelectKategori');
                        const preferensiKategori = document.getElementById('preferensiKategori');

                        labelRequired.style.display = 'inline';
                        if (this.value === 'revision_required') {
                            btnRevision.style.display = 'inline-block';
                            btnValidasi.style.display = 'none';
                            revisionSection.style.display = 'block';

                            // Add danger border to all asesor cards
                            document.querySelectorAll('.asesor-card').forEach(card => {
                                card.classList.add('border-danger');
                            });
                        } else {
                            btnRevision.style.display = 'none';
                            btnValidasi.style.display = 'inline-block';
                            revisionSection.style.display = 'none';

                            // Remove danger border
                            document.querySelectorAll('.asesor-card').forEach(card => {
                                card.classList.remove('border-danger');
                            });
                        }
                        if (['validated', 'revision_required'].includes(this.value)) {
                            quickSelectKategori.style.display = 'block';
                            preferensiKategori.style.display = 'block';
                        } else {
                            quickSelectKategori.style.display = 'none';
                            preferensiKategori.style.display = 'none';
                        }
                    });
                });
            }

            const btnSaveValidasi = document.getElementById('btnSaveValidasi');
            if (btnSaveValidasi) {
                btnSaveValidasi.addEventListener('click', function() {
                    submitValidasi();
                });
            }

            const btnSaveRevision = document.getElementById('btnSaveRevision');
            if (btnSaveRevision) {
                btnSaveRevision.addEventListener('click', function() {
                    submitValidasi();
                });
            }
        }

        /**
         * ============================================
         * SUBMIT VALIDASI
         * ============================================
         */
        async function submitValidasi() {
            const elemenId = document.getElementById('validasiElemenId').value;
            const skorFinal = document.getElementById('skorFinal').value;
            const statusValidasi = document.getElementById('statusValidasi').value;
            const catatanValidator = document.getElementById('catatanValidator').value;
            // ✅ Build payload based on status
            let payload = {
                status: statusValidasi
                , skor_final: skorFinal
                , catatan_validator: catatanValidator
            };

            if (statusValidasi === 'revision_required') {
                const selectedChecks = Array.from(
                    document.querySelectorAll('input[name="asesor_target_revisi[]"]:checked')
                );
                if (selectedChecks.length === 0) {
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Pilih Asesor'
                        , text: 'Silakan centang asesor mana yang harus merevisi penilaian!'
                    });
                    return;
                }

                // ✅ Only add id_asesors if revision_required
                payload.id_asesors = selectedChecks.map(cb => cb.value);

                if (!catatanValidator.trim()) {
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Catatan Diperlukan'
                        , text: 'Silakan berikan catatan untuk revisi'
                    });
                    return;
                }
            }
            if (!statusValidasi) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Status Validasi Diperlukan'
                    , text: 'Silakan pilih setujui menjadi kategori final, atau minta revisi terlebih dahulu'
                });
                return;
            }
            if (!skorFinal && statusValidasi == 'validated') {
                Swal.fire({
                    icon: 'warning'
                    , title: 'Preferensi Kategori Diperlukan'
                    , text: 'Silakan pilih preferensi kategori terlebih dahulu'
                });
                return;
            }

            let confirmText = `Anda akan menyetujui penilaian ${statusValidasi === 'validated'? `dengan preferensi kategori: ${skorFinal}`: 'ini'}`;
            if (statusValidasi === 'revision_required') {
                const selectedAsesors = Array.from(
                    document.querySelectorAll('input[name="asesor_target_revisi[]"]:checked')
                ).map(cb => cb.dataset.nama);
                confirmText = `Anda akan meminta revisi kepada: ${selectedAsesors.join(', ')}`;
            }
            const labelConfirm =
                statusValidasi === 'validated' ?
                'Ya, Setujui' :
                statusValidasi === 'validated_diff' ?
                'Setujui dengan perbedaan nilai' :
                'Ya, Minta Revisi';
            const confirmResult = await Swal.fire({
                icon: 'question'
                , title: ['validated', 'validated_diff'].includes(statusValidasi) ? 'Setujui Penilaian?' : 'Minta Revisi?'
                , text: confirmText
                , showCancelButton: true
                , confirmButtonText: labelConfirm
                , cancelButtonText: 'Batal'
            });

            if (!confirmResult.isConfirmed) return;

            showLoading();

            try {
                const response = await fetch(`/ak/validasi/${idAsesmen}/elemen/${elemenId}/validate`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'Accept': 'application/json'
                        , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                    , body: JSON.stringify(payload) // ✅ Send payload instead of hardcoded object
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Gagal menyimpan validasi');
                }

                await Swal.fire({
                    icon: 'success'
                    , title: 'Berhasil!'
                    , text: result.message
                    , timer: 2000
                    , showConfirmButton: false
                });

                const modal = bootstrap.Modal.getInstance(document.getElementById('modalValidasiDetail'));
                modal.hide();

                window.location.reload();

            } catch (error) {
                console.error('Error submitting validation:', error);
                hideLoading();

                Swal.fire({
                    icon: 'error'
                    , title: 'Error'
                    , text: error.message || 'Gagal menyimpan validasi'
                });
            }
        }

        /**
         * ============================================
         * VALIDATE ALL AGREED
         * ============================================
         */
        async function validateAllAgreed() {
            showLoading();

            try {
                const response = await fetch(`/ak/validasi/${idAsesmen}/validate-agreed`, {
                    method: 'POST'
                    , headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        , 'Accept': 'application/json'
                        , 'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                hideLoading();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , html: `<p>${data.validated_count} elemen yang sama telah divalidasi otomatis.</p>`
                        , confirmButtonColor: '#28a745'
                    });

                    window.location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                hideLoading();
                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal'
                    , text: error.message
                });
            }
        }

        /**
         * ============================================
         * HELPERS
         * ============================================
         */
        function showLoading() {
            Swal.fire({
                title: 'Processing...'
                , allowOutsideClick: false
                , didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function hideLoading() {
            Swal.close();
        }
    });

    /**
     * ============================================
     * SHOW KOMENTAR POPOVER
     * ============================================
     */
    function showKomentarPopover(element, namaAsesor, skor, komentar) {
        const skorLabel = getSkorLabel(skor);

        Swal.fire({
            title: `💬 Komentar ${namaAsesor}`
            , html: `
                <div class="text-start">
                    <div class="mb-2">
                        <span class="badge ${getSkorBadgeClass(skor)}">${skorLabel}</span>
                    </div>
                    <div class="alert alert-light alert-permanent">
                        <strong>Justifikasi:</strong>
                        <p class="mb-0 mt-2">${komentar || '<em>Tidak ada komentar</em>'}</p>
                    </div>
                </div>
            `
            , icon: 'info'
            , confirmButtonText: 'Tutup'
            , width: '600px'
        });
    }

    /**
     * ============================================
     * SHOW VALIDASI DETAIL (VIEW-ONLY MODAL)
     * ============================================
     */
    async function showValidasiDetail(elemenId, validasiId) {
        const modal = showModalById('modalDetailValidasi');

        document.getElementById('loadingDetailValidasi').style.display = 'block';
        document.getElementById('contentDetailValidasi').style.display = 'none';

        try {
            const response = await fetch(`/ak/validasi/${idAsesmen}/detail/${elemenId}`, {
                headers: {
                    'Accept': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal memuat data');
            }

            populateValidasiDetail(result.data);

            document.getElementById('loadingDetailValidasi').style.display = 'none';
            document.getElementById('contentDetailValidasi').style.display = 'block';

        } catch (error) {
            console.error('Error loading validasi detail:', error);
            Swal.fire({
                icon: 'error'
                , title: 'Error'
                , text: error.message
            });
            modal.hide();
        }
    }

    /**
     * ============================================
     * POPULATE VALIDASI DETAIL
     * ============================================
     */
    function populateValidasiDetail(data) {
        const {
            elemen
            , validasi
            , penilaianAsesor
        } = data;

        // Elemen info
        const detailValidasiKriteria = document.getElementById('detailValidasiKriteria');
        if (detailValidasiKriteria) {
            detailValidasiKriteria.textContent = elemen.kriteria ?
                `${elemen.kriteria.kode_kriteria} - ${elemen.kriteria.nama_kriteria}` :
                '-';
        }
        const detailValidasiKode = document.getElementById('detailValidasiKode');
        if (detailValidasiKode) {
            detailValidasiKode.textContent = elemen.kode_elemen;
        }
        const detailValidasiElemen = document.getElementById('detailValidasiElemen');
        if (detailValidasiElemen) {
            detailValidasiElemen.textContent = elemen.pernyataan_elemen;
        }

        // Validasi info
        const statusBadge =
            validasi.status_validasi === 'validated' ?
            '<span class="badge bg-success">✅ Disetujui</span>' :
            validasi.status_validasi === 'validated_diff' ?
            '<span class="badge bg-info text-dark">ℹ️ Disetujui (Berbeda)</span>' :
            '<span class="badge bg-warning text-dark">⚠️ Perlu Revisi</span>';

        const detailValidasiStatus = document.getElementById('detailValidasiStatus');
        if (detailValidasiStatus) {
            detailValidasiStatus.innerHTML = statusBadge;
        }

        if (validasi.skor_final && envIsLocal) {
            document.querySelectorAll('.detailValidasiSkor').forEach(e => {
                e.classList.remove('d-none');
            });
            const skorBadge = document.getElementById('badgeSkorFinal');
            if (skorBadge) {
                skorBadge.textContent = validasi.skor_final || '-';
                skorBadge.className = `badge ${getSkorBadgeClass(validasi.skor_final)}`;

            }
        } else {
            document.querySelectorAll('.detailValidasiSkor').forEach(e => {
                e.classList.add('d-none');
            });
        }

        const detailValidasiValidator = document.getElementById('detailValidasiValidator');
        if (detailValidasiValidator) {
            detailValidasiValidator.textContent = validasi.validator ? validasi.validator.name : '-';
        }
        const detailValidasiTanggal = document.getElementById('detailValidasiTanggal');
        if (detailValidasiTanggal) {
            detailValidasiTanggal.textContent = formatDateTime(validasi.validated_at);
        }
        const detailValidasiCatatan = document.getElementById('detailValidasiCatatan');
        if (detailValidasiCatatan) {
            detailValidasiCatatan.innerHTML = validasi.catatan_validator || '<em class="text-muted">Tidak ada catatan</em>';
        }

        // Penilaian asesor (Dynamic for all asesors)
        const colors = ['#2196f3', '#ff9800', '#4caf50', '#9c27b0', '#00bcd4'];

        let htmlPenilaian = '<div class="row">';
        penilaianAsesor.forEach((pen, index) => {
            const color = colors[index % colors.length];

            htmlPenilaian += `
                <div class="col-md-12 col-lg-6 mb-3">
                    <div class="card asesor-penilaian-card asesor-${index + 1}" style="border-left-color: ${color};">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar-circle-detail me-2" style="background: ${color};">
                                    ${pen.asesor.name.substring(0, 2).toUpperCase()}
                                </div>
                                <div>
                                    <strong>${pen.asesor.name}</strong>
                                    <div><small class="text-muted">Asesor ${index + 1}</small></div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">Kategori:</small>
                                <div>
                                    <span class="badge ${getSkorBadgeClass(pen.skor)}">
                                        ${getSkorLabel(pen.skor)}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <small class="text-muted">Komentar:</small>
                                <p class="small mb-0 mt-1">${pen.komentar || '<em class="text-muted">Tidak ada komentar</em>'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        htmlPenilaian += '</div>';

        document.getElementById('detailPenilaianAsesor').innerHTML = htmlPenilaian;
    }

    function formatDateTime(datetime) {
        if (!datetime) return '-';
        const date = new Date(datetime);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit'
            , month: 'long'
            , year: 'numeric'
            , hour: '2-digit'
            , minute: '2-digit'
        });
    }

</script>
@endpush
