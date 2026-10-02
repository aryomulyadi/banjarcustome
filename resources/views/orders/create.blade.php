<x-layouts.app>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            <span class="text-foreground">Form Pemesanan</span>
        </nav>

        <div class="rounded-2xl border border-border bg-card p-6 sm:p-8">
            <span class="text-xs font-semibold uppercase tracking-widest text-primary">Custom Order</span>
            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Form Pemesanan Custom</h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Isi detail kebutuhan Anda, unggah desain (jika ada), lalu lanjutkan konfirmasi ke WhatsApp kami.
                Estimasi direspon pada jam kerja.
            </p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
                    <p class="font-semibold">Mohon perbaiki data berikut:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('pesan.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium">Nama Lengkap <span class="text-destructive">*</span></label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            placeholder="Nama Anda / nama tim"
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('name') border-destructive @enderror"
                        >
                        @error('name') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="whatsapp_number" class="text-sm font-medium">Nomor WhatsApp <span class="text-destructive">*</span></label>
                        <input
                            id="whatsapp_number"
                            name="whatsapp_number"
                            type="text"
                            value="{{ old('whatsapp_number') }}"
                            required
                            placeholder="0812xxxxxxx"
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('whatsapp_number') border-destructive @enderror"
                        >
                        @error('whatsapp_number') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="product_id" class="text-sm font-medium">Produk</label>
                        <select
                            id="product_id"
                            name="product_id"
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('product_id') border-destructive @enderror"
                        >
                            <option value="">Custom (sesuai kebutuhan)</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $preselected) == $product->id)>
                                    {{ $product->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="quantity" class="text-sm font-medium">Jumlah (pcs)</label>
                        <input
                            id="quantity"
                            name="quantity"
                            type="number"
                            min="1"
                            value="{{ old('quantity') }}"
                            placeholder="mis. 24"
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('quantity') border-destructive @enderror"
                        >
                        @error('quantity') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="order_details" class="text-sm font-medium">Detail Pesanan <span class="text-destructive">*</span></label>
                    <textarea
                        id="order_details"
                        name="order_details"
                        rows="5"
                        required
                        placeholder="Jelaskan kebutuhan Anda: jenis item, warna, ukuran, deadline, nama tim/komunitas, catatan desain, dll."
                        class="flex min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('order_details') border-destructive @enderror"
                    >{{ old('order_details') }}</textarea>
                    @error('order_details') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="design_file" class="text-sm font-medium">Upload Desain <span class="font-normal text-muted-foreground">(opsional)</span></label>
                    <input
                        id="design_file"
                        name="design_file"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.pdf,.ai,.psd,.zip"
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('design_file') border-destructive @enderror"
                    >
                    <p class="text-xs text-muted-foreground">Format: jpg, png, webp, pdf, ai, psd, zip — maks 5 MB.</p>
                    @error('design_file') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-3 border-t border-border pt-5 sm:flex-row sm:items-center">
                    <button type="submit" class="inline-flex h-12 items-center justify-center rounded-md bg-primary px-8 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90">
                        Kirim Pesanan
                    </button>
                    <a
                        href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya mau pesan custom. Boleh minta info lebih lanjut?') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-12 items-center justify-center rounded-md border border-border px-8 text-sm font-semibold transition-colors hover:bg-secondary"
                    >
                        Langsung Chat CS
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
