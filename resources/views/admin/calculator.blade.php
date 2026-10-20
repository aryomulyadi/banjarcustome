<x-layouts.admin :title="'Kalkulator Harga'">

    <div
        class="grid gap-5 lg:grid-cols-2"
        x-data='{
            products: {{ json_encode($products, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) }},
            sizeSurcharges: {{ json_encode($sizeSurcharges, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) }},
            jersey: {{ json_encode($jersey, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) }},
            expressFee: {{ json_encode($expressFee) }},
            productId: {{ $products[0]["id"] ?? "null" }},
            customer: "",
            jenis: "",
            bahan: "",
            type: "",
            warna: "",
            sizeText: "",
            qty: 1,
            overrideUnit: "",
            sizeOverrides: {},
            express: false,
            jerseyCollar: "",
            jerseyFabric: "Dryfit Milano",
            jerseyVip: false,
            jerseyDesign: false,
            copied: false,
            get product() { return this.products.find(p => p.id === Number(this.productId)) || null; },
            get isJersey() { return String(this.jenis || "").toLowerCase().includes("jersey"); },
            get collarFee() {
                const found = (this.jersey.collars || []).find(c => c.name === this.jerseyCollar);
                return found ? (Number(found.fee) || 0) : 0;
            },
            get fabricFee() {
                const found = (this.jersey.fabrics || []).find(c => c.name === this.jerseyFabric);
                return found ? (Number(found.fee) || 0) : 0;
            },
            // Tambahan per pcs utk jersey: upgrade kerah + upgrade bahan + VIP (+10.000/pcs).
            get jerseyExtra() {
                if (!this.isJersey) return 0;
                return this.collarFee + this.fabricFee + (this.jerseyVip ? (Number(this.jersey.vip_fee) || 0) : 0);
            },
            // Biaya desain jersey: sekali per pesanan (revisi bebas hingga fix).
            get designFee() {
                return this.isJersey && this.jerseyDesign ? (Number(this.jersey.design_fee) || 0) : 0;
            },
            get jerseyNote() {
                if (!this.isJersey) return "";
                const parts = [];
                if (this.jerseyCollar && this.jerseyCollar !== "O-Neck") parts.push(this.jerseyCollar);
                if (this.jerseyFabric && this.jerseyFabric !== "Dryfit Milano") parts.push(this.jerseyFabric);
                if (this.jerseyVip) parts.push("VIP ORDER");
                return parts.length ? " [" + parts.join(" + ") + "]" : "";
            },
            // Qty untuk tangga harga: dari daftar ukuran bila ada, tanpa lewat
            // rows/baseUnit (hindari circular: rows -> baseUnit -> autoUnit).
            get tierQty() {
                const sizes = this.parsedSizes;
                if (sizes.length > 0) return sizes.reduce((sum, row) => sum + row.qty, 0);
                return Math.max(1, Number(this.qty) || 1);
            },
            get autoUnit() {
                if (!this.product) return null;
                const qty = this.tierQty;
                let price = this.product.basePrice ?? null;
                let bestMin = -1;
                for (const t of this.product.tiers) {
                    if (t.unitPrice === null || t.unitPrice === undefined) continue;
                    // > (bukan >=) agar tangga dengan min_qty kembar mengambil yang
                    // pertama — sama dengan urutan PriceCalculator::unitPrice() PHP.
                    if (t.minQty <= qty && t.minQty > bestMin) { bestMin = t.minQty; price = t.unitPrice; }
                }
                return price;
            },
            get baseUnit() {
                if (this.overrideUnit !== "") {
                    const v = Number(this.overrideUnit);
                    return isNaN(v) ? null : v;
                }
                return this.autoUnit;
            },
            surchargeOf(size) {
                const k = String(size || "").trim().toUpperCase();
                if (k === "") return 0;
                if (this.sizeSurcharges[k] !== undefined) return this.sizeSurcharges[k];
                if (k === "XXL") return 10000;
                if (k === "XXXL") return 20000;
                const m = k.match(/^(\d+)XL$/);
                if (m && Number(m[1]) >= 3) return (Number(m[1]) - 2) * 10000;
                return 0;
            },
            get parsedSizes() {
                const map = {};
                const parts = String(this.sizeText || "").split(/[,;\n]/);
                for (const part of parts) {
                    const m = part.trim().match(/^([A-Za-z0-9]{1,10})\s*:\s*(\d{1,7})$/);
                    if (!m) continue;
                    const qty = Number(m[2]);
                    if (qty <= 0) continue;
                    map[m[1]] = (map[m[1]] || 0) + qty;
                }
                return Object.keys(map).map((size) => ({ size, qty: map[size] }));
            },
            get rows() {
                const sizes = this.parsedSizes;
                if (sizes.length === 0) {
                    return [{
                        size: "—",
                        qty: Math.max(1, Number(this.qty) || 1),
                        unit: this.baseUnit === null ? null : this.baseUnit + this.jerseyExtra,
                        single: true,
                    }];
                }
                return sizes.map((row) => {
                    const saved = this.sizeOverrides[row.size];
                    const unit = saved !== undefined
                        ? saved
                        : (this.baseUnit === null ? null : this.baseUnit + this.jerseyExtra + this.surchargeOf(row.size));
                    return { size: row.size, qty: row.qty, unit, single: false };
                });
            },
            get hasSizeRows() { return this.parsedSizes.length > 0; },
            get qtyTotal() { return this.tierQty; },
            get subtotal() {
                if (this.rows.some((row) => row.unit === null)) return null;
                return this.rows.reduce((sum, row) => sum + row.qty * row.unit, 0);
            },
            get fee() { return (this.express && this.expressFee) ? this.expressFee : 0; },
            get total() { return (this.subtotal || 0) + this.fee + this.designFee; },
            get typeDisplay() { return this.type || (this.product ? this.product.title : "Custom"); },
            setOverride(size, value) {
                if (size === "—") return;
                if (value === "" || value === null) {
                    delete this.sizeOverrides[size];
                    return;
                }
                const n = Number(value);
                if (isNaN(n) || n < 0) return;
                this.sizeOverrides[size] = n;
            },
            rp(n) { return "Rp " + (Number(n) || 0).toLocaleString("id-ID"); },
            get summary() {
                if (this.subtotal === null) return "Isi harga dahulu (pilih produk atau ketik harga per ukuran).";
                const parts = this.rows
                    .map((row) => (row.size === "—" ? row.qty : row.size + " × " + row.qty) + " @ " + this.rp(row.unit))
                    .join(", ");
                return (this.customer ? this.customer + " — " : "")
                    + this.typeDisplay + this.jerseyNote + " (" + this.qtyTotal + " pcs): " + parts
                    + (this.fee ? " + biaya express " + this.rp(this.fee) : "")
                    + (this.designFee ? " + biaya desain " + this.rp(this.designFee) : "")
                    + " = " + this.rp(this.total);
            },
            copy() {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(this.summary).then(() => {
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 1500);
                    });
                }
            },
            printQuote() {
                const params = new URLSearchParams({
                    customer: this.customer || "",
                    jenis: this.jenis || "",
                    bahan: this.bahan || "",
                    type: this.typeDisplay || "",
                    warna: this.warna || "",
                    ukuran: this.sizeText || "",
                    qty: String(this.qtyTotal || 0),
                    unit: String(Math.round(Number(this.baseUnit) || 0)),
                    express: this.express ? "1" : "0",
                    desain: this.designFee > 0 ? "1" : "0",
                });
                const priced = this.rows.filter((row) => row.unit !== null);
                if (this.hasSizeRows && priced.length) {
                    params.set("sizes", JSON.stringify(
                        priced.map((row) => ({ s: row.size, q: row.qty, u: Math.round(row.unit) })),
                    ));
                }
                window.open({{ json_encode(route('admin.calculator.print')) }} + "?" + params.toString(), "_blank");
            },
        }'
    >
        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Input</h2>

            <div class="mt-4 space-y-4">
                <div class="space-y-1.5">
                    <x-label for="calc-customer">Nama Customer</x-label>
                    <x-input id="calc-customer" type="text" x-model="customer" placeholder="cth. Budi Santoso" maxlength="120" />
                </div>

                <div class="space-y-1.5">
                    <x-label for="calc-product">Produk</x-label>
                    <select
                        id="calc-product"
                        x-model="productId"
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.title" :selected="p.id === Number(productId)"></option>
                        </template>
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-label for="calc-jenis">Jenis</x-label>
                        <select
                            id="calc-jenis"
                            x-model="jenis"
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">— pilih —</option>
                            @foreach ($serviceTypes as $title)
                                <option value="{{ $title }}">{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="calc-qty" x-show="!hasSizeRows">Jumlah (pcs)</x-label>
                        <x-input id="calc-qty" type="number" min="1" x-model="qty" value="1" x-show="!hasSizeRows" x-cloak />
                        <p class="text-xs text-muted-foreground" x-show="hasSizeRows" x-cloak>
                            Total <strong x-text="qtyTotal"></strong> pcs (dari daftar ukuran).
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-label for="calc-bahan">Bahan</x-label>
                        <x-input id="calc-bahan" type="text" x-model="bahan" placeholder="cth. Cotton Combed 30s" maxlength="120" />
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="calc-type">Type</x-label>
                        <x-input id="calc-type" type="text" x-model="type" placeholder="otomatis judul produk" maxlength="120" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-label for="calc-warna">Warna</x-label>
                        <x-input id="calc-warna" type="text" x-model="warna" placeholder="cth. Navy" maxlength="120" />
                    </div>

                    <div class="space-y-1.5">
                        <x-label for="calc-ukuran">Ukuran (per baris)</x-label>
                        <x-input id="calc-ukuran" type="text" x-model="sizeText" placeholder="cth. S:10, M:12, L:15" maxlength="200" />
                        <p class="text-xs text-muted-foreground">
                            Tiap ukuran jadi baris terpisah dengan tambahan otomatis
                            (XXL +10.000, 4XL +30.000, dst). Harga per baris bisa diedit di tabel.
                        </p>
                    </div>
                </div>

                {{-- Panel jersey: upgrade kerah/bahan, VIP, biaya desain (hanya utk jenis berlabel jersey) --}}
                <div x-show="isJersey" x-cloak class="space-y-4 rounded-xl border border-primary/30 bg-primary/5 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-widest text-primary">Panel Jersey</p>
                        <p class="text-xs text-muted-foreground">
                            Dasar <strong x-text="rp(jersey.base_price)"></strong>/pcs ·
                            <a href="{{ route('katalog-jersey') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-primary underline hover:no-underline">Katalog Jersey</a>
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <x-label for="calc-collar">Kerah (upgrade)</x-label>
                            <select
                                id="calc-collar"
                                x-model="jerseyCollar"
                                class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                <option value="">— standar O-Neck —</option>
                                <template x-for="c in (jersey.collars || [])" :key="c.name">
                                    <option :value="c.name" x-text="c.name + (c.fee > 0 ? ' (+' + rp(c.fee) + ')' : '')"></option>
                                </template>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <x-label for="calc-fabric">Bahan (upgrade)</x-label>
                            <select
                                id="calc-fabric"
                                x-model="jerseyFabric"
                                class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                <template x-for="f in (jersey.fabrics || [])" :key="f.name">
                                    <option :value="f.name" x-text="f.name + (f.fee > 0 ? ' (+' + rp(f.fee) + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" x-model="jerseyVip" class="mt-0.5 h-4 w-4 border-input accent-[color:var(--color-primary)]">
                            <span>
                                <span class="block font-medium">VIP ORDER <span class="font-normal text-muted-foreground">(+ {{ 'Rp '.number_format((int) ($jersey['vip_fee'] ?? 0), 0, ',', '.') }} per jersey, lebih cepat)</span></span>
                            </span>
                        </label>
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" x-model="jerseyDesign" class="mt-0.5 h-4 w-4 border-input accent-[color:var(--color-primary)]">
                            <span>
                                <span class="block font-medium">Biaya desain <span class="font-normal text-muted-foreground">({{ 'Rp '.number_format((int) ($jersey['design_fee'] ?? 50000), 0, ',', '.') }} sekali, 1-2 hari kerja, revisi bebas)</span></span>
                            </span>
                        </label>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Tambahan per pcs saat ini:
                        <strong x-text="rp(collarFee + fabricFee + (jerseyVip ? (Number(jersey.vip_fee) || 0) : 0))"></strong>
                        <span x-show="jerseyCollar || jerseyFabric || jerseyVip">(<span x-text="[jerseyCollar, jerseyFabric, jerseyVip ? 'VIP' : ''].filter(Boolean).join(' + ')"></span>)</span>
                        — otomatis menambah harga tiap baris.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <x-label for="calc-override">Harga dasar (opsional)</x-label>
                    <x-input id="calc-override" type="number" min="0" x-model="overrideUnit" placeholder="otomatis dari tangga / harga dasar" />
                    <p class="text-xs text-muted-foreground">
                        Dikosongkan = pakai harga otomatis.
                        <template x-if="autoUnit !== null">
                            <span> Saat ini: <strong x-text="rp(autoUnit)"></strong></span>
                        </template>
                        <template x-if="hasSizeRows">
                            <span> Harga per baris = dasar + surcharge ukuran (bisa dioverride manual).</span>
                        </template>
                    </p>
                </div>

                @if ($expressFee !== null)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" x-model="express" class="mt-0.5 h-4 w-4 border-input accent-[color:var(--color-primary)]">
                        <span>
                            <span class="block font-medium">Tambah biaya express <span class="font-normal text-muted-foreground">(+ {{ 'Rp '.number_format($expressFee, 0, ',', '.') }})</span></span>
                        </span>
                    </label>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Tabel Perhitungan</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-2 py-2 font-medium">Jenis</th>
                            <th class="px-2 py-2 font-medium">Bahan</th>
                            <th class="px-2 py-2 font-medium">Type</th>
                            <th class="px-2 py-2 font-medium">Warna</th>
                            <th class="px-2 py-2 font-medium">Ukuran</th>
                            <th class="px-2 py-2 text-right font-medium">Jumlah</th>
                            <th class="px-2 py-2 text-right font-medium">Harga</th>
                            <th class="px-2 py-2 text-right font-medium">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="row.size">
                            <tr class="border-b border-border">
                                <td class="px-2 py-2" x-text="index === 0 ? (jenis || '—') : ''"></td>
                                <td class="px-2 py-2" x-text="index === 0 ? (bahan || '—') : ''"></td>
                                <td class="px-2 py-2" x-text="index === 0 ? typeDisplay : ''"></td>
                                <td class="px-2 py-2" x-text="index === 0 ? (warna || '—') : ''"></td>
                                <td class="px-2 py-2" x-text="row.size"></td>
                                <td class="px-2 py-2 text-right whitespace-nowrap" x-text="row.qty + ' pcs'"></td>
                                <td class="px-2 py-1 text-right whitespace-nowrap">
                                    <template x-if="row.single">
                                        <span x-text="row.unit === null ? '—' : rp(row.unit)"></span>
                                    </template>
                                    <template x-if="!row.single">
                                        <input
                                            type="number"
                                            min="0"
                                            class="w-28 rounded-md border border-input bg-transparent px-2 py-1 text-right text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                            :value="row.unit === null ? '' : row.unit"
                                            :placeholder="row.unit === null ? 'otomatis' : ''"
                                            :aria-label="'Harga ukuran ' + row.size"
                                            @input="setOverride(row.size, $event.target.value)"
                                        />
                                    </template>
                                </td>
                                <td class="px-2 py-2 text-right whitespace-nowrap font-medium" x-text="row.unit === null ? '—' : rp(row.qty * row.unit)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-border pb-3">
                    <dt class="text-muted-foreground">Jumlah total</dt>
                    <dd class="text-right font-medium" x-text="qtyTotal + ' pcs'"></dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-border pb-3">
                    <dt class="text-muted-foreground">Subtotal</dt>
                    <dd class="text-right font-medium" x-text="subtotal === null ? '—' : rp(subtotal)"></dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-border pb-3">
                    <dt class="text-muted-foreground">Biaya express</dt>
                    <dd class="text-right font-medium" x-text="fee ? rp(fee) : '—'"></dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-border pb-3" x-show="designFee > 0" x-cloak>
                    <dt class="text-muted-foreground">Biaya desain jersey</dt>
                    <dd class="text-right font-medium" x-text="rp(designFee)"></dd>
                </div>
            </dl>

            <div class="mt-4 rounded-xl bg-primary/10 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Total pembayaran</p>
                <p class="mt-1 text-2xl font-bold text-foreground" x-text="subtotal === null ? '—' : rp(total)"></p>
            </div>

            <p class="mt-4 rounded-lg bg-secondary/60 px-3 py-2 text-sm text-muted-foreground" x-text="summary" aria-live="polite"></p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <x-button type="button" variant="secondary" @click="copy()" x-text="copied ? 'Tersalin!' : 'Salin ringkasan'"></x-button>
                <x-button type="button" @click="printQuote()">Cetak Penawaran</x-button>
            </div>
        </div>
    </div>

    <p class="mt-5 text-xs text-muted-foreground">
        Kalkulator ini untuk admin (estimasi ke pelanggan). Harga dasar &amp; tangga jumlah diedit di menu Produk.
        Masukkan ukuran "S:10, M:12" agar jadi baris terpisah seperti nota cetak — "Cetak Penawaran" membuka lembar
        cetak dengan tabel yang sama, tanpa nomor pesanan.
        {{ $products === [] ? 'Belum ada produk — tambahkan produk dulu agar harga dasar bisa dihitung.' : '' }}
    </p>

</x-layouts.admin>
