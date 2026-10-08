<x-layouts.app
    :title="'FAQ — Pertanyaan Umum Banjar Custome'"
    :description="'Jawaban pertanyaan umum seputar pemesanan di Banjar Custome: harga, DP, estimasi produksi, revisi desain, minimal order, dan pengiriman.'"
>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">FAQ</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Pertanyaan Umum</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Pertanyaan yang Sering Diajukan</h1>
        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
            Tidak menemukan jawabannya? Chat kami langsung via WhatsApp — dibalas pada jam kerja.
        </p>

        <div class="mt-8 space-y-4">
            @forelse ($faqs as $faq)
                <details class="group rounded-xl border border-border bg-card p-5 open:bg-muted/40">
                    <summary class="flex cursor-pointer list-none items-start justify-between gap-4 text-sm font-semibold sm:text-base">
                        <span>{{ $faq->question }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-primary transition-transform group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    </summary>
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $faq->answer }}</p>
                </details>
            @empty
                <div class="rounded-xl border border-border bg-card p-8 text-center text-sm text-muted-foreground">
                    Belum ada pertanyaan yang dipublikasikan. Silakan hubungi kami via WhatsApp.
                </div>
            @endforelse
        </div>

        <div class="mt-10 rounded-2xl border border-border bg-muted/40 p-6 text-center">
            <h2 class="text-lg font-bold">Masih ada pertanyaan?</h2>
            <p class="mt-1 text-sm text-muted-foreground">Kami siap membantu memilih bahan, desain, dan estimasi harga.</p>
            <a href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya punya pertanyaan tentang pesanan.') }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex h-11 items-center rounded-md bg-primary px-6 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                Chat via WhatsApp
            </a>
        </div>
    </div>

    @if ($faqs->isNotEmpty())
        <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                ])->values(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

</x-layouts.app>
