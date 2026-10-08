@props(['items'])

<script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => collect($items)->map(fn (array $item, int $index) => [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $item['name'],
        'item' => $item['url'],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
