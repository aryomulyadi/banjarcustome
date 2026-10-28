<x-layouts.app
    :title="'Form Pemesanan Custom — Banjar Custome'"
    :description="'Pesan kaos, jersey, kemeja, jaket, dan seragam custom di Banjar Custome. Isi form, unggah desain, lalu konfirmasi via WhatsApp.'"
    :noindex="true"
>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="inline-block py-1.5 hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">Form Pemesanan</span>
        </nav>

        <div class="rounded-2xl border border-border bg-card p-6 sm:p-8">
            <span class="text-xs font-semibold uppercase tracking-widest text-primary">Custom Order</span>
            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Form Pemesanan Custom</h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Isi detail kebutuhan Anda, unggah desain (jika ada), lalu lanjutkan konfirmasi ke WhatsApp kami.
                Estimasi direspon pada jam kerja.
            </p>

            @if ($errors->any())
                <div id="error-summary" tabindex="-1" class="mt-6 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive" role="alert">
                    <p class="font-semibold">Mohon perbaiki data berikut:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <script nonce="{{ request()->attributes->get('csp_nonce') }}">
                    document.getElementById('error-summary')?.focus();
                </script>
            @endif

            <form method="POST" action="{{ route('pesan.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf

                {{-- Honeypot: jangan diisi, jangan dihapus. --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium">Nama Lengkap <span class="text-destructive">*</span></label>
                        <x-input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Nama Anda / nama tim"
                            :class="$errors->has('name') ? 'border-destructive!' : ''"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        />
                        @error('name') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="whatsapp_number" class="text-sm font-medium">Nomor WhatsApp <span class="text-destructive">*</span></label>
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
                        <label for="product_id" class="text-sm font-medium">Produk</label>
                        <x-select
                            id="product_id"
                            name="product_id"
                            :class="$errors->has('product_id') ? 'border-destructive!' : ''"
                            aria-invalid="{{ $errors->has('product_id') ? 'true' : 'false' }}"
                        >
                            <option value="">Custom (sesuai kebutuhan)</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $preselected) == $product->id)>
                                    {{ $product->title }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('product_id') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="service_type" class="text-sm font-medium">Jenis Sablon / Pengerjaan</label>
                        <x-select
                            id="service_type"
                            name="service_type"
                            :class="$errors->has('service_type') ? 'border-destructive!' : ''"
                            aria-invalid="{{ $errors->has('service_type') ? 'true' : 'false' }}"
                        >
                            <option value="">Belum yakin — minta rekomendasi</option>
                            @foreach ($serviceTypes as $type)
                                <option value="{{ $type }}" @selected(old('service_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </x-select>
                        @error('service_type') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium">Jumlah per Ukuran <span class="font-normal text-muted-foreground">(opsional)</span></legend>
                    <p class="text-xs text-muted-foreground">
                        Isi jumlah per ukuran, atau lihat
                        <a href="{{ route('size-chart') }}" class="font-medium text-primary underline-offset-4 hover:underline" target="_blank">panduan ukuran</a>.
                    </p>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
                        @foreach ($sizes as $size)
                            <div class="space-y-1">
                                <label for="sizes_{{ $size }}" class="text-xs font-medium uppercase">{{ $size }}</label>
                                <x-input
                                    id="sizes_{{ $size }}"
                                    name="sizes[{{ $size }}]"
                                    type="number"
                                    min="0"
                                    value="{{ old('sizes.'.$size, 0) }}"
                                    inputmode="numeric"
                                    :class="'text-center '.($errors->has('sizes.'.$size) ? 'border-destructive!' : '')"
                                    aria-invalid="{{ $errors->has('sizes.'.$size) ? 'true' : 'false' }}"
                                />
                            </div>
                        @endforeach
                    </div>
                    @error('sizes') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror

                    <div class="grid gap-5 pt-2 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="quantity" class="text-sm font-medium">Total Jumlah (pcs)</label>
                            <x-input
                                id="quantity"
                                name="quantity"
                                type="number"
                                min="1"
                                value="{{ old('quantity') }}"
                                placeholder="mis. 24"
                                :class="$errors->has('quantity') ? 'border-destructive!' : ''"
                                aria-invalid="{{ $errors->has('quantity') ? 'true' : 'false' }}"
                            />
                            <p class="text-xs text-muted-foreground">Biarkan kosong untuk menghitung otomatis dari jumlah per ukuran.</p>
                            @error('quantity') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="deadline" class="text-sm font-medium">Deadline <span class="font-normal text-muted-foreground">(opsional)</span></label>
                            <x-input
                                id="deadline"
                                name="deadline"
                                type="date"
                                min="{{ now()->format('Y-m-d') }}"
                                value="{{ old('deadline') }}"
                                :class="$errors->has('deadline') ? 'border-destructive!' : ''"
                                aria-invalid="{{ $errors->has('deadline') ? 'true' : 'false' }}"
                            />
                            @error('deadline') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                            <p class="text-xs text-muted-foreground">Estimasi produksi: kaos 3–7 hari kerja, jersey 14–25 hari kerja (VIP ORDER +Rp10.000, lebih cepat) — dihitung setelah desain disetujui. Minimal order jersey 12 pcs.</p>
                        </div>
                    </div>
                </fieldset>

                <div class="rounded-md border border-primary/30 bg-primary/5 p-4">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="checkbox"
                            name="express"
                            value="1"
                            @checked((bool) old('express'))
                            class="mt-0.5 h-4 w-4 border-input accent-[color:var(--color-primary)]"
                        >
                        <span>
                            <span class="block text-sm font-medium">Pesanan Express <span class="font-normal text-muted-foreground">(opsional, biaya tambahan{{ \App\Models\CustomOrder::expressFeeLabel() ? ' '. \App\Models\CustomOrder::expressFeeLabel() : '' }})</span></span>
                            <span class="mt-0.5 block text-xs text-muted-foreground">
                                Kaos bisa dikerjakan same-day dan pesanan lain di bawah 10 hari. Centang lalu konfirmasi detail &amp; biayanya ke CS sebelum produksi.
                            </span>
                        </span>
                    </label>
                </div>

                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium">Pengambilan / Pengiriman</legend>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input type="radio" name="delivery_method" value="kirim" @checked(old('delivery_method') === 'kirim') class="h-4 w-4 border-input accent-[color:var(--color-primary)]">
                            Dikirim (kurir / ekspedisi)
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input type="radio" name="delivery_method" value="ambil" @checked(old('delivery_method') === 'ambil') class="h-4 w-4 border-input accent-[color:var(--color-primary)]">
                            Ambil sendiri di workshop
                        </label>
                    </div>
                    @error('delivery_method') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror

                    <div class="space-y-2 pt-1">
                        <label for="address" class="text-sm font-medium">Alamat Pengiriman <span class="font-normal text-muted-foreground">(wajib jika dikirim)</span></label>
                        <x-textarea
                            id="address"
                            name="address"
                            rows="2"
                            placeholder="Nama penerima, alamat lengkap, kecamatan/kota"
                            :class="'min-h-[70px] '.($errors->has('address') ? 'border-destructive!' : '')"
                            aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                        >{{ old('address') }}</x-textarea>
                        @error('address') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                    </div>
                </fieldset>

                <div class="space-y-2">
                    <label for="order_details" class="text-sm font-medium">Detail Pesanan <span class="font-normal text-muted-foreground">(opsional)</span></label>
                    <x-textarea
                        id="order_details"
                        name="order_details"
                        rows="5"
                        placeholder="Jelaskan kebutuhan Anda: warna, nama tim/komunitas, catatan desain, dll."
                        :class="'min-h-[120px] '.($errors->has('order_details') ? 'border-destructive!' : '')"
                        aria-invalid="{{ $errors->has('order_details') ? 'true' : 'false' }}"
                    >{{ old('order_details') }}</x-textarea>
                    @error('order_details') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="notes" class="text-sm font-medium">Catatan Tambahan <span class="font-normal text-muted-foreground">(opsional)</span></label>
                    <x-input
                        id="notes"
                        name="notes"
                        value="{{ old('notes') }}"
                        placeholder="mis. minta sample dulu, bayar DP 50%, dst."
                        :class="$errors->has('notes') ? 'border-destructive!' : ''"
                        aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                    />
                    @error('notes') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="design_file" class="text-sm font-medium">Upload Desain <span class="font-normal text-muted-foreground">(opsional)</span></label>
                    <input
                        id="design_file"
                        name="design_file"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.pdf,.ai,.psd,.zip"
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring {{ $errors->has('design_file') ? 'border-destructive!' : '' }}"
                        aria-invalid="{{ $errors->has('design_file') ? 'true' : 'false' }}"
                    >
                    <p class="text-xs text-muted-foreground">Format: jpg, png, webp, pdf, ai, psd, zip — maks 5 MB.</p>
                    @error('design_file') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-3 border-t border-border pt-5 sm:flex-row sm:items-center">
                    <x-button type="submit" size="xl">
                        Kirim Pesanan
                    </x-button>
                    <x-button
                        href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya mau pesan custom. Boleh minta info lebih lanjut?') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        size="xl"
                        variant="outline"
                    >
                        Langsung Chat CS
                    </x-button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
