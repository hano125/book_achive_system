@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'prepend' => null,
    'append' => null,
    'icon' => null,
    'help' => null,
    'wrapperClass' => 'mb-3',
])

@php
    $id ??= $name;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $hasError = $name && $errorBag->has($name);
    $errorId = $id ? $id.'-error' : null;
    $helpId = $id ? $id.'-help' : null;
    $descriptionId = $hasError ? $errorId : ($help ? $helpId : null);
    $hasCustomInput = trim((string) $slot) !== '';
@endphp

<div class="{{ $wrapperClass }}">
    @if ($label)
        <label @if ($id) for="{{ $id }}" @endif class="form-label">{{ $label }}</label>
    @endif

    <div @class(['input-group', 'has-validation' => $hasError])>
        @if ($prepend !== null || $icon)
            <span class="input-group-text">
                @if ($icon)
                    <i class="{{ $icon }}"></i>
                @else
                    {{ $prepend }}
                @endif
            </span>
        @endif

        @if ($hasCustomInput)
            {{ $slot }}
        @else
            <input
                {{ $attributes->class(['form-control', 'is-invalid' => $hasError])->merge([
                    'type' => $type,
                    'name' => $name,
                    'id' => $id,
                    'placeholder' => $placeholder,
                    'aria-describedby' => $descriptionId,
                ]) }}
                @if ($type !== 'file') value="{{ $name ? old($name, $value) : $value }}" @endif
            >
        @endif

        @if ($append !== null)
            <span class="input-group-text">{{ $append }}</span>
        @endif

        @if ($hasError)
            <div class="invalid-feedback" id="{{ $errorId }}">{{ $errorBag->first($name) }}</div>
        @endif
    </div>

    @if ($help && ! $hasError)
        <div class="form-text" @if ($helpId) id="{{ $helpId }}" @endif>{{ $help }}</div>
    @endif
</div>
