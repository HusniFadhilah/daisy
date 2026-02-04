{{-- resources/views/components/stat-card-gradient.blade.php --}}

@props([
'title',
'value',
'description',
'icon' => 'graph-up',
'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
])

<div class="card stat-card p-0" style="background: {{ $gradient }};">
    <div class="card-body text-white">
        <h6 class="mb-2 opacity-75">{{ $title }}</h6>
        <div class="d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 pe-3">
                <h2 class="mb-0 fw-bold">{{ $value }}</h2>
                <small class="opacity-75">{{ $description }}</small>
            </div>
            <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                <i class="bi bi-{{ $icon }} fs-4"></i>
            </div>
        </div>
    </div>
</div>
