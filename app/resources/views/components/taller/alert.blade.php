@props(['type' => 'success', 'title' => null])

@php
    $icon = match($type) {
        'success' => 'fas fa-check',
        'danger' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
        default => 'fas fa-bell',
    };
    $defaultTitle = match($type) {
        'success' => '¡Éxito!',
        'danger' => 'Error',
        'warning' => 'Atención',
        'info' => 'Información',
        default => 'Notificación',
    };
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$type} border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 fade show"]) }} role="alert">
    <div class="icon-shape icon-sm bg-{{ $type }}-light text-{{ $type }} rounded-circle me-3">
        <i class="{{ $icon }}"></i>
    </div>
    <div>
        <h6 class="mb-0 fw-bold">{{ $title ?? $defaultTitle }}</h6>
        <small>{{ $slot }}</small>
    </div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
