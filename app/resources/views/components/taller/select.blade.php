@props(['label', 'icon' => null, 'name', 'required' => false, 'options' => [], 'selected' => null])

<div class="form-group mb-0">
    <label for="{{ $name }}" class="text-muted small fw-bold mb-2 d-flex align-items-center">
        @if($icon)
            <i class="{{ $icon }} me-2 opacity-50"></i>
        @endif
        {{ strtoupper($label) }} {!! $required ? '<span class="text-danger ms-1">*</span>' : '' !!}
    </label>
    <select name="{{ $name }}" 
            id="{{ $name }}" 
            class="form-select border-2 shadow-none rounded-3 bg-white py-2 border-light-2 @error($name) border-danger @enderror" 
            style="height: 58px;"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}>
        <option value="">Seleccione...</option>
        {{ $slot }}
    </select>
    @error($name)
        <div class="text-danger small fw-bold mt-1">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ $message }}
        </div>
    @enderror
</div>
