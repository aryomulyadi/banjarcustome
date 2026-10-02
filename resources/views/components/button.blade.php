@props(['variant' => 'default', 'size' => 'default'])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground hover:bg-primary/90',
        'secondary' => 'bg-secondary text-secondary-foreground border border-border hover:bg-secondary/70',
        'outline' => 'border border-border bg-transparent hover:bg-secondary',
        'ghost' => 'hover:bg-secondary',
        'link' => 'text-primary underline-offset-4 hover:underline',
    ];

    $sizes = [
        'default' => 'h-10 px-4 py-2 text-sm',
        'sm' => 'h-9 px-3 text-sm',
        'lg' => 'h-11 px-8 text-base',
        'icon' => 'h-10 w-10',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 '
        . ($variants[$variant] ?? $variants['default'])
        . ' '
        . ($sizes[$size] ?? $sizes['default']);
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
