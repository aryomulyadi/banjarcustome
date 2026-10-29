<x-layouts.admin :title="'Data Jersey'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Operasional desain &amp; order jersey — terpisah dari Data Target.</p>
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.jersey.import') }}"
                class="inline-flex h-10 items-center justify-center rounded-md border border-border bg-background/80 px-4 text-sm font-semibold transition-colors hover:bg-secondary"
            >
                Impor Excel
            </a>
            <a
                href="{{ route('admin.jersey.create') }}"
                class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                Tambah Data
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.jersey.index') }}" class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end">
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
        {{-- Filter multi-status ala spreadsheet: checklist, boleh lebih dari satu. --}}
        <div class="sm:w-56 relative" x-data="{ statusOpen: false }" @click.outside="statusOpen = false">
            <x-label>Status</x-label>
            <button
                type="button"
                aria-haspopup="true"
                :aria-expanded="statusOpen"
                class="flex h-10 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors hover:bg-secondary/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                @click="statusOpen = !statusOpen"
            >
                <span class="truncate">
                    Status
                    @if ($activeStatuses !== [])
                        <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-bold text-primary-foreground">{{ count($activeStatuses) }}</span>
                    @endif
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-4 w-4 shrink-0 text-muted-foreground transition-transform" :class="statusOpen && 'rotate-180'">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="statusOpen" x-cloak x-transition.origin.top.left class="absolute left-0 z-30 mt-1 w-72 rounded-md border border-border bg-popover p-2 shadow-lg">
                <div class="max-h-64 space-y-0.5 overflow-y-auto">
                    @foreach ($statuses as $option)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 hover:bg-secondary">
                            <input type="checkbox" name="status[]" value="{{ $option }}" @checked(in_array($option, $activeStatuses, true)) class="h-4 w-4 rounded border-input accent-primary" />
                            <x-status-chip :status="$option" />
                            <span class="ml-auto text-xs tabular-nums text-muted-foreground">({{ $statusCounts[$option] ?? 0 }})</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center gap-2 border-t border-border pt-2">
                    <x-button type="submit" class="flex-1">Terapkan</x-button>
                    <a
                        href="{{ route('admin.jersey.index', array_filter(['q' => $search, 'desainer' => $activeDesainer, 'sort' => $activeSort])) }}"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-border px-3 text-xs font-medium transition-colors hover:bg-secondary"
                    >
                        Reset status
                    </a>
                </div>
            </div>
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

    <p class="mt-3 text-xs text-muted-foreground">
        Menampilkan {{ $entries->total() }} data.
        @if ($activeStatuses !== [])
            <span class="ml-1 rounded-full bg-secondary px-2 py-0.5">Filter status: {{ implode(', ', $activeStatuses) }}</span>
        @endif
    </p>

    <div
        x-data="{ selected: [], bulkOpen: false, all: {{ $entries->pluck('id')->all() ? Illuminate\Support\Js::from($entries->pluck('id')->all()) : '[]' }} }"
    >
        {{-- Token CSRF dipakai juga oleh quick-edit (fetch). --}}
        <form id="bulk-jersey" method="POST" action="{{ route('admin.jersey.bulk-status') }}" class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="bulk-status" value="" />
        </form>

        {{-- Toolbar bulk (muncul saat ada baris terpilih). --}}
        <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-primary/30 bg-primary/10 px-4 py-2.5 text-sm" x-show="selected.length > 0" x-cloak>
            <span class="font-medium"><span x-text="selected.length"></span> data terpilih</span>
            <div class="relative" @click.outside="bulkOpen = false">
                <button
                    type="button"
                    class="inline-flex h-10 items-center gap-2 rounded-md border border-border bg-background/80 px-3 text-sm font-semibold transition-colors hover:bg-secondary"
                    @click="bulkOpen = !bulkOpen"
                >
                    Ubah Status
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-4 w-4" :class="bulkOpen && 'rotate-180'">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="bulkOpen" x-cloak x-transition.origin.top.left class="absolute left-0 z-40 mt-1 w-48 rounded-md border border-border bg-popover p-1.5 shadow-lg">
                    @foreach ($statuses as $option)
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-secondary"
                            @click="document.getElementById('bulk-status').value = '{{ $option }}'; document.getElementById('bulk-jersey').submit()"
                        >
                            <x-status-chip :status="$option" />
                        </button>
                    @endforeach
                </div>
            </div>
            <button type="button" class="py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground" @click="selected = []">
                Batal pilihan
            </button>
        </div>

        @if ($entries->isEmpty())
            <div class="mt-4 rounded-xl border border-border bg-card px-5 py-10 text-center text-sm text-muted-foreground">
                @if ($search !== '' || $activeDesainer !== '' || $activeStatuses !== [])
                    Tidak ada data yang cocok dengan filter.
                    <a href="{{ route('admin.jersey.index') }}" class="font-medium text-primary underline underline-offset-2">Reset filter</a>
                @else
                    Belum ada data jersey. Klik "Tambah Data" untuk memulai.
                @endif
            </div>
        @else
            {{-- Tabel utk layar besar — header sticky & kotak scroll vertikal. --}}
            <div class="mt-4 hidden max-h-[70vh] overflow-auto rounded-xl border border-border bg-card lg:block">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-card shadow-[0_1px_0_0_var(--color-border)]">
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-4 py-3 font-medium">
                                <input
                                    type="checkbox"
                                    aria-label="Pilih semua di halaman ini"
                                    class="h-4 w-4 rounded border-input accent-primary"
                                    :checked="all.length > 0 && selected.length === all.length"
                                    @change="selected = $event.target.checked ? [...all] : []"
                                />
                            </th>
                            <th class="px-4 py-3 font-medium">No</th>
                            <th class="px-4 py-3 font-medium">Tanggal Masuk</th>
                            <th class="px-4 py-3 font-medium">Desainer</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Deadline</th>
                            <th class="px-4 py-3 font-medium">Sisa Hari</th>
                            <th class="hidden px-4 py-3 font-medium xl:table-cell">Kerah</th>
                            <th class="hidden px-4 py-3 font-medium xl:table-cell">Bahan</th>
                            <th class="hidden px-4 py-3 font-medium xl:table-cell">Model</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="hidden px-4 py-3 font-medium 2xl:table-cell">Catatan</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            @php
                                $rowTint = $entry->terlambat
                                    ? 'bg-destructive/[0.04]'
                                    : ($entry->status === 'Cancel' ? 'bg-secondary/40' : '');
                            @endphp
                            <tr @class(['border-b border-border last:border-0 hover:bg-secondary/50', $rowTint])>
                                <td class="px-4 py-3 {{ $entry->terlambat ? 'border-l-2 border-l-destructive' : '' }}">
                                    <input
                                        type="checkbox"
                                        form="bulk-jersey"
                                        name="ids[]"
                                        value="{{ $entry->id }}"
                                        x-model="selected"
                                        aria-label="Pilih data {{ $entry->id }}"
                                        class="h-4 w-4 rounded border-input accent-primary"
                                    />
                                </td>
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
                                <td class="hidden px-4 py-3 xl:table-cell">{{ $entry->jenis_kerah ?? '—' }}</td>
                                <td class="hidden px-4 py-3 xl:table-cell">{{ $entry->jenis_bahan ?? '—' }}</td>
                                <td class="hidden px-4 py-3 xl:table-cell">{{ $entry->model ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @include('admin.jersey.partials.quick-status')
                                </td>
                                <td class="hidden px-4 py-3 max-w-48 2xl:table-cell"><span class="block truncate" title="{{ $entry->catatan }}">{{ $entry->catatan ?? '—' }}</span></td>
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
                            <input
                                type="checkbox"
                                form="bulk-jersey"
                                name="ids[]"
                                value="{{ $entry->id }}"
                                x-model="selected"
                                aria-label="Pilih data {{ $entry->id }}"
                                class="h-5 w-5 shrink-0 rounded border-input accent-primary"
                            />
                            <span class="font-semibold text-primary">#{{ $entry->id }}</span>
                            @include('admin.jersey.partials.quick-status')
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
    </div>

</x-layouts.admin>
