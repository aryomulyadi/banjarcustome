<x-layouts.admin :title="'Buat Pesanan Baru'">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Kembali ke daftar pesanan
        </a>
    </div>

    <div class="mt-3 rounded-xl border border-border bg-card p-6">
        <h2 class="text-lg font-semibold">Buat Pesanan Baru</h2>
        <p class="mt-1.5 text-sm text-muted-foreground">Isi data pelanggan dan detail pesanan. Harga bisa ditambah nanti di halaman detail pesanan.</p>

        <form method="POST" action="{{ route('admin.orders.store') }}" class="mt-6 space-y-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-label for="name">Nama Pelanggan <span class="text-destructive">*</span></x-label>
                    <x-input id="name" name="name" value="{{ old('name') }}" required placeholder="cth. Budi Santoso" maxlength="255" />
                    @error('name') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="whatsapp_number">Nomor WhatsApp <span class="text-destructive">*</span></x-label>
                    <x-input
                        id="whatsapp_number"
                        name="whatsapp_number"
                        value="{{ old('whatsapp_number') }}"
                        required
                        placeholder="0812xxxxxxx"
                        :class="$errors->has('whatsapp_number') ? 'border-destructive!' : ''"
                        aria-invalid="{{ $errors->has('whatsapp_number') ? 'true' : 'false' }}"
                    />
                    @error('whatsapp_number') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-label for="product_id">Produk</x-label>
                    <x-select
                        id="product_id"
                        name="product_id"
                        :class="$errors->has('product_id') ? 'border-destructive!' : ''"
                        aria-invalid="{{ $errors->has('product_id') ? 'true' : 'false' }}"
                    >
                        <option value="">Custom (sesuai kebutuhan)</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->title }}
                            </option>
                        @endforeach
                    </x-select>
                    @error('product_id') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="service_type">Jenis Pengerjaan</x-label>
                    <x-select
                        id="service_type"
                        name="service_type"
                        :class="$errors->has('service_type') ? 'border-destructive!' : ''"
                        aria-invalid="{{ $errors->has('service_type') ? 'true' : 'false' }}"
                    >
                        <option value="">Otomatis dari produk</option>
                        @foreach ($serviceTypes as $type)
                            <option value="{{ $type }}" @selected(old('service_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </x-select>
                    @error('service_type') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-label for="quantity">Total Jumlah (pcs) <span class="text-muted-foreground">(opsional, otomatis dari ukuran)</span></x-label>
                    <x-input id="quantity" name="quantity" type="number" min="1" max="1000000" value="{{ old('quantity') }}" placeholder="Total jumlah" />
                    @error('quantity') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="deadline">Deadline</x-label>
                    <x-input id="deadline" name="deadline" type="date" value="{{ old('deadline') }}" />
                    @error('deadline') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-2">
                <x-label>Jumlah per Ukuran <span class="font-normal text-muted-foreground">(opsional)</span></x-label>
                <p class="text-xs text-muted-foreground">
                    Isi jumlah per ukuran untuk rincian detail. Sistem otomatis hitung total jika diisi.
                </p>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
                    @foreach (['S', 'M', 'L', 'XL', 'XXL', '3XL'] as $size)
                        <div class="space-y-1">
                            <label for="sizes_{{ $size }}" class="text-xs font-medium uppercase">{{ $size }}</label>
                            <x-input
                                id="sizes_{{ $size }}"
                                name="sizes[{{ $size }}]"
                                type="number"
                                min="0"
                                value="{{ old('sizes.'.$size, 0) }}"
                                inputmode="numeric"
                                placeholder="0"
                            />
                        </div>
                    @endforeach
                </div>
                @error('sizes') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div class="space-y-2">
                    <x-label for="unit_price">Harga Satuan (Rp) <span class="font-normal text-muted-foreground">(opsional)</span></x-label>
                    <x-input id="unit_price" name="unit_price" type="number" min="0" max="100000000000" value="{{ old('unit_price') }}" placeholder="Otomatis jika kosong" />
                    @error('unit_price') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="total_price">Harga Total (Rp) <span class="font-normal text-muted-foreground">(opsional)</span></x-label>
                    <x-input id="total_price" name="total_price" type="number" min="0" max="100000000000" value="{{ old('total_price') }}" placeholder="Otomatis jika kosong" />
                    @error('total_price') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="delivery_method">Pengiriman</x-label>
                    <x-select id="delivery_method" name="delivery_method">
                        <option value="ambil" @selected(old('delivery_method') === 'ambil')>Ambil Di Tempat</option>
                        <option value="kirim" @selected(old('delivery_method') === 'kirim')>Kirim</option>
                    </x-select>
                    @error('delivery_method') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-2">
                <x-label for="address">Alamat Pengiriman <span class="font-normal text-muted-foreground">(wajib bila metode kirim)</span></x-label>
                <x-textarea
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="Isi alamat lengkap untuk pengiriman"
                >{{ old('address') }}</x-textarea>
                @error('address') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <x-label for="notes">Catatan</x-label>
                    <x-textarea id="notes" name="notes" rows="3" placeholder="Opsional" maxlength="1000">{{ old('notes') }}</x-textarea>
                    @error('notes') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <x-label for="order_details">Detail Pesanan</x-label>
                    <x-textarea
                        id="order_details"
                        name="order_details"
                        rows="3"
                        placeholder="Isi minimal 10 karakter"
                        maxlength="3000"
                        :class="$errors->has('order_details') ? 'border-destructive!' : ''"
                    >{{ old('order_details') }}</x-textarea>
                    @error('order_details') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-button type="submit" class="flex-1 sm:flex-none">Buat Pesanan</x-button>
            </div>
        </form>
    </div>

</x-layouts.admin>
