<div class="col-md-6">
    <label for="is_active" class="form-label">Status</label>
    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
        <option value="1" {{ old('is_active', $value ?? true) == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('is_active', $value ?? true) == '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
