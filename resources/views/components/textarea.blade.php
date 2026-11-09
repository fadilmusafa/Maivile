@props(['name', 'label', 'value' => null, 'rows' => 3, 'required' => false, 'hint' => null])
<div class="mb-3">
    <label for="f_{{ $name }}" class="form-label">{{ $label }}@if ($required) <span class="text-secondary" aria-hidden="true">*</span>@endif</label>
    <textarea id="f_{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              @class(['form-control', 'is-invalid' => $errors->has($name)])
              @required($required)
              @if ($hint) aria-describedby="h_{{ $name }}" @endif>{{ $value }}</textarea>
    @error($name)
        <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
    @enderror
    @if ($hint)
        <div id="h_{{ $name }}" class="form-text text-secondary">{{ $hint }}</div>
    @endif
</div>
