@php
$reviewData = $validation->{"review_{$category}"} ?? [];
$currentReview = $reviewData[$itemId] ?? null;
$currentGrade = $currentReview['grade'] ?? null;
$currentCatatan = $currentReview['catatan'] ?? '';

// LKPS: elemenId dikirim dari parent biar bisa update counter per elemen
$elemenId = $elemenId ?? null;
@endphp

<div class="review-item" data-category="{{ $category }}" data-item-id="{{ $itemId }}" @if($elemenId) data-elemen-id="{{ $elemenId }}" @endif>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success grade-btn {{ $currentGrade === 'A' ? 'active' : '' }}" data-grade="A">A - Sudah tepat</button>
            <button type="button" class="btn btn-outline-warning grade-btn {{ $currentGrade === 'B' ? 'active' : '' }}" data-grade="B">B - Kurang lengkap</button>
            <button type="button" class="btn btn-outline-danger grade-btn {{ $currentGrade === 'C' ? 'active' : '' }}" data-grade="C">C - Perlu diperbaiki</button>
        </div>

        <button type="button" class="btn btn-primary btn-sm btn-save-review">
            <i class="bi bi-save"></i> Simpan
        </button>
    </div>

    <textarea class="form-control form-control-sm catatan-input" rows="4" placeholder="Berikan catatan review...">{{ $currentCatatan }}</textarea>

    <div class="mt-2">
        <small class="text-muted status-text">
            @if($currentReview)
            <i class="bi bi-check-circle text-success"></i>
            Tersimpan - {{ \Carbon\Carbon::parse($currentReview['reviewed_at'])->diffForHumans() }}
            @else
            <i class="bi bi-circle text-secondary"></i> Belum direview
            @endif
        </small>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // klik grade => hanya update UI (belum save)
        document.querySelectorAll('.review-item .grade-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const wrap = this.closest('.review-item');
                wrap.querySelectorAll('.grade-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const status = wrap.querySelector('.status-text');
                status.innerHTML = '<i class="bi bi-pencil"></i> Belum disimpan';
            });
        });

        // tombol simpan
        document.querySelectorAll('.review-item .btn-save-review').forEach(btn => {
            btn.addEventListener('click', async function() {
                const wrap = this.closest('.review-item');
                const category = wrap.dataset.category;
                const itemId = parseInt(wrap.dataset.itemId, 10);
                const elemenId = wrap.dataset.elemenId ? parseInt(wrap.dataset.elemenId, 10) : null;

                const active = wrap.querySelector('.grade-btn.active');
                const grade = active ? active.dataset.grade : null;
                const catatan = wrap.querySelector('.catatan-input').value;

                if (!grade) {
                    alert('Pilih grade terlebih dahulu.');
                    return;
                }

                const status = wrap.querySelector('.status-text');
                const originalBtn = this.innerHTML;

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
                status.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

                try {
                    const url = @json(route("validator.borang.update-review", $assignmentId));
                    const res = await fetch(url, {
                        method: 'POST'
                        , headers: {
                            'Content-Type': 'application/json'
                            , 'Accept': 'application/json'
                            , 'X-CSRF-TOKEN': @json(csrf_token())
                        }
                        , body: JSON.stringify({
                            category: category
                            , item_id: itemId
                            , grade: grade
                            , catatan: catatan
                        })
                    });

                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        status.innerHTML = '<i class="bi bi-x-circle text-danger"></i> ' + (data.message || 'Gagal menyimpan');
                        return;
                    }

                    status.innerHTML = '<i class="bi bi-check-circle text-success"></i> Tersimpan';

                    // ===== update status elemen (badge lengkap/belum lengkap) =====
                    // LED/Suplemen: 1 item per elemen
                    if (category === 'led' || category === 'suplemen') {
                        if (typeof window.__updateElemenStatus === 'function') {
                            window.__updateElemenStatus({
                                tab: category
                                , elemenId: itemId
                                , reviewedCount: 1
                                , requiredCount: 1
                            });
                        }
                    }

                    // LKPS: perlu hitung ulang reviewed indikator per elemen (client-side sederhana)
                    if (category === 'lkps' && elemenId) {
                        const allInElemen = document.querySelectorAll(`.review-item[data-category="lkps"][data-elemen-id="${elemenId}"]`);
                        let requiredCount = allInElemen.length;
                        let reviewedCount = 0;

                        allInElemen.forEach(x => {
                            const activeGrade = x.querySelector('.grade-btn.active');
                            // dianggap reviewed kalau punya active grade dan status tersimpan minimal sekali.
                            // lebih akurat: cek status icon check-circle, tapi ini cukup untuk UI.
                            if (activeGrade) {
                                const statusText = x.querySelector('.status-text')
                                const st = statusText ? statusText.innerText : '';
                                if (st.includes('Tersimpan')) reviewedCount++;
                            }
                        });

                        // kalau baru disimpan item ini, pastikan minimal nambah 1
                        reviewedCount = Math.min(requiredCount, Math.max(reviewedCount, 1));

                        if (typeof window.__updateElemenStatus === 'function') {
                            window.__updateElemenStatus({
                                tab: 'lkps'
                                , elemenId
                                , reviewedCount
                                , requiredCount
                            });
                        }
                    }

                    // Optional: refresh statistik progress dengan endpoint stats jika mau real akurat
                    // (biar progress cards atas update tanpa reload)
                    await refreshStats();

                } catch (err) {
                    console.error(err);
                    status.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Error';
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalBtn;
                }
            });
        });

        // (opsional) refresh stats progress cards atas
        async function refreshStats() {
            // kalau kamu punya route stats: validator.borang.stats / getValidationStats
            // silakan diaktifkan. Aku tidak aktifkan default biar tidak asumsi nama route.
        }
    });

</script>
@endpush
@endonce
