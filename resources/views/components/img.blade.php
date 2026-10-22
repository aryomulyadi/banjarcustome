@props(['src', 'alt', 'width' => null, 'height' => null, 'sizes' => null])

@php
    $webp = \App\Support\ImageOptimizer::hasWebp($src)
        ? asset('storage/' . \App\Support\ImageOptimizer::webpPath($src))
        : null;
    $variants = \App\Support\ImageOptimizer::responsiveVariants($src);
    $srcset = '';
    if ($webp && $variants !== []) {
        $entries = [];
        foreach ($variants as $variantWidth => $path) {
            $entries[] = asset('storage/' . $path) . ' ' . $variantWidth . 'w';
        }
        // Varian utama diklaim selebar batas MAX_DIMENSION (1600) — semua file ≤ itu.
        $entries[] = $webp . ' 1600w';
        $srcset = implode(', ', $entries);
    }
@endphp

<picture>
    @if ($webp)
        @if ($srcset !== '')
            <source srcset="{{ $srcset }}" @if ($sizes) sizes="{{ $sizes }}" @endif type="image/webp">
        @else
            <source srcset="{{ $webp }}" type="image/webp">
        @endif
    @endif
    <img
        src="{{ asset('storage/' . $src) }}"
        alt="{{ $alt }}"
        @if ($width) width="{{ $width }}" @endif
        @if ($height) height="{{ $height }}" @endif
        {{ $attributes }}
    >
</picture>
