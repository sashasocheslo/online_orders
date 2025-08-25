<div class="position-relative">
    @if ($formId)
        <button type="button"
            class="btn position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent d-flex align-items-center justify-content-center rounded-circle"
            onclick="document.getElementById('{{ $name }}').value = ''; document.getElementById('{{ $formId }}').submit();"
        >
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 1.5rem; height: 1.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.5" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </span>
        </button>
    @endif

    @if ($textarea)
        <textarea name="{{ $name }}" id="{{ $name }}" placeholder="{{ $placeholder }}" x-ref="input-{{ $name }}"
            @class([
                'form-control',
                'form-control-lg rounded-3' => in_array($name, ['email', 'password', 'name', 'password_confirmation']) === false,
                'border-0 shadow-sm ps-4 py-2 pe-5' => true,
                'border border-danger' => $errors->has($name),
                'is-invalid' => $errors->has($name),
                'pe-4' => $formId,
            ])
        >{{ old($name, $value) }}</textarea>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" x-ref="input-{{ $name }}"
            @class([
                'form-control',
                'form-control-lg rounded-3' => in_array($name, ['email', 'password', 'name', 'password_confirmation']),
                'border-0 shadow-sm ps-4 py-2 pe-5' => !in_array($name, ['email', 'password', 'name', 'password_confirmation']),
                'border border-danger' => $errors->has($name),
                'is-invalid' => $errors->has($name),
                'pe-4' => $formId,
            ])
        />
    @endif

    @error($name)
        <div class="invalid-feedback d-block small mt-1">
            {{ $message }}
        </div>
    @enderror
</div>
