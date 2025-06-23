<label class="form-label mb-2 fw-medium text-dark"
    for="{{ $for }}">
    {{ $slot }}
    @if ($required)
        <span class="text-danger">*</span>
    @endif
</label>
