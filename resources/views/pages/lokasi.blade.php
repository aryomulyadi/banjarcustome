<x-layouts.app
    :title="'Lokasi Workshop — Banjar Custome Banjarmasin'"
    :description="'Alamat dan jam buka workshop Banjar Custome di Kuripan, Banjarmasin Timur. Rute, peta, dan kontak WhatsApp dalam satu halaman.'"
>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">Lokasi</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Kunjungi Kami</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Lokasi Workshop</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">
            Workshop kami buka untuk konsultasi langsung, pengambilan pesanan, dan diskusi desain.
            Datang saja — atau kabari dulu via WhatsApp agar kami siapkan waktu.
        </p>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="overflow-hidden rounded-2xl border border-border bg-card">
                <iframe
                    src="https://www.google.com/maps?q={{ config('banjarcustom.maps_latitude') }},{{ config('banjarcustom.maps_longitude') }}&z=16&output=embed"
                    class="h-[380px] w-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Peta lokasi Banjar Custome"
                ></iframe>
            </div>

            <div class="space-y-5">
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider">Alamat</h2>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ config('banjarcustom.address') }}</p>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                        <a
                            href="{{ config('banjarcustom.maps_place_url') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline"
                        >
                            Buka di Google Maps
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                        </a>
                        <a
                            href="https://www.google.com/maps/dir/?api=1&destination={{ config('banjarcustom.maps_latitude') }},{{ config('banjarcustom.maps_longitude') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline"
                        >
                            Rute petunjuk arah
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider">Jam Buka</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        @foreach (config('banjarcustom.open_hours') as $day => $hours)
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted-foreground">{{ $day }}</dt>
                                <dd class="font-medium {{ $hours === 'Tutup' ? 'text-destructive' : '' }}">{{ $hours }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider">Kontak</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li>
                            <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-primary hover:underline">
                                WhatsApp {{ config('banjarcustom.whatsapp') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ config('banjarcustom.instagram') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-primary hover:underline">
                                Instagram {{ config('banjarcustom.instagram_handle') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Banjar Custome',
            'description' => 'Jasa konveksi dan sablon custom di Banjarmasin.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl Veteran komplek halim ruko No.01 pagar hijau seberang SMP 7, Kuripan',
                'addressLocality' => 'Banjarmasin',
                'addressRegion' => 'Kalimantan Selatan',
                'postalCode' => '70239',
                'addressCountry' => 'ID',
            ],
            'telephone' => '+62-813-4813-8440',
            'url' => config('app.url'),
            'hasMap' => config('banjarcustom.maps_place_url'),
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => config('banjarcustom.maps_latitude'),
                'longitude' => config('banjarcustom.maps_longitude'),
            ],
            'openingHours' => config('banjarcustom.opening_hours_schema', []),
            'sameAs' => [config('banjarcustom.instagram')],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

</x-layouts.app>
