@php $enErreur = $errors->has($name); @endphp

<div class="mb-4">

    @if ($label !== '')
        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    @endif

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="{{ $placeholder }}"
            @class(['field-textarea', 'border-danger-fg' => $enErreur])>{{ $value }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
            placeholder="{{ $placeholder }}" @class(['field-input', 'border-danger-fg' => $enErreur])>
    @endif

    @if ($hint !== '' && !$enErreur)
        <p class="mt-1.5 text-xs text-faint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror

</div>
