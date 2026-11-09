@props(['name', 'label', 'multiple' => false, 'required' => false, 'hint' => null])
@php
    $hasError = $errors->has($name) || $errors->has($name.'.*');
    $message = $errors->first($name) ?: $errors->first($name.'.*');
@endphp
<div class="mb-3">
    <label for="f_{{ $name }}" class="form-label">{{ $label }}@if ($required) <span class="text-secondary" aria-hidden="true">*</span>@endif</label>
    <input id="f_{{ $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" type="file" @if ($multiple) multiple @endif
           accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
           @class(['form-control', 'is-invalid' => $hasError]) @required($required)>
    @if ($hasError)
        <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
    @endif
    <div class="form-text text-secondary">{{ $hint ?? 'PDF, JPG, atau PNG. Maksimal 2 MB per berkas.' }}</div>
</div>
