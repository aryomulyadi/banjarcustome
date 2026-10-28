@props([])

<textarea
    {{ $attributes->merge(['class' => 'flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm transition-colors sm:text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50']) }}
>{{ $slot }}</textarea>
