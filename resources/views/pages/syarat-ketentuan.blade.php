<x-layouts.app>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            <span class="text-foreground">Syarat &amp; Ketentuan</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Legal</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Syarat &amp; Ketentuan Pemesanan</h1>
        <p class="mt-2 text-sm text-muted-foreground">Terakhir diperbarui: {{ date('d F Y') }}</p>

        <div class="mt-8 space-y-6 text-sm leading-relaxed text-muted-foreground">
            <section>
                <h2 class="text-base font-semibold text-foreground">1. Pemesanan</h2>
                <p class="mt-2">
                    Pesanan dinyatakan sah setelah dikonfirmasi oleh CS kami melalui WhatsApp. Form di situs
                    ini adalah permintaan pesanan, belum termasuk pembayaran atau ikatan kontrak.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">2. Harga</h2>
                <p class="mt-2">
                    Harga yang tertera pada katalog adalah harga estimasi dan dapat berubah menyesuaikan bahan,
                    jumlah pesanan, tingkat kerumitan desain, dan waktu pengerjaan. Harga final dikonfirmasi
                    sebelum produksi dimulai.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">3. Desain &amp; Bukti Sablon</h2>
                <p class="mt-2">
                    Desain yang dikirim pelanggan menjadi tanggung jawab pelanggan atas hak cipta dan kontennya.
                    Untuk pesanan produksi, kami memberikan bukti desain (sample) sebelum eksekusi massal;
                    perubahan setelah disetujui dapat menambah biaya dan waktu pengerjaan.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">4. Termin &amp; Pembayaran</h2>
                <p class="mt-2">
                    Ketentuan pembayaran (DP, pelunasan, dan metode) disepakati melalui WhatsApp saat konfirmasi
                    pesanan. Pesanan mulai diproses setelah kesepakatan tercapai.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">5. Estimasi Waktu Produksi</h2>
                <p class="mt-2">
                    Estimasi pengerjaan umumnya 3-10 hari kerja tergantung jumlah dan kompleksitas pesanan.
                    Keterlambatan akibat keterlambatan pembayaran desain atau revisi di luar kesepakatan
                    bukan menjadi tanggung jawab kami.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">6. Komplain &amp; Revisi</h2>
                <p class="mt-2">
                    Komplain mengenai hasil pesanan (cacat produksi, salah ukuran, salah desain dari pihak kami)
                    wajib disampaikan maksimal 3 hari setelah pesanan diterima, disertai foto. Kami akan
                    memperbaiki atau mengganti sesuai kesepakatan.
                </p>
            </section>

            <section>
                <h2 class="text-base font-semibold text-foreground">7. Pengiriman</h2>
                <p class="mt-2">
                    Pengiriman dapat dilakukan langsung di lokasi kami atau melalui jasa ekspedisi. Biaya
                    pengiriman ditanggung pelanggan kecuali disepakati lain.
                </p>
            </section>
        </div>
    </div>

</x-layouts.app>
