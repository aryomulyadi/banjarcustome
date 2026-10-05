<x-layouts.app
    :title="'Kebijakan Privasi — Banjar Custome'"
    :description="'Kebijakan privasi Banjar Custome: data apa yang dikumpulkan dari form pemesanan, cara penggunaan, penyimpanan, dan hak Anda atas data.'"
>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">Kebijakan Privasi</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Legal</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Kebijakan Privasi</h1>
        <p class="mt-2 text-sm text-muted-foreground">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

        <div class="mt-8 space-y-6 text-sm leading-relaxed text-muted-foreground">
            <section>
                <h2 class="text-base font-semibold text-foreground">1. Data yang Kami Kumpulkan</h2>
                <p class="mt-2">
                    Kami mengumpulkan informasi yang Anda berikan melalui form pemesanan, yaitu nama, nomor
                    WhatsApp, detail pesanan, dan file desain (jika diunggah). Kami tidak meminta data kartu
                    kredit atau informasi keuangan melalui situs ini.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">2. Penggunaan Data</h2>
                <p class="mt-2">
                    Data digunakan untuk memproses pesanan, menghubungi Anda terkait konfirmasi produksi,
                    serta meningkatkan kualitas layanan. Pesanan diproses melalui WhatsApp di nomor resmi kami.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">3. Penyimpanan &amp; Keamanan</h2>
                <p class="mt-2">
                    File desain dan data pesanan disimpan pada server kami dan hanya diakses oleh tim produksi
                    Banjar Custome. Kami tidak menjual atau membagikan data Anda kepada pihak ketiga, kecuali
                    diwajibkan oleh hukum.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">4. Hak Anda</h2>
                <p class="mt-2">
                    Anda dapat meminta penghapusan data pesanan Anda kapan saja dengan menghubungi CS kami
                    melalui WhatsApp {{ config('banjarcustom.whatsapp') }}.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">5. Kontak</h2>
                <p class="mt-2">
                    Pertanyaan seputar privasi dapat disampaikan ke {{ config('banjarcustom.whatsapp') }}
                    atau Instagram {{ config('banjarcustom.instagram_handle') }}.
                </p>
            </section>
        </div>
    </div>

</x-layouts.app>
