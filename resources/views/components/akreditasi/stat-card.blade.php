@php
$hasGradient = $type === 'gradient' && $gradient;
$cardClass = $hasGradient ? ($gradientMode === 'accent' ? 'stat-card stat-card-accent' : 'stat-card stat-card-full') : 'stat-card';
@endphp

<div class="card {{ $cardClass }} p-0" @if($hasGradient) style="--gradient: {{ $gradient }}; @if($gradientMode === 'full') background: {{ $gradient }}; @endif" @endif>
    <div class="card-body @if($hasGradient && $gradientMode === 'full') text-white @else text-dark @endif">
        <h6 class="mb-2 @if($hasGradient && $gradientMode === 'full') opacity-75 @else text-muted @endif">
            {{ $title }}
        </h6>
        <div class="d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 pe-3">
                <h2 class="mb-0 fw-bold">{{ $value }}</h2>
                <small class="@if($hasGradient && $gradientMode === 'full') opacity-75 @else text-muted @endif">
                    {{ $description }}
                </small>
            </div>

            @if($hasGradient && $gradientMode === 'full')
            {{-- Icon untuk full gradient mode --}}
            <div class="stat-icon-gradient">
                <i class="bi bi-{{ $icon }}"></i>
            </div>
            @else
            {{-- Icon untuk white/accent mode --}}
            <div class="icon-box bg-light rounded-3 p-3 flex-shrink-0">
                <i class="bi bi-{{ $icon }} fs-4"></i>
            </div>
            {{-- <div class="icon-box bg-{{ $iconBg }} rounded-3 p-3 flex-shrink-0">
            <i class="bi bi-{{ $icon }} fs-4"></i>
        </div> --}}
        @endif
    </div>
</div>
</div>

<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Icon box untuk white/accent mode */
    .icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 60px;
        min-height: 60px;
    }

    /* Accent mode: gradient hanya di border kiri */
    .stat-card-accent::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--gradient);
        transition: width 0.3s ease;
    }

    .stat-card-accent:hover::before {
        width: 6px;
    }

    /* Full mode: Icon box dengan gradient background */
    .stat-icon-gradient {
        background: rgba(255, 255, 255, 0.2);
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

</style>
