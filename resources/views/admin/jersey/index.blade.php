<x-layouts.admin :title="'Data Jersey'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Operasional desain & order jersey — terpisah dari Data Target.</p>
        <a
            href="{{ route('admin.jersey.create') }}"
            class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
        >
            Tambah Data
        </a>
    </div>

    <form method="GET" action="{{ route('admin.jersey.index') }}" class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end" x-data>
        <div class="flex-1 min-w-40">
            <x-label for="q">Pencarian</x-label>
            <x-input id="q" name="q" value="{{ $search }}" placeholder="Cari customer, desainer, catatan…" />
        </div>
        <div class="sm:w-44">
            <x-label for="desainer">Desainer</x-label>
            <x-select id="desainer" name="desainer" @change="$el.form.submit()">
                <option value="">Semua desainer</option>
                @foreach ($desainers as $name)
                    <option value="{{ $name }}" @selected($activeDesainer === $name)>{{ $name }}</option>
                @endforeach
            </x-select>
        </div>
        <div class="sm:w-44">
            <x-label for="status">Status</x-label>
            <x-select id="status" name="status" @change="$el.form.submit()">
                <option value="">Semua status</option>
                @foreach ($statuses as $name)
                    <option value="{{ $name }}" @selected($activeStatus === $name)>{{ $name }}</option>
                @endforeach
            </x-select>
        </div>
        <div class="sm:w-56">
            <x-label for="sort">Urutkan</x-label>
            <x-select id="sort" name="sort" @change="$el.form.submit()">
                @foreach ([
                    'tanggal_masuk:desc' => 'Tanggal masuk (terbaru)',
                    'tanggal_masuk:asc' => 'Tanggal masuk (terlama)',
                    'deadline:asc' => 'Deadline (terdekat)',
                    'deadline:desc' => 'Deadline (terjauh)',
                    'customer:asc' => 'Customer (A–Z)',
                    'desainer:asc' => 'Desainer (A–Z)',
                    'status:asc' => 'Status (A–Z)',
                ] as $value => $label)
                    <option value="{{ $value }}" @selected($activeSort === $value)>{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div class="flex gap-2">
            <x-button type="submit">Cari</x-button>
            <x-button href="{{ route('admin.jersey.index') }}" variant="outline">Reset</x-button>
        </div>
    </form>

    <p class="mt-3 text-xs text-muted-foreground">Menampilkan {{ $entries->total() }} data.</p>

    @if ($entries->isEmpty())
        <div class="mt-4 rounded-xl border border-border bg-card px-5 py-10 text-center text-sm text-muted-foreground">
            Belum ada data jersey. Klik "Tambah Data" untuk memulai.
        </div>
    @else
        {{-- Tabel utk layar besar --}}
        <div class="mt-4 hidden overflow-x-auto rounded-xl border border-border bg-card lg:block">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3 font-medium">No</th>
                        <th class="px-4 py-3 font-medium">Tanggal Masuk</th>
                        <th class="px-4 py-3 font-medium">Desainer</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Deadline</th>
                        <th class="px-4 py-3 font-medium">Sisa Hari</th>
                        <th class="px-4 py-3 font-medium">Kerah</th>
                        <th class="px-4 py-3 font-medium">Bahan</th>
                        <th class="px-4 py-3 font-medium">Model</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Catatan</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                            <td class="px-4 py-3 font-semibold text-muted-foreground">{{ $entry->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $entry->tanggal_masuk?->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $entry->desainer }}</td>
                            <td class="px-4 py-3 font-medium">{{ $entry->customer }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $entry->deadline?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($entry->terlambat)
                                    <span class="rounded-full bg-destructive/15 px-2.5 py-0.5 text-xs font-semibold text-destructive">TERLAMBAT</span>
                                @elseif ($entry->sisa_hari !== null)
                                    <span class="{{ $entry->sisa_hari <= 3 ? 'font-semibold text-destructive' : 'text-muted-foreground' }}">{{ $entry->sisa_hari }} hari</span>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $entry->jenis_kerah ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $entry->jenis_bahan ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $entry->model ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $entry->status }}</span>
                            </td>
                            <td class="px-4 py-3 max-w-48"><span class="block truncate" title="{{ $entry->catatan }}">{{ $entry->catatan ?? '—' }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.jersey.edit', $entry) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary">Edit</a>
                                    <form method="POST" action="{{ route('admin.jersey.destroy', $entry) }}" x-data @submit.prevent="if (confirm('Hapus data jersey {{ $entry->customer }}?')) $el.submit()">
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
                        <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $entry->status }}</span>
                        @if ($entry->terlambat)
                            <span class="rounded-full bg-destructive/15 px-2.5 py-0.5 text-xs font-semibold text-destructive">TERLAMBAT</span>
                        @elseif ($entry->sisa_hari !== null)
                            <span class="text-xs {{ $entry->sisa_hari <= 3 ? 'font-semibold text-destructive' : 'text-muted-foreground' }}">{{ $entry->sisa_hari }} hari lagi</span>
                        @endif
                    </div>
                    <p class="mt-2 font-medium">{{ $entry->customer }}</p>
                    <p class="text-xs text-muted-foreground">Desainer: {{ $entry->desainer }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Masuk</dt>
                            <dd class="font-medium">{{ $entry->tanggal_masuk?->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Deadline</dt>
                            <dd class="font-medium">{{ $entry->deadline?->translatedFormat('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Kerah</dt>
                            <dd class="font-medium">{{ $entry->jenis_kerah ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Bahan</dt>
                            <dd class="font-medium">{{ $entry->jenis_bahan ?? '—' }}</dd>
                        </div>
                        <div class="col-span-2 flex justify-between gap-2">
                            <dt class="text-muted-foreground">Model</dt>
                            <dd class="font-medium">{{ $entry->model ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if ($entry->catatan)
                        <p class="mt-2 rounded-md bg-secondary/60 px-3 py-2 text-xs">{{ $entry->catatan }}</p>
                    @endif
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.jersey.edit', $entry) }}" class="inline-flex h-10 flex-1 items-center justify-center rounded-md bg-secondary text-sm font-medium transition-colors hover:bg-secondary/70">Edit</a>
                        <form method="POST" action="{{ route('admin.jersey.destroy', $entry) }}" x-data @submit.prevent="if (confirm('Hapus data jersey {{ $entry->customer }}?')) $el.submit()">
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
