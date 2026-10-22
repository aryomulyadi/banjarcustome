<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>404 — Halaman Tidak Ditemukan | Banjar Custome</title>
        @include('partials.favicon')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            (function () {
                var stored = localStorage.getItem('theme');
                if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-background px-4 text-foreground">
        <main class="w-full max-w-md text-center">
            <p class="text-7xl font-bold text-primary">404</p>
            <h1 class="mt-4 text-2xl font-bold">Halaman Tidak Ditemukan</h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Halaman yang Anda cari tidak ada atau sudah dipindahkan.
                Silakan kembali ke beranda atau telusuri katalog kami.
            </p>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <x-button href="{{ route('home') }}" size="lg">
                    Kembali ke Beranda
                </x-button>
                <x-button href="{{ route('produk.index') }}" size="lg" variant="outline">
                    Lihat Katalog
                </x-button>
            </div>
        </main>
    </body>
</html>
