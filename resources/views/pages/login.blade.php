<x-layouts.app>

    <div class="mx-auto max-w-md px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-border bg-card p-8 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary/15 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>

            <h1 class="mt-5 text-2xl font-bold">Akun &amp; Riwayat Pesanan</h1>
            <p class="mt-3 text-sm leading-relaxed text-muted-foreground">
                Fitur login untuk melihat riwayat pesanan sedang disiapkan pada tahap berikutnya.
                Untuk saat ini, semua pesanan dikonfirmasi langsung melalui WhatsApp.
            </p>

            <div class="mt-6 flex flex-col gap-3">
                <a href="{{ route('pesan.create') }}" class="inline-flex h-11 items-center justify-center rounded-md bg-primary px-6 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90">
                    Buat Pesanan
                </a>
                <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-11 items-center justify-center rounded-md border border-border px-6 text-sm font-semibold transition-colors hover:bg-secondary">
                    Chat CS via WhatsApp
                </a>
                <a href="{{ route('home') }}" class="text-sm font-medium text-primary hover:underline">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
