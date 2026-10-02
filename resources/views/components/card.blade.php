@props(['title' => null])

@php
    $hasHeader = isset($header) || filled($title);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card text-card-foreground shadow-sm']) }}>
    @if ($hasHeader)
        <div class="flex flex-col space-y-1.5 p-6">
            @if (isset($header))
                {{ $header }}
            @else
                <h3 class="text-lg font-semibold leading-none tracking-tight">{{ $title }}</h3>
            @endif
        </div>
    @endif
    <div class="p-6 {{ $hasHeader ? 'pt-0' : '' }}">
        {{ $slot }}
    </div>
</div>
