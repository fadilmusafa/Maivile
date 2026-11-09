@props(['name', 'label', 'required' => false, 'hint' => null, 'disabled' => false])
<div class="mb-3">
    <label for="f_{{ $name }}" class="form-label">{{ $label }}@if ($required) <span class="text-secondary" aria-hidden="true">*</span>@endif</label>
    <select id="f_{{ $name }}" name="{{ $name }}"
            @class(['form-select', 'is-invalid' => $errors->has($name)])
            @required($required) @disabled($disabled)
            @if ($hint) aria-describedby="h_{{ $name }}" @endif>
        {{ $slot }}
    </select>
    @error($name)
        <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
    @enderror
    @if ($hint)
        <div id="h_{{ $name }}" class="form-text text-secondary">{{ $hint }}</div>
    @endif
</div>
