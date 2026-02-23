{{--
    resources/views/de/penyampaian-hasil-akreditasi/components/syarat-row.blade.php

    Props:
      $memenuhi  bool
      $icon      string  (Bootstrap Icons name)
      $label     string
      $detail    string
--}}
<div class="d-flex align-items-start gap-3">
    <div class="flex-shrink-0 mt-1">
        @if($memenuhi)
        <span class="badge rounded-circle p-2 bg-success-subtle">
            <i class="bi bi-check-lg text-success" style="font-size:.9rem"></i>
        </span>
        @else
        <span class="badge rounded-circle p-2 bg-danger-subtle">
            <i class="bi bi-x-lg text-danger" style="font-size:.9rem"></i>
        </span>
        @endif
    </div>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-{{ $icon }} text-muted small"></i>
            <span class="fw-semibold small">{{ $label }}</span>
        </div>
        <p class="text-muted mb-0" style="font-size: .8rem">{{ $detail }}</p>
    </div>
</div>
