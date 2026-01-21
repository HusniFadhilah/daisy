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
            <button type="button" class="btn btn-outline-success grade-btn js-grade-btn {{ $currentGrade === 'A' ? 'active' : '' }}" data-grade="A">A - Sudah tepat</button>
            <button type="button" class="btn btn-outline-warning grade-btn js-grade-btn {{ $currentGrade === 'B' ? 'active' : '' }}" data-grade="B">B - Kurang lengkap, perlu melengkapi</button>
            <button type="button" class="btn btn-outline-danger grade-btn js-grade-btn {{ $currentGrade === 'C' ? 'active' : '' }}" data-grade="C">C - Perlu diperbaiki</button>
        </div>

        <button type="button" class="btn btn-primary btn-sm btn-save-review">
            <i class="bi bi-save"></i> Simpan
        </button>
    </div>

    <textarea class="form-control form-control-sm catatan-input js-review-note" rows="4" placeholder="Berikan catatan validasi...">{{ $currentCatatan }}</textarea>

    <div class="mt-2">
        <small class="text-muted status-text">
            @if($currentReview)
            <i class="bi bi-check-circle text-success"></i>
            Tersimpan - {{ \Carbon\Carbon::parse($currentReview['reviewed_at'])->diffForHumans() }}
            @else
            <i class="bi bi-circle text-secondary"></i> Belum direview/divalidasi
            @endif
        </small>
    </div>
</div>
