<div class="{{ $col ?? 'col-md-6' }}">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"{{ !empty($required) ? ' required' : '' }}>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
