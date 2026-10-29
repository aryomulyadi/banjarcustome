@props(['status', 'label' => null])

@php
    $classes = \App\Support\StatusTheme::classes(is_string($status) ? $status : null);
    $text = is_string($label) && $label !== '' ? $label : $status;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold '.$classes]) }}>{{ $text }}</span>
