@props([])

<select
    {{ $attributes->merge(['class' => 'flex h-10 w-full rounded-md border border-input bg-transparent pl-3 pr-8 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50']) }}
>{{ $slot }}</select>
