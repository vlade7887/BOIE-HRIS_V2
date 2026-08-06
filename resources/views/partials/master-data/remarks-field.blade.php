<div class="col-md-12">
    <label for="remarks" class="form-label">Remarks</label>
    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $value) }}</textarea>
    @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
