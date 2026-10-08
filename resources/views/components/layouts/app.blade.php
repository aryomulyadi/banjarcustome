@php
    $pageTitle = isset($title) && $title !== '' ? $title : config('app.name');
    $pageDescription = isset($description) && $description !== ''
        ? $description
        : 'Banjar Custome — jasa konveksi dan sablon custom di Banjarmasin. Kaos, jersey printing, kemeja, jaket, seragam, dan merchandise.';
    $canonical = $canonical ?? url()->current();
    $ogImage = $ogImage ?? asset('images/banner-1.jpg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $pageDescription }}">

        <title>{{ $pageTitle }}</title>

        <link rel="canonical" href="{{ $canonical }}">

        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="{{ $ogImageWidth ?? 1600 }}">
        <meta property="og:image:height" content="{{ $ogImageHeight ?? 700 }}">
        <meta property="og:image:alt" content="{{ $pageTitle }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        @if (! empty($noindex))
            <meta name="robots" content="noindex, nofollow">
        @endif

        @include('partials.favicon')

        <link rel="preload" href="{{ asset('fonts/instrument-sans-400-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ asset('fonts/instrument-sans-500-latin.woff2') }}" as="font" type="font/woff2" crossorigin>

        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            (function () {
                var stored = localStorage.getItem('theme');
                if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            $sitewideJsonLd = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'WebSite',
                        'url' => url('/'),
                        'name' => config('app.name'),
                        'inLanguage' => 'id-ID',
                        'potentialAction' => [
                            '@type' => 'SearchAction',
                            'target' => [
                                '@type' => 'EntryPoint',
                                'urlTemplate' => route('produk.index').'?q={search_term_string}',
                            ],
                            'query-input' => 'required name=search_term_string',
                        ],
                    ],
                    [
                        '@type' => 'Organization',
                        'name' => 'Banjar Custome',
                        'url' => url('/'),
                        'logo' => asset('images/logo.svg'),
                        'sameAs' => [config('banjarcustom.instagram')],
                        'address' => [
                            '@type' => 'PostalAddress',
                            'addressLocality' => 'Banjarmasin',
                            'addressRegion' => 'Kalimantan Selatan',
                            'addressCountry' => 'ID',
                        ],
                    ],
                ],
            ];
        @endphp
        <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode($sitewideJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </head>
    <body class="flex min-h-screen flex-col bg-background text-foreground">
        <a href="#konten-utama" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-primary-foreground">
            Lewati ke konten utama
        </a>

        @include('partials.header')

        <main id="konten-utama" class="flex-1" tabindex="-1">
            {{ $slot }}
        </main>

        @include('partials.footer')
        @include('partials.whatsapp-fab')
    </body>
</html>
