@props([])

<div {{ $attributes->merge(['class' => 'w-[78vw] max-w-[380px] shrink-0 snap-start sm:w-auto sm:max-w-none']) }}>
    {{ $slot }}
</div>
