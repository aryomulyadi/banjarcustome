@props(['src', 'alt', 'width' => null, 'height' => null])

@php
    $webp = \App\Support\ImageOptimizer::hasWebp($src)
        ? asset('storage/' . \App\Support\ImageOptimizer::webpPath($src))
        : null;
@endphp

<picture>
    @if ($webp)
        <source srcset="{{ $webp }}" type="image/webp">
    @endif
    <img
        src="{{ asset('storage/' . $src) }}"
        alt="{{ $alt }}"
        @if ($width) width="{{ $width }}" @endif
        @if ($height) height="{{ $height }}" @endif
        {{ $attributes }}
    >
</picture>
