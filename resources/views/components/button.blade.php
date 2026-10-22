@props(['variant' => 'default', 'size' => 'default'])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground hover:bg-primary/90',
        'secondary' => 'bg-secondary text-secondary-foreground border border-border hover:bg-secondary/70',
        'outline' => 'border border-border bg-background/80 hover:bg-secondary',
        'ghost' => 'hover:bg-secondary',
        'link' => 'text-primary underline-offset-4 hover:underline',
        // Pita CTA berlatar primary (layanan/tentang/katalog-jersey): teks & latar dibalik.
        'invert' => 'bg-primary-foreground text-primary hover:bg-primary-foreground/90',
        'invert-outline' => 'border border-primary-foreground/40 text-primary-foreground hover:bg-primary-foreground/10',
    ];

    $sizes = [
        'sm' => 'h-9 px-4 text-sm',
        'default' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-6 text-sm',
        'xl' => 'h-12 px-8 text-sm',
        'icon' => 'h-10 w-10',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 '
        . ($variants[$variant] ?? $variants['default'])
        . ' '
        . ($sizes[$size] ?? $sizes['default']);
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
