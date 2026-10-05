<x-layouts.app
    :title="'Panduan Ukuran — Banjar Custome'"
    :description="'Tabel panduan ukuran kaos, jersey, kemeja, dan jaket Banjar Custome dalam sentimeter. Pesan seragam tim dengan ukuran akurat.'"
>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            <span class="text-foreground">Panduan Ukuran</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Size Chart</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Panduan Ukuran</h1>
        <p class="mt-3 text-sm leading-relaxed text-muted-foreground">
            Ukuran dalam sentimeter (cm) dan bersifat toleransi ±2 cm. Untuk pesanan seragam, sebaiknya
            kirimkan daftar ukuran tim Anda agar hasil lebih akurat.
        </p>

        <div class="mt-8 overflow-x-auto rounded-xl border border-border">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Ukuran</th>
                        <th class="px-4 py-3 font-semibold">Lingkar Dada</th>
                        <th class="px-4 py-3 font-semibold">Panjang</th>
                        <th class="px-4 py-3 font-semibold">Lingkar Lengan</th>
                        <th class="px-4 py-3 font-semibold">Perkiraan Tinggi (cm)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @php
                        $sizes = [
                            ['S', '92', '66', '38', '155-160'],
                            ['M', '98', '70', '40', '160-165'],
                            ['L', '104', '74', '42', '165-170'],
                            ['XL', '110', '76', '44', '170-175'],
                            ['XXL', '116', '78', '46', '175-180'],
                            ['XXXL', '122', '80', '48', '180-185'],
                        ];
                    @endphp
                    @foreach ($sizes as $row)
                        <tr class="bg-card">
                            <td class="px-4 py-3 font-semibold text-primary">{{ $row[0] }}</td>
                            <td class="px-4 py-3">{{ $row[1] }}</td>
                            <td class="px-4 py-3">{{ $row[2] }}</td>
                            <td class="px-4 py-3">{{ $row[3] }}</td>
                            <td class="px-4 py-3">{{ $row[4] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 rounded-xl border border-border bg-muted/50 p-5 text-sm text-muted-foreground">
            <p class="font-medium text-foreground">Catatan:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li>Jersey printing mengikuti bentuk badan (fit), pertimbangkan tambahan 1-2 cm jika ingin longgar.</li>
                <li>Kemeja formal cenderung pas di badan, ukuran lengan dihitung dari bahu ke pergelangan.</li>
                <li>Bahan cotton sedikit menyusut (±3%) pada pencucian pertama dengan air hangat.</li>
            </ul>
            <p class="mt-3">
                Masih ragu? Hubungi CS kami melalui
                <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-primary hover:underline">WhatsApp</a>
                untuk bantuan memilih ukuran.
            </p>
        </div>
    </div>

</x-layouts.app>
