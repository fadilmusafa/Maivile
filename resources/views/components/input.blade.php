@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'disabled' => false, 'autocomplete' => null, 'placeholder' => null])
<div class="mb-3">
    <label for="f_{{ $name }}" class="form-label">{{ $label }}@if ($required) <span class="text-secondary" aria-hidden="true">*</span>@endif</label>
    <input id="f_{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : $value }}"
           @class(['form-control', 'is-invalid' => $errors->has($name)])
           @required($required) @disabled($disabled)
           @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           @if ($placeholder) placeholder="{{ $placeholder }}" @endif
           @if ($hint) aria-describedby="h_{{ $name }}" @endif>
    @error($name)
        <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
    @enderror
    @if ($hint)
        <div id="h_{{ $name }}" class="form-text text-secondary">{{ $hint }}</div>
    @endif
</div>
