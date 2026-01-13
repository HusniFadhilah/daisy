{{-- resources/views/validator/borang/partials/review-item.blade.php --}}
@php
$reviewData = $validation->{"review_{$category}"} ?? [];
$currentReview = $reviewData[$itemId] ?? null;
$currentGrade = $currentReview['grade'] ?? null;
$currentCatatan = $currentReview['catatan'] ?? '';
@endphp

<div class="review-item" data-category="{{ $category }}" data-item-id="{{ $itemId }}">
    <div class="btn-group mb-2" role="group">
        <button type="button" class="btn btn-outline-danger grade-btn {{ $currentGrade === 'A' ? 'active' : '' }}" data-grade="A">
            A - Perlu diperbaiki
        </button>
        <button type="button" class="btn btn-outline-warning grade-btn {{ $currentGrade === 'B' ? 'active' : '' }}" data-grade="B">
            B - Kurang lengkap
        </button>
        <button type="button" class="btn btn-outline-success grade-btn {{ $currentGrade === 'C' ? 'active' : '' }}" data-grade="C">
            C - Sudah tepat
        </button>
    </div>

    <textarea class="form-control form-control-sm catatan-input" rows="2" placeholder="Catatan (opsional)...">{{ $currentCatatan }}</textarea>

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
        // Handle grade button click
        document.querySelectorAll('.grade-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewItem = this.closest('.review-item');
                const category = reviewItem.dataset.category;
                const itemId = reviewItem.dataset.itemId;
                const grade = this.dataset.grade;
                const catatan = reviewItem.querySelector('.catatan-input').value;

                // Update UI
                reviewItem.querySelectorAll('.grade-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Save review
                saveReview(category, itemId, grade, catatan, reviewItem);
            });
        });

        // Handle catatan blur
        document.querySelectorAll('.catatan-input').forEach(input => {
            input.addEventListener('blur', function() {
                const reviewItem = this.closest('.review-item');
                const activeGrade = reviewItem.querySelector('.grade-btn.active');

                if (activeGrade) {
                    const category = reviewItem.dataset.category;
                    const itemId = reviewItem.dataset.itemId;
                    const grade = activeGrade.dataset.grade;
                    const catatan = this.value;

                    saveReview(category, itemId, grade, catatan, reviewItem);
                }
            });
        });
    });

    function saveReview(category, itemId, grade, catatan, reviewItem) {
        const statusText = reviewItem.querySelector('.status-text');
        statusText.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

        fetch('{{ route("validator.borang.update-review", $assignment->id) }}', {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
                , body: JSON.stringify({
                    category: category
                    , item_id: itemId
                    , grade: grade
                    , catatan: catatan
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusText.innerHTML = '<i class="bi bi-check-circle text-success"></i> Tersimpan';

                    // Update progress
                    updateProgress(data.progress);
                } else {
                    statusText.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Gagal menyimpan';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusText.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Error';
            });
    }

    function updateProgress(progress) {
        // Update progress bars and counters
        // (implementation depends on your UI)
        location.reload(); // Temporary - reload to show updated progress
    }

</script>
@endpush
@endonce
