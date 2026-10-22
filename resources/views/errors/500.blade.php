<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>500 — Terjadi Gangguan | Banjar Custome</title>
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
            <p class="text-7xl font-bold text-primary">500</p>
            <h1 class="mt-4 text-2xl font-bold">Terjadi Gangguan</h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Server kami sedang bermasalah. Silakan coba lagi beberapa saat,
                atau hubungi kami langsung via WhatsApp.
            </p>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <x-button href="{{ route('home') }}" size="lg">
                    Muat Ulang Beranda
                </x-button>
                <x-button href="{{ config('banjarcustom.whatsapp_link') }}" size="lg" variant="outline" target="_blank" rel="noopener noreferrer">
                    Hubungi Kami
                </x-button>
            </div>
        </main>
    </body>
</html>
