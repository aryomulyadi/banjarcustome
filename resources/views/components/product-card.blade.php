@props(['product', 'highlightColor' => null])

@php
    $swatches = $product->colors->take(6);

    if ($highlightColor) {
        $matched = $product->colors->firstWhere('name', $highlightColor);

        if ($matched) {
            $swatches = collect([$matched])
                ->concat($product->colors->where('name', '!=', $highlightColor)->take(5));
        }
    }
@endphp

<div class="relative">
<a
    href="{{ route('produk.show', $product->slug) }}"
    class="group flex flex-col overflow-hidden rounded-xl border border-border bg-card transition-all hover:-translate-y-0.5 hover:shadow-lg"
>
    <div class="relative overflow-hidden">
        @if ($product->image)
            <x-img
                :src="$product->image"
                :alt="$product->title"
                width="800"
                height="600"
                loading="lazy"
                decoding="async"
                sizes="(min-width: 1024px) 400px, (min-width: 640px) calc(50vw - 32px), calc(100vw - 32px)"
                class="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
        @else
            <x-placeholder-image :label="$product->category?->name ?? 'Produk'" class="transition-transform duration-300 group-hover:scale-105" />
        @endif

        @if ($product->category)
            <span class="absolute left-3 top-3 rounded-full bg-background/90 px-2.5 py-1 text-xs font-medium text-foreground backdrop-blur">
                {{ $product->category->name }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-2 p-4">
        <h3 class="font-semibold leading-snug transition-colors group-hover:text-primary">
            {{ $product->title }}
        </h3>
        <p class="line-clamp-2 text-sm text-muted-foreground">
            {{ $product->description }}
        </p>
        <div class="mt-auto flex items-center justify-between gap-2 pt-2">
            <span class="text-sm font-semibold text-primary">
                {{ $product->price_estimate ?? 'Hubungi CS' }}
            </span>
            @if ($product->colors->isNotEmpty())
                <div class="flex -space-x-1.5">
                    @foreach ($swatches as $color)
                        <span
                            class="h-4 w-4 rounded-full border-2 border-card {{ $highlightColor && $color->name === $highlightColor ? 'ring-2 ring-ring ring-offset-1 ring-offset-card' : '' }}"
                            style="background-color: {{ $color->hex }}"
                            title="{{ $color->name }}"
                        ></span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</a>
<a
    href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custom, saya mau tanya produk: '.$product->title) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="absolute right-3 top-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-background/90 text-foreground shadow-sm backdrop-blur transition-colors hover:bg-primary hover:text-primary-foreground"
    aria-label="Tanya {{ $product->title }} via WhatsApp"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
</a>
</div>
