<div class="mb-4">

    @if ($label !== '')
        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    @endif

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="3"
            @class(['field-input', 'border-rouge-700' => $errors->has($name)])>{{ $value }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
            @class(['field-input', 'border-rouge-700' => $errors->has($name)])>
    @endif

    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror

</div>
