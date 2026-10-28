<x-layouts.admin :title="'Detail Pesanan #'.$order->id">
    @php $isAdmin = auth()->user()?->isAdmin(); @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ $isAdmin ? route('admin.orders.index') : route('admin.produksi') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            {{ $isAdmin ? 'Kembali ke daftar pesanan' : 'Kembali ke antrian produksi' }}
        </a>
        @if ($isAdmin)
        <a
            href="{{ route('admin.orders.print', $order) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex h-9 items-center gap-2 rounded-md border border-border px-3 text-sm font-medium transition-colors hover:bg-secondary"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
            Cetak Nota
        </a>
        @endif
    </div>

    <div class="mt-3 grid gap-4 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold">Informasi Pesanan</h2>
                    <span class="rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <dl class="mt-3 grid grid-cols-1 gap-x-6 text-sm sm:grid-cols-2">
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Pelanggan</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->name }}</dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">WhatsApp</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->whatsapp_number }}</dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Produk</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->product?->title ?? 'Custom' }}</dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Jumlah</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->quantity ? $order->quantity.' pcs' : '—' }}</dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Jenis pengerjaan</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->service_type ?? '—' }}</dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Deadline</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->deadline?->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div class="border-b border-border py-2 sm:col-span-2">
                        <dt class="text-xs text-muted-foreground">Estimasi</dt>
                        <dd class="mt-0.5 font-medium">
                            @if ($order->is_express)
                                <span class="rounded-full bg-destructive/15 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-destructive">Express</span>
                                <span class="text-xs font-normal text-muted-foreground">same-day / &lt;10 hari</span>
                            @else
                                Reguler (3–7 hari kaos, 10–12 hari jersey)
                            @endif
                        </dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Harga satuan</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ $order->unit_price !== null ? 'Rp '.number_format($order->unit_price, 0, ',', '.') : '—' }}
                        </dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Subtotal</dt>
                        <dd class="mt-0.5 font-medium">
                            @if ($order->resolvedTotalPrice() !== null)
                                Rp {{ number_format($order->resolvedTotalPrice(), 0, ',', '.') }}
                                @if ($order->total_price === null)
                                    <span class="text-xs font-normal text-muted-foreground">(otomatis)</span>
                                @endif
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if ($order->expressFeeNumber() !== null)
                        <div class="border-b border-border py-2">
                            <dt class="text-xs text-muted-foreground">Biaya express</dt>
                            <dd class="mt-0.5 font-medium">Rp {{ number_format($order->expressFeeNumber(), 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="border-b-2 border-border py-2 sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Total pembayaran</dt>
                        <dd class="mt-0.5 text-base font-semibold">
                            @if ($order->grandTotalPrice() !== null)
                                Rp {{ number_format($order->grandTotalPrice(), 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Pengiriman</dt>
                        <dd class="mt-0.5 font-medium">
                            {{ $order->delivery_method === 'ambil' ? 'Ambil sendiri' : ($order->delivery_method === 'kirim' ? 'Dikirim' : '—') }}
                        </dd>
                    </div>
                    @if ($order->address)
                        <div class="border-b border-border py-2 sm:col-span-2">
                            <dt class="text-xs text-muted-foreground">Alamat</dt>
                            <dd class="mt-0.5 font-medium">{{ $order->address }}</dd>
                        </div>
                    @endif
                    @if ($order->size_quantities)
                        <div class="border-b border-border py-2 sm:col-span-2">
                            <dt class="text-xs text-muted-foreground">Per ukuran</dt>
                            <dd class="mt-0.5 font-medium">
                                @foreach ($order->size_quantities as $size => $qty)
                                    {{ $size }}: {{ $qty }}{{ $loop->last ? '' : ', ' }}
                                @endforeach
                            </dd>
                        </div>
                    @endif
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Tanggal masuk</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                    <div class="border-b border-border py-2">
                        <dt class="text-xs text-muted-foreground">Terakhir diperbarui</dt>
                        <dd class="mt-0.5 font-medium">{{ $order->updated_at->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                    @if ($order->status === \App\Models\CustomOrder::STATUS_CANCELLED && $order->cancel_reason)
                        <div class="border-b border-border py-2 sm:col-span-2">
                            <dt class="text-xs text-muted-foreground">Alasan batal</dt>
                            <dd class="mt-0.5 font-medium">{{ $order->cancel_reason }}</dd>
                        </div>
                    @endif
                    @if ($order->isOverdue())
                        <div class="py-2 sm:col-span-2">
                            <dt class="text-xs text-muted-foreground">Perhatian</dt>
                            <dd class="mt-1"><span class="rounded-full bg-destructive/15 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-destructive">Lewat deadline</span></dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Detail Pesanan</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">
                    {{ $order->order_details ?? '—' }}
                </p>
                @if ($order->notes)
                    <h3 class="mt-3 text-sm font-semibold">Catatan Tambahan</h3>
                    <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $order->notes }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Riwayat Status</h2>
                <ol class="mt-3 space-y-3">
                    @forelse ($order->statusHistory as $history)
                        <li class="flex items-start justify-between gap-4 text-sm">
                            <span>
                                @if ($history->from_status)
                                    {{ $history->fromLabel() }} → {{ $history->toLabel() }}
                                @else
                                    Pesanan dibuat ({{ $history->toLabel() }})
                                @endif
                                @if ($history->changer)
                                    <span class="block text-xs text-muted-foreground">oleh {{ $history->changer->name }}</span>
                                @endif
                            </span>
                            <span class="shrink-0 text-xs text-muted-foreground">{{ $history->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-muted-foreground">Belum ada riwayat.</li>
                    @endforelse
                </ol>
            </div>

            @if ($isAdmin)
            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Harga</h2>
                <p class="mt-1.5 text-xs text-muted-foreground">Untuk nota, halaman detail, dan export Excel. Tidak ditampilkan ke pelanggan.</p>

                @php
                    $unitOld = old('unit_price', $order->unit_price);
                    $totalOld = old('total_price', $order->total_price);
                    $unitJson = $unitOld !== null && $unitOld !== '' ? (string) (int) $unitOld : '""';
                    $totalJson = $totalOld !== null && $totalOld !== '' ? (string) (int) $totalOld : '""';
                @endphp
                <form
                    method="POST"
                    action="{{ route('admin.orders.price', $order) }}"
                    class="mt-3 space-y-3"
                    x-data='{
                        unit: {{ $unitJson }},
                        total: {{ $totalJson }},
                        qty: {{ (int) ($order->quantity ?? 0) }},
                        auto: true,
                        init() {
                            this.$watch("unit", (value) => {
                                if (!this.auto) return;
                                const n = Number(value);
                                this.total = (value === "" || isNaN(n))
                                    ? ""
                                    : String((n * (this.qty || 0)) || "");
                            });
                        },
                    }'
                >
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1.5">
                        <x-label for="unit_price">Harga satuan (Rp)</x-label>
                        <x-input id="unit_price" name="unit_price" type="number" min="0" x-model="unit" placeholder="cth. 45000" />
                        @error('unit_price')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="total_price">Total (Rp)</x-label>
                        <x-input id="total_price" name="total_price" type="number" min="0" x-model="total" @input="auto = false" placeholder="otomatis bila dikosongkan" />
                        @error('total_price')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($order->size_quantities)
                        <div class="space-y-2 border-t border-border pt-3">
                            <x-label>Harga per ukuran (opsional)</x-label>
                            <p class="text-xs text-muted-foreground">
                                Kosongkan untuk otomatis: harga satuan + surcharge ukuran (XXL +10.000, 4XL +30.000, dst).
                            </p>

                            @foreach ($order->size_quantities as $size => $sizeQty)
                                @continue($sizeQty === null || $sizeQty === '' || (int) $sizeQty <= 0)
                                <div class="flex items-center justify-between gap-3">
                                    <label for="size_price_{{ $size }}" class="text-sm">
                                        {{ $size }}
                                        <span class="text-xs text-muted-foreground">— {{ (int) $sizeQty }} pcs</span>
                                    </label>
                                    <input
                                        id="size_price_{{ $size }}"
                                        name="size_prices[{{ $size }}]"
                                        type="number"
                                        min="0"
                                        max="100000000000"
                                        value="{{ old('size_prices.'.$size, $order->size_prices[$size] ?? '') }}"
                                        placeholder="{{ $order->unit_price !== null ? 'Rp '.number_format($order->unit_price + \App\Support\SizeSurcharge::of((string) $size), 0, ',', '.') : 'otomatis' }}"
                                        class="flex h-9 w-36 rounded-md border border-input bg-transparent px-3 py-1 text-sm text-right shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    />
                                </div>
                            @endforeach

                            @error('size_prices')
                                <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <p class="text-xs text-muted-foreground">Saat satuan diubah, total ikut dihitung otomatis (rincian ukuran bila ada, atau satuan × jumlah) kecuali Anda mengetik total sendiri. Kosongkan keduanya untuk menghapus harga.</p>

                    @if ($order->designFee() !== null)
                        <p class="rounded-md border border-border bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                            Biaya desain jersey <span class="font-semibold">Rp {{ number_format($order->designFee(), 0, ',', '.') }}</span> dikenakan di awal (revisi gratis). Masukkan ke kolom Total bila menagih sekaligus, atau tagih terpisah.
                        </p>
                    @endif

                    <x-button type="submit" class="w-full">Simpan Harga</x-button>
                </form>
            </div>
            @endif

            @if ($isAdmin)
            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Desain</h2>

                <div class="mt-2 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-muted-foreground">Pelanggan</span>
                        <span class="text-right font-medium">
                            @if ($order->design_approved_at)
                                Disetujui {{ $order->design_approved_at->translatedFormat('d M Y, H:i') }}
                            @else
                                Belum disetujui
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-muted-foreground">Jumlah revisi</span>
                        <span class="text-right font-medium">{{ $order->design_revisions }}× (gratis)</span>
                    </div>
                    @if ($order->design_revision_reason)
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Alasan revisi terakhir</span>
                            <span class="max-w-[60%] text-right font-medium">{{ $order->design_revision_reason }}</span>
                        </div>
                    @endif
                </div>
                @if ($order->design_file_admin)
                    <a
                        href="{{ route('admin.orders.design.preview', $order) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        Lihat Hasil Desain
                    </a>
                @else
                    <p class="mt-3 text-sm text-muted-foreground">Belum ada hasil desain yang diunggah.</p>
                @endif

                <form method="POST" action="{{ route('admin.orders.design.upload', $order) }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <div class="space-y-1.5">
                        <x-label for="design_file_admin">Unggah hasil desain</x-label>
                        <p class="text-xs text-muted-foreground">jpg, png, webp, atau pdf (maks 10 MB). Ganti file = persetujuan lama direset.</p>
                        <input
                            id="design_file_admin"
                            name="design_file_admin"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border file:border-border file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-foreground hover:file:bg-secondary/70"
                        />
                        @error('design_file_admin')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-button type="submit" class="w-full">Simpan Desain</x-button>
                </form>

                @if ($order->design_approved_at === null && in_array($order->status, [\App\Models\CustomOrder::STATUS_DESIGN, \App\Models\CustomOrder::STATUS_READY], true))
                    <form method="POST" action="{{ route('admin.orders.design.approve', $order) }}" class="mt-3">
                        @csrf
                        <x-button type="submit" variant="secondary" class="w-full">Tandai Disetujui (via WA)</x-button>
                    </form>
                @endif

                @if ($order->design_file)
                    <p class="mt-3 border-t border-border pt-3 text-xs text-muted-foreground">File referensi dari pelanggan:</p>
                    <a
                        href="{{ route('admin.orders.design', $order) }}"
                        class="mt-2 inline-flex h-10 w-full items-center justify-center rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                    >
                        Unduh Referensi
                    </a>
                @endif
            </div>
            @endif
        </div>

        <div class="space-y-4">
            @if ($isAdmin)
            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Perbarui Status</h2>
                @php $nextStatuses = $order->allowedNextStatuses(); @endphp

                @if ($nextStatuses === [])
                    <p class="mt-2 text-sm text-muted-foreground">
                        Status "<span class="font-medium">{{ $order->statusLabel() }}</span>" sudah final.
                    </p>
                @else
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-3" x-data="{ target: '{{ old('status', $nextStatuses[0]) }}' }">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-1.5">
                            <x-label for="status">Pindahkan ke</x-label>
                            <x-select
                                id="status"
                                name="status"
                                x-model="target"
                                aria-label="Status pesanan"
                            >
                                @foreach ($nextStatuses as $key)
                                    <option value="{{ $key }}" {{ old('status', $nextStatuses[0]) === $key ? 'selected' : '' }}>{{ \App\Models\CustomOrder::STATUS_LABELS[$key] }}</option>
                                @endforeach
                            </x-select>
                            @error('status')
                                <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5" x-show="target === 'cancelled'" x-cloak>
                            <x-label for="cancel_reason">Alasan pembatalan (wajib)</x-label>
                            <textarea
                                id="cancel_reason"
                                name="cancel_reason"
                                rows="3"
                                maxlength="1000"
                                placeholder="cth. Pelanggan membatalkan sebelum produksi."
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:text-sm"
                            >{{ old('cancel_reason') }}</textarea>
                            @error('cancel_reason')
                                <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-xs text-muted-foreground">Urutan tahap: Menunggu Konfirmasi → Terkonfirmasi → Proses Desain → Siap Produksi → Sedang Diproses → Selesai. Lompat tahap tidak diizinkan.</p>

                        <x-button type="submit" class="w-full">Simpan Status</x-button>
                    </form>
                @endif
            </div>
            @endif

            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Progres Produksi</h2>
                <p class="mt-1 text-xs text-muted-foreground">Penanda pelengkap status: ok/nok untuk tahap pesan bahan, produksi, dan finish.</p>

                <form method="POST" action="{{ route('admin.orders.progress', $order) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('PATCH')

                    @foreach (\App\Models\CustomOrder::PROGRESS_FLAGS as $flag => $label)
                        <label class="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2 text-sm">
                            <span>{{ $label }}</span>
                            <span class="flex items-center gap-2">
                                <span class="text-xs {{ $order->{$flag} ? 'text-primary' : 'text-muted-foreground' }}">{{ $order->{$flag} ? 'ok' : 'nok' }}</span>
                                <input
                                    type="checkbox"
                                    name="{{ $flag }}"
                                    value="1"
                                    {{ $order->{$flag} ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-input"
                                    aria-label="{{ $label }} ok"
                                >
                            </span>
                        </label>
                    @endforeach

                    <x-button type="submit" class="w-full">Simpan Progres</x-button>
                </form>
            </div>

            @if ($isAdmin)
            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Spesifikasi Nota</h2>
                <p class="mt-1.5 text-xs text-muted-foreground">Diisi admin sebelum cetak nota — tampil di kolom Bahan, Type, dan Warna.</p>

                <form method="POST" action="{{ route('admin.orders.spec', $order) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1.5">
                        <x-label for="bahan">Bahan</x-label>
                        <x-input id="bahan" name="bahan" value="{{ old('bahan', $order->bahan) }}" placeholder="cth. Cotton Combed 30s" maxlength="120" />
                        @error('bahan')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="type">Type</x-label>
                        <x-input id="type" name="type" value="{{ old('type', $order->type) }}" placeholder="otomatis judul produk bila kosong" maxlength="120" />
                        @error('type')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="warna">Warna</x-label>
                        <x-input id="warna" name="warna" value="{{ old('warna', $order->warna) }}" placeholder="cth. Navy / Hitam" maxlength="120" />
                        @error('warna')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="collar_model">Model Kerah <span class="font-normal text-muted-foreground">(jersey)</span></x-label>
                        <x-input id="collar_model" name="collar_model" list="collar-options" value="{{ old('collar_model', $order->collar_model) }}" placeholder="cth. Wangky Zipper" maxlength="60" />
                        <datalist id="collar-options">
                            @foreach (\App\Support\JerseyCatalog::collars() as $collar)
                                <option value="{{ $collar['name'] }}"></option>
                            @endforeach
                        </datalist>
                        <p class="text-xs text-muted-foreground">Opsi upgrade lihat <a href="{{ route('katalog-jersey') }}" target="_blank" class="underline hover:text-foreground">Katalog Jersey</a>.</p>
                        @error('collar_model')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="submit" class="w-full">Simpan Spesifikasi</x-button>
                </form>
            </div>
            @endif

            @if ($isAdmin)
            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">DP 50%</h2>

                @if ($order->dp_received_at)
                    <p class="mt-1.5 text-xs text-muted-foreground">
                        Diterima {{ $order->dp_received_at->translatedFormat('d M Y, H:i') }}.
                    </p>
                @else
                    <p class="mt-1.5 text-xs text-muted-foreground">Catat nominal DP yang sudah diterima — syarat naik ke Siap Produksi.</p>
                @endif

                @php $dpOld = old('dp_amount', $order->dp_amount); @endphp
                <form method="POST" action="{{ route('admin.orders.dp', $order) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1.5">
                        <x-label for="dp_amount">Nominal DP (Rp)</x-label>
                        <x-input
                            id="dp_amount"
                            name="dp_amount"
                            type="number"
                            min="0"
                            max="100000000000"
                            value="{{ $dpOld !== null && $dpOld !== '' ? (int) $dpOld : '' }}"
                            placeholder="kosongkan = belum diterima"
                        />
                        @error('dp_amount')
                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($order->grandTotalPrice() !== null)
                        <p class="text-xs text-muted-foreground">
                            Total pembayaran: <span class="font-semibold">Rp {{ number_format($order->grandTotalPrice(), 0, ',', '.') }}</span>
                            — sisa setelah DP:
                            <span class="font-semibold">Rp {{ number_format(max(0, $order->grandTotalPrice() - (int) ($order->dp_amount ?? 0)), 0, ',', '.') }}</span>
                        </p>
                    @endif

                    <x-button type="submit" class="w-full">Simpan DP</x-button>
                </form>
            </div>
            @endif

            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-base font-semibold">Serah Terima Produksi</h2>
                <p class="mt-1.5 text-xs text-muted-foreground">Kartu Kerja untuk jalur konveksi; SPK untuk over ke operator sublim (jersey).</p>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a
                        href="{{ route('admin.orders.card', $order) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                    >
                        Kartu Kerja
                    </a>
                    <a
                        href="{{ route('admin.orders.spk', $order) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                    >
                        Cetak SPK
                    </a>
                </div>
            </div>

            @if ($isAdmin)
            <div
                class="rounded-xl border border-border bg-card p-4"
                x-data='{
                    templates: @json($order->waTemplates()),
                    selected: "masuk",
                    copied: false,
                    get currentText() {
                        const item = this.templates.find((t) => t.key === this.selected) || this.templates[0];
                        return item ? item.text : "";
                    },
                    get waLink() {
                        const base = @json($order->customerWhatsappLink());
                        return base + (base.includes("?") ? "&" : "?") + "text=" + encodeURIComponent(this.currentText);
                    },
                    async copy() {
                        try {
                            await navigator.clipboard.writeText(this.currentText);
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 1500);
                        } catch (e) {
                            this.$refs.preview.select();
                        }
                    },
                }'
            >
                <h2 class="text-base font-semibold">Pesan WhatsApp</h2>
                <p class="mt-1.5 text-xs text-muted-foreground">Template pesan per tahap — salin lalu tempel di chat, atau buka langsung.</p>

                <div class="mt-3 space-y-3">
                    <div class="space-y-1.5">
                        <x-label for="wa_template">Pilih template</x-label>
                        <x-select
                            id="wa_template"
                            x-model="selected"
                            aria-label="Template pesan WhatsApp"
                        >
                            <template x-for="item in templates" :key="item.key">
                                <option :value="item.key" x-text="item.label" :selected="item.key === selected"></option>
                            </template>
                        </x-select>
                    </div>

                    <textarea
                        rows="6"
                        readonly
                        x-ref="preview"
                        x-text="currentText"
                        class="w-full rounded-md border border-border bg-muted/40 px-3 py-2 text-base sm:text-sm"
                        aria-label="Isi template pesan"
                    ></textarea>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="copy"
                            class="inline-flex h-10 items-center justify-center rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                            x-text="copied ? 'Tersalin ✓' : 'Salin'"
                        ></button>
                        <a
                            :href="waLink"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 items-center justify-center rounded-md bg-primary text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Buka WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <a
                href="{{ $order->customerWhatsappLink() }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-primary text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                Chat Pelanggan via WhatsApp
            </a>
            @endif
        </div>
    </div>

</x-layouts.admin>
