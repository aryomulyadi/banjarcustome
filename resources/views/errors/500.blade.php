<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>500 — Terjadi Gangguan | Banjar Custome</title>
        @include('partials.favicon')
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
                <a href="{{ route('home') }}" class="inline-flex h-11 items-center rounded-md bg-primary px-6 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                    Muat Ulang Beranda
                </a>
                <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-11 items-center rounded-md border border-border px-6 text-sm font-semibold transition-colors hover:bg-secondary">
                    Hubungi Kami
                </a>
            </div>
        </main>
    </body>
</html>
