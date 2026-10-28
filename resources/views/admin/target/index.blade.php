<x-layouts.admin :title="'Data Target / Produksi'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Target, bahan, produksi & finishing — terpisah dari Data Jersey.</p>
        <a
            href="{{ route('admin.target.create') }}"
            class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
        >
            Tambah Data
        </a>
    </div>

    <form method="GET" action="{{ route('admin.target.index') }}" class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end" x-data>
        <div class="flex-1 min-w-40">
            <x-label for="q">Pencarian</x-label>
            <x-input id="q" name="q" value="{{ $search }}" placeholder="Cari customer, jenis, bahan, warna…" />
        </div>
        <div class="sm:w-48">
            <x-label for="jenis">Jenis</x-label>
            <x-select id="jenis" name="jenis" @change="$el.form.submit()">
                <option value="">Semua jenis</option>
                @foreach ($jenisList as $name)
                    <option value="{{ $name }}" @selected($activeJenis === $name)>{{ $name }}</option>
                @endforeach
            </x-select>
        </div>
        <div class="sm:w-36">
            <x-label for="pesanan_bahan">Pesan Bahan</x-label>
            <x-select id="pesanan_bahan" name="pesanan_bahan" @change="$el.form.submit()">
                <option value="">Semua</option>
                <option value="OK" @selected($activePesananBahan === 'OK')>OK</option>
                <option value="NOK" @selected($activePesananBahan === 'NOK')>NOK</option>
            </x-select>
        </div>
        <div class="sm:w-36">
            <x-label for="progres_produksi">Produksi</x-label>
            <x-select id="progres_produksi" name="progres_produksi" @change="$el.form.submit()">
                <option value="">Semua</option>
                <option value="OK" @selected($activeProduksi === 'OK')>OK</option>
                <option value="NOK" @selected($activeProduksi === 'NOK')>NOK</option>
            </x-select>
        </div>
        <div class="sm:w-36">
            <x-label for="finish">Finish</x-label>
            <x-select id="finish" name="finish" @change="$el.form.submit()">
                <option value="">Semua</option>
                <option value="OK" @selected($activeFinish === 'OK')>OK</option>
                <option value="NOK" @selected($activeFinish === 'NOK')>NOK</option>
            </x-select>
        </div>
        <div class="sm:w-56">
            <x-label for="sort">Urutkan</x-label>
            <x-select id="sort" name="sort" @change="$el.form.submit()">
                @foreach ([
                    'waktu_pengambilan:asc' => 'Pengambilan (terdekat)',
                    'waktu_pengambilan:desc' => 'Pengambilan (terjauh)',
                    'customer:asc' => 'Customer (A–Z)',
                    'jenis:asc' => 'Jenis (A–Z)',
                    'bahan:asc' => 'Bahan (A–Z)',
                    'total:desc' => 'Total (terbesar)',
                    'total:asc' => 'Total (terkecil)',
                    'created_at:desc' => 'Terbaru ditambah',
                ] as $value => $label)
                    <option value="{{ $value }}" @selected($activeSort === $value)>{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div class="flex gap-2">
            <x-button type="submit">Cari</x-button>
            <x-button href="{{ route('admin.target.index') }}" variant="outline">Reset</x-button>
        </div>
    </form>

    <p class="mt-3 text-xs text-muted-foreground">Menampilkan {{ $entries->total() }} data.</p>

    @if ($entries->isEmpty())
        <div class="mt-4 rounded-xl border border-border bg-card px-5 py-10 text-center text-sm text-muted-foreground">
            Belum ada data target. Klik "Tambah Data" untuk memulai.
        </div>
    @else
        {{-- Tabel utk layar besar --}}
        <div class="mt-4 hidden overflow-x-auto rounded-xl border border-border bg-card lg:block">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3 font-medium">No</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Jenis</th>
                        <th class="px-4 py-3 font-medium">Waktu Pengambilan</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium">Bahan</th>
                        <th class="px-4 py-3 font-medium">Warna</th>
                        <th class="px-4 py-3 font-medium">Pesan Bahan</th>
                        <th class="px-4 py-3 font-medium">Produksi</th>
                        <th class="px-4 py-3 font-medium">Finish</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                            <td class="px-4 py-3 font-semibold text-muted-foreground">{{ $entry->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $entry->customer }}</td>
                            <td class="px-4 py-3">{{ $entry->jenis ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $entry->waktu_pengambilan?->format('d M') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $entry->type ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $entry->bahan ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $entry->warna ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ $entry->pesanan_bahan === 'OK' ? 'bg-primary/15 text-primary' : 'bg-destructive/15 text-destructive' }} px-2.5 py-0.5 text-xs font-semibold">{{ $entry->pesanan_bahan }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ $entry->progres_produksi === 'OK' ? 'bg-primary/15 text-primary' : 'bg-destructive/15 text-destructive' }} px-2.5 py-0.5 text-xs font-semibold">{{ $entry->progres_produksi }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ $entry->finish === 'OK' ? 'bg-primary/15 text-primary' : 'bg-destructive/15 text-destructive' }} px-2.5 py-0.5 text-xs font-semibold">{{ $entry->finish }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $entry->total }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.target.edit', $entry) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary">Edit</a>
                                    <form method="POST" action="{{ route('admin.target.destroy', $entry) }}" x-data @submit.prevent="if (confirm('Hapus data target {{ $entry->customer }}?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-destructive/40 px-2.5 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Kartu utk layar kecil --}}
        <div class="mt-4 space-y-3 lg:hidden">
            @foreach ($entries as $entry)
                <div class="rounded-xl border border-border bg-card p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-primary">#{{ $entry->id }}</span>
                        @if ($entry->jenis)
                            <span class="rounded-full bg-secondary px-2.5 py-0.5 text-xs font-semibold">{{ $entry->jenis }}</span>
                        @endif
                        @if ($entry->waktu_pengambilan)
                            <span class="text-xs text-muted-foreground">Ambil {{ $entry->waktu_pengambilan->format('d M') }}</span>
                        @endif
                    </div>
                    <p class="mt-2 font-medium">{{ $entry->customer }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Type</dt>
                            <dd class="font-medium">{{ $entry->type ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Bahan</dt>
                            <dd class="font-medium">{{ $entry->bahan ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Warna</dt>
                            <dd class="font-medium">{{ $entry->warna ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Total</dt>
                            <dd class="font-semibold">{{ $entry->total }}</dd>
                        </div>
                    </dl>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full {{ $entry->pesanan_bahan === 'OK' ? 'bg-primary/15 text-primary' : 'bg-destructive/15 text-destructive' }} px-2.5 py-1">Bahan: {{ $entry->pesanan_bahan }}</span>
                        <span class="rounded-full {{ $entry->progres_produksi === 'OK' ? 'bg-primary/15 text-primary' : 'bg-destructive/15 text-destructive' }} px-2.5 py-1">Produksi: {{ $entry->progres_produksi }}</span>
                        <span class="rounded-full {{ $entry->finish === 'OK' ? 'bg-primary/15 text-primary' : 'bg-destructive/15 text-destructive' }} px-2.5 py-1">Finish: {{ $entry->finish }}</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.target.edit', $entry) }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-secondary text-sm font-medium transition-colors hover:bg-secondary/70">Edit</a>
                        <form method="POST" action="{{ route('admin.target.destroy', $entry) }}" x-data @submit.prevent="if (confirm('Hapus data target {{ $entry->customer }}?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md border border-destructive/40 px-4 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $entries->links() }}
        </div>
    @endif

</x-layouts.admin>
