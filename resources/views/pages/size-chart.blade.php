<x-layouts.app
    :title="'Panduan Ukuran — Banjar Custom'"
    :description="'Tabel panduan ukuran kaos Cotton Combed dan jersey Banjar Custom Banjarmasin dalam sentimeter. Ukuran dewasa dan anak, lengkap dengan toleransi.'"
>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="inline-block py-1.5 hover:text-primary">Beranda</a>
            <span>/</span>
            <span class="text-foreground">Panduan Ukuran</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Size Chart</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Panduan Ukuran</h1>
        <p class="mt-3 text-sm leading-relaxed text-muted-foreground">
            Ukuran dalam sentimeter (cm), terukur secara flat (tidak dililit). Toleransi produksi ±1–2 cm.
            Untuk pesanan seragam, kirimkan daftar ukuran tim Anda agar hasil lebih akurat.
        </p>

        <nav class="sticky top-16 z-20 -mx-4 mt-6 flex gap-1 overflow-x-auto border-b border-border bg-background/95 px-4 py-2 backdrop-blur sm:mx-0 sm:px-0" aria-label="Navigasi size chart">
            <a href="#kaos" class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">Kaos</a>
            <a href="#jersey" class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">Jersey</a>
            <a href="#jaket" class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">Jaket Raglan</a>
        </nav>

        {{-- ===================== KAOS ===================== --}}
        <section id="kaos" class="mt-10 scroll-mt-24">
            <h2 class="text-2xl font-bold sm:text-3xl">Kaos Cotton Combed 30s</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Untuk kaos lengan pendek &amp; panjang Cotton Combed 30s. Bahan cotton sedikit menyusut (±3%) pada pencucian pertama dengan air hangat.
            </p>

            <h3 class="mt-6 text-lg font-semibold">Dewasa</h3>
            <div class="mt-3 overflow-x-auto rounded-xl border border-border">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Ukuran</th>
                            <th class="px-4 py-3 font-semibold">Lebar</th>
                            <th class="px-4 py-3 font-semibold">Panjang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @php
                            $kaosDewasa = [
                                ['XS', '43 cm', '62 cm'],
                                ['S', '45 cm', '66 cm'],
                                ['M', '48 cm', '68 cm'],
                                ['L', '50 cm', '71 cm'],
                                ['XL', '53 cm', '74 cm'],
                                ['2XL', '55 cm', '76 cm'],
                                ['3XL', '58 cm', '79 cm'],
                                ['4XL', '61 cm', '80 cm'],
                                ['5XL', '64 cm', '84 cm'],
                            ];
                        @endphp
                        @foreach ($kaosDewasa as $row)
                            <tr class="bg-card">
                                <td class="px-4 py-3 font-semibold text-primary">{{ $row[0] }}</td>
                                <td class="px-4 py-3">{{ $row[1] }}</td>
                                <td class="px-4 py-3">{{ $row[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Toleransi ukuran: 1–2 cm.</p>

            <h3 class="mt-8 text-lg font-semibold">Anak (Pendek &amp; Panjang)</h3>
            <div class="mt-3 overflow-x-auto rounded-xl border border-border">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Usia (Tahun)</th>
                            <th class="px-4 py-3 font-semibold">Lebar</th>
                            <th class="px-4 py-3 font-semibold">Panjang</th>
                            <th class="px-4 py-3 font-semibold">Lebar Tangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @php
                            $kaosAnak = [
                                ['2', '33 cm', '42 cm', '30 cm'],
                                ['4', '35 cm', '47 cm', '36 cm'],
                                ['6', '36 cm', '51 cm', '41 cm'],
                                ['8', '37 cm', '53 cm', '45 cm'],
                                ['10', '38 cm', '56 cm', '48 cm'],
                                ['12', '39 cm', '58 cm', '50 cm'],
                                ['14', '41 cm', '60 cm', '52 cm'],
                            ];
                        @endphp
                        @foreach ($kaosAnak as $row)
                            <tr class="bg-card">
                                <td class="px-4 py-3 font-semibold text-primary">{{ $row[0] }}</td>
                                <td class="px-4 py-3">{{ $row[1] }}</td>
                                <td class="px-4 py-3">{{ $row[2] }}</td>
                                <td class="px-4 py-3">{{ $row[3] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Toleransi ukuran: 1–2 cm.</p>
        </section>

        {{-- ===================== JERSEY ===================== --}}
        <section id="jersey" class="mt-12 scroll-mt-24 border-t border-border pt-10">
            <h2 class="text-2xl font-bold sm:text-3xl">Jersey</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Jersey printing mengikuti bentuk badan (fit). Pertimbangkan tambahan 1–2 cm jika ingin longgar.
            </p>

            <h3 class="mt-6 text-lg font-semibold">Jersey Standar (Dewasa)</h3>
            <div class="mt-3 overflow-x-auto rounded-xl border border-border">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Ukuran</th>
                            <th class="px-4 py-3 font-semibold">Lebar</th>
                            <th class="px-4 py-3 font-semibold">Panjang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @php
                            $jerseyDewasa = [
                                ['XS', '47 cm', '63 cm'],
                                ['S', '49 cm', '66 cm'],
                                ['M', '51 cm', '69 cm'],
                                ['L', '53 cm', '72 cm'],
                                ['XL', '55 cm', '75 cm'],
                                ['XXL', '57 cm', '78 cm'],
                                ['3XL', '60 cm', '79 cm'],
                                ['4XL', '63 cm', '81 cm'],
                                ['5XL', '66 cm', '82 cm'],
                            ];
                        @endphp
                        @foreach ($jerseyDewasa as $row)
                            <tr class="bg-card">
                                <td class="px-4 py-3 font-semibold text-primary">{{ $row[0] }}</td>
                                <td class="px-4 py-3">{{ $row[1] }}</td>
                                <td class="px-4 py-3">{{ $row[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Toleransi ukuran: 1–2 cm.</p>

            <h3 class="mt-8 text-lg font-semibold">Jersey Anak</h3>
            <div class="mt-3 overflow-x-auto rounded-xl border border-border">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Ukuran</th>
                            <th class="px-4 py-3 font-semibold">Lebar</th>
                            <th class="px-4 py-3 font-semibold">Panjang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @php
                            $jerseyAnak = [
                                ['XS', '33 cm', '41 cm'],
                                ['S', '36 cm', '45 cm'],
                                ['M', '39 cm', '49 cm'],
                                ['L', '42 cm', '53 cm'],
                                ['XL', '44 cm', '57 cm'],
                                ['XXL', '46 cm', '61 cm'],
                            ];
                        @endphp
                        @foreach ($jerseyAnak as $row)
                            <tr class="bg-card">
                                <td class="px-4 py-3 font-semibold text-primary">{{ $row[0] }}</td>
                                <td class="px-4 py-3">{{ $row[1] }}</td>
                                <td class="px-4 py-3">{{ $row[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Toleransi ukuran: 1–2 cm.</p>
        </section>

        {{-- ===================== JAKET RAGLAN ===================== --}}
        <section id="jaket" class="mt-12 scroll-mt-24 border-t border-border pt-10">
            <h2 class="text-2xl font-bold sm:text-3xl">Jaket Raglan</h2>
            <div class="mt-3 overflow-x-auto rounded-xl border border-border">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Ukuran</th>
                            <th class="px-4 py-3 font-semibold">Lebar</th>
                            <th class="px-4 py-3 font-semibold">Panjang</th>
                            <th class="px-4 py-3 font-semibold">Panjang Lengan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @php
                            $jaketRaglan = [
                                ['XS', '50 cm', '61 cm', '47 cm'],
                                ['S', '52 cm', '63 cm', '48 cm'],
                                ['M', '54 cm', '65 cm', '49 cm'],
                                ['L', '56 cm', '67 cm', '50 cm'],
                                ['XL', '58 cm', '69 cm', '51 cm'],
                                ['XXL', '60 cm', '71 cm', '52 cm'],
                                ['3XL', '62 cm', '72 cm', '53 cm'],
                                ['4XL', '64 cm', '74 cm', '54 cm'],
                                ['5XL', '66 cm', '75 cm', '55 cm'],
                            ];
                        @endphp
                        @foreach ($jaketRaglan as $row)
                            <tr class="bg-card">
                                <td class="px-4 py-3 font-semibold text-primary">{{ $row[0] }}</td>
                                <td class="px-4 py-3">{{ $row[1] }}</td>
                                <td class="px-4 py-3">{{ $row[2] }}</td>
                                <td class="px-4 py-3">{{ $row[3] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-10 rounded-xl border border-border bg-muted/50 p-5 text-sm text-muted-foreground">
            <p class="font-medium text-foreground">Catatan:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li>Lebar = lebar dada flat (bukan lingkar). Lingkar dada ≈ 2 × lebar.</li>
                <li>Kemeja formal cenderung pas di badan; ukuran lengan dihitung dari bahu ke pergelangan.</li>
                <li>Bahan Cotton Combed 24s/30s punya toleransi penyusutan ±3% pada pencucian pertama.</li>
            </ul>
            <p class="mt-3">
                Masih ragu? Hubungi CS kami melalui
                <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-primary hover:underline">WhatsApp</a>
                untuk bantuan memilih ukuran.
            </p>
        </div>
    </div>

</x-layouts.app>
