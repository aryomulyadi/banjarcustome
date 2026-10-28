@props(['grid' => ''])

<div {{ $attributes->merge(['class' => 'flex -mx-4 snap-x snap-mandatory gap-4 overflow-x-auto px-4 sm:mx-0 sm:grid sm:overflow-x-visible sm:px-0 '.$grid]) }}>
    {{ $slot }}
</div>
