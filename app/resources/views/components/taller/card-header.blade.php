@props(['icon' => 'fas fa-info-circle', 'title', 'subtitle' => null, 'badge' => null, 'badgeColor' => 'primary'])

<div {{ $attributes->merge(['class' => 'card-header bg-white py-3 border-bottom d-flex align-items-center']) }}>
    <div class="icon-box bg-primary text-white rounded-3 me-3 p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="{{ $icon }} fs-5"></i>
    </div>
    <div>
        <h5 class="fw-bold mb-0 text-dark">{{ $title }}</h5>
        @if($subtitle)
            <p class="text-muted small mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if($badge)
        <span class="badge bg-{{ $badgeColor }} rounded-pill ms-auto px-3 py-2 fw-bold">{{ $badge }}</span>
    @endif
</div>
