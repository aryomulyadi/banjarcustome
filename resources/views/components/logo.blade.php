@props(['size' => 'sm'])

@php
    $imgSizes = [
        'sm' => 'h-9 max-w-[150px] w-auto',
        'lg' => 'h-12 max-w-[200px] w-auto',
    ];

    $boxSizes = [
        'sm' => 'h-9 w-9 text-sm',
        'lg' => 'h-12 w-12 text-base',
    ];

    $hasLogo = file_exists(public_path('images/logo.svg'));
@endphp

@if ($hasLogo)
    <img
        src="{{ asset('images/logo.svg') }}"
        alt="Banjar Custome"
        {{ $attributes->merge(['class' => 'block shrink-0 object-contain '.($imgSizes[$size] ?? $imgSizes['sm'])]) }}
    >
@else
    <span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-lg bg-primary font-bold text-primary-foreground '.($boxSizes[$size] ?? $boxSizes['sm'])]) }}>
        BC
    </span>
@endif
