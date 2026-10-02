@props(['product'])

<a
    href="{{ route('produk.show', $product->slug) }}"
    class="group flex flex-col overflow-hidden rounded-xl border border-border bg-card transition-all hover:-translate-y-0.5 hover:shadow-lg"
>
    <div class="relative overflow-hidden">
        @if ($product->image)
            <img
                src="{{ asset('storage/' . $product->image) }}"
                alt="{{ $product->title }}"
                class="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105"
            >
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
                    @foreach ($product->colors->take(6) as $color)
                        <span
                            class="h-4 w-4 rounded-full border-2 border-card"
                            style="background-color: {{ $color->hex }}"
                            title="{{ $color->name }}"
                        ></span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</a>
