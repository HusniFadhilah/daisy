<div class="card stat-card p-0">
    <div class="card-body text-dark">
        <h6 class="mb-2 text-muted">{{ $title }}</h6>
        <div class="d-flex justify-content-between align-items-center">
            <div class="flex-grow-1 pe-3">
                <h2 class="mb-0 fw-bold">{{ $value }}</h2>
                <small class="text-muted">{{ $description }}</small>
            </div>
            <div class="icon-box bg-light rounded-3 p-3 flex-shrink-0">
                <i class="bi bi-{{ $icon }} fs-4"></i>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 60px;
        min-height: 60px;
    }

</style>
