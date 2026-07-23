@props([
    'id',
    'title',
    'button' => 'Open',
    'buttonColor' => 'primary',
    'submit' => 'Save',
    'submitColor' => 'primary',
    'close' => 'Close',
    'action' => null,
    'method' => 'POST',
    'size' => null,
    'centered' => true,
    'scrollable' => false,
    'staticBackdrop' => false,
    'showTrigger' => true,
    'formId' => null,
    'formAttributes' => [],
])

@php
    $allowedColors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark', 'light'];
    $buttonColor = in_array($buttonColor, $allowedColors, true) ? $buttonColor : 'primary';
    $submitColor = in_array($submitColor, $allowedColors, true) ? $submitColor : 'primary';

    $sizeClasses = [
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        'fullscreen' => 'modal-fullscreen',
    ];

    $dialogClass = $sizeClasses[$size] ?? null;
    $formId ??= $id.'-form';
    $titleId = $id.'-title';
    $httpMethod = strtoupper($method);
    $nativeMethod = $httpMethod === 'GET' ? 'GET' : 'POST';
    $formBag = new \Illuminate\View\ComponentAttributeBag($formAttributes);
@endphp

@if ($showTrigger)
    <button
        {{ $attributes->class(['btn', 'btn-'.$buttonColor])->merge([
            'type' => 'button',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#'.$id,
            'aria-controls' => $id,
        ]) }}
    >
        {{ $button }}
    </button>
@endif

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $titleId }}"
    aria-hidden="true"
    @if ($staticBackdrop) data-bs-backdrop="static" data-bs-keyboard="false" @endif
>
    <div @class([
        'modal-dialog',
        $dialogClass,
        'modal-dialog-centered' => $centered,
        'modal-dialog-scrollable' => $scrollable,
    ]) role="document">
        <form
            {{ $formBag->class('modal-content') }}
            id="{{ $formId }}"
            action="{{ $action ?: url()->current() }}"
            method="{{ $nativeMethod }}"
        >
            @if ($nativeMethod === 'POST')
                @csrf
            @endif

            @if (! in_array($httpMethod, ['GET', 'POST'], true))
                @method($httpMethod)
            @endif

            <div class="modal-header">
                <h5 class="modal-title" id="{{ $titleId }}">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ $close }}"></button>
            </div>

            <div class="modal-body">
                {{ $slot }}
            </div>

            <div class="modal-footer">
                @isset($footer)
                    {{ $footer }}
                @else
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ $close }}
                    </button>
                    <button type="submit" class="btn btn-{{ $submitColor }}">
                        {{ $submit }}
                    </button>
                @endisset
            </div>
        </form>
    </div>
</div>
