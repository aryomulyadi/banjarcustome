<?php

namespace App\Models;

use App\Support\PriceCalculator;
use App\Support\ServiceTypes;
use App\Support\SizeSurcharge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomOrder extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'whatsapp_number',
        'quantity',
        'deadline',
        'service_type',
        'bahan',
        'type',
        'warna',
        'collar_model',
        'delivery_method',
        'is_express',
        'address',
        'size_quantities',
        'notes',
        'order_details',
        'design_file',
        'source',
        'unit_price',
        'total_price',
        'size_prices',
        'pesan_bahan_ok',
        'produksi_ok',
        'finish_ok',
        'cancel_reason',
        'design_file_admin',
        'design_revisions',
        'design_revision_reason',
        'dp_amount',
        'assigned_operator',
    ];

    protected $casts = [
        'deadline' => 'date',
        'size_quantities' => 'array',
        'size_prices' => 'array',
        'is_express' => 'boolean',
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'pesan_bahan_ok' => 'boolean',
        'produksi_ok' => 'boolean',
        'finish_ok' => 'boolean',
        'design_revisions' => 'integer',
        'dp_amount' => 'integer',
        'design_approved_at' => 'datetime',
        'dp_received_at' => 'datetime',
        'spk_issued_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'source' => self::SOURCE_FORM,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DESIGN = 'design';

    public const STATUS_READY = 'ready';

    public const STATUS_PRODUCTION = 'production';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu Konfirmasi',
        self::STATUS_CONFIRMED => 'Terkonfirmasi',
        self::STATUS_DESIGN => 'Proses Desain',
        self::STATUS_READY => 'Siap Produksi',
        self::STATUS_PRODUCTION => 'Sedang Diproses',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /**
     * Matriks transisi status (admin via updateStatus; pelanggan hanya lewat
     * mekanisme revisi desain — lihat ready → design). Lompat tahap ditolak.
     *
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_DESIGN, self::STATUS_CANCELLED],
        self::STATUS_DESIGN => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY => [self::STATUS_PRODUCTION, self::STATUS_DESIGN, self::STATUS_CANCELLED],
        self::STATUS_PRODUCTION => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [self::STATUS_CANCELLED],
        self::STATUS_CANCELLED => [],
    ];

    public const PROGRESS_FLAGS = [
        'pesan_bahan_ok' => 'Pesan Bahan',
        'produksi_ok' => 'Produksi',
        'finish_ok' => 'Finish',
    ];

    public const SOURCE_FORM = 'form';

    public const SOURCE_IMPORT = 'import';

    public const SOURCE_LABELS = [
        self::SOURCE_FORM => 'Formulir',
        self::SOURCE_IMPORT => 'Impor',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (! $order->tracking_token) {
                $order->tracking_token = Str::random(40);
            }
        });

        static::created(function (self $order) {
            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => $order->status,
            ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusLabelFor(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    public function canTransitionTo(string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Status tujuan yang diizinkan dari status saat ini (untuk select admin).
     *
     * @return list<string>
     */
    public function allowedNextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    /**
     * Daftar syarat yang belum terpenuhi untuk naik ke status "ready".
     * Kosong = boleh naik. Pesan memakai bahasa admin (dipakai juga di pesan
     * error validasi).
     *
     * @return list<string>
     */
    public function readyBlockers(): array
    {
        $issues = [];

        if ($this->status !== self::STATUS_DESIGN) {
            $issues[] = 'Status harus dari "'.self::STATUS_LABELS[self::STATUS_DESIGN].'" terlebih dahulu.';
        }

        if ($this->design_approved_at === null) {
            $issues[] = 'Desain belum disetujui pelanggan.';
        }

        if ($this->size_quantities === null || $this->size_quantities === []) {
            $issues[] = 'Rincian ukuran belum diisi.';
        } elseif ($this->quantity) {
            $sum = 0;

            foreach ($this->size_quantities as $qty) {
                if ($qty !== null && $qty !== '') {
                    $sum += (int) $qty;
                }
            }

            if ($sum !== (int) $this->quantity) {
                $issues[] = 'Jumlah rincian ukuran ('.$sum.') tidak sama dengan jumlah pesanan ('.$this->quantity.').';
            }
        }

        if ($this->resolvedTotalPrice() === null) {
            $issues[] = 'Harga pesanan belum diisi.';
        }

        $missingSpec = [];

        foreach (['bahan', 'type', 'warna'] as $column) {
            if ($this->{$column} === null || trim((string) $this->{$column}) === '') {
                $missingSpec[] = $column;
            }
        }

        if ($missingSpec !== []) {
            $issues[] = 'Spesifikasi nota belum lengkap ('.implode(', ', $missingSpec).').';
        }

        if ($this->dp_amount === null) {
            $issues[] = 'DP 50% belum diterima.';
        }

        return $issues;
    }

    public function isOverdue(): bool
    {
        return $this->deadline !== null
            && $this->deadline->lt(today())
            && ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }

    public function isJersey(): bool
    {
        return ServiceTypes::isJerseyTitle($this->service_type);
    }

    /**
     * Biaya desain jersey (Rp50.000 di awal, revisi gratis) — null utk non-jersey.
     */
    public function designFee(): ?int
    {
        if (! $this->isJersey()) {
            return null;
        }

        return (int) config('banjarcustom.design_fee_jersey', 50000);
    }

    public function trackUrl(): string
    {
        return route('track.token', ['token' => $this->tracking_token]);
    }

    /**
     * Template pesan WhatsApp per tahap (otomatis terisi data pesanan +
     * link lacak). Kunci dipakai sebagai identitas stabil di view/test.
     *
     * @return list<array{key: string, label: string, text: string}>
     */
    public function waTemplates(): array
    {
        $track = $this->trackUrl();

        $sizeLine = '';

        if ($this->size_quantities !== null && $this->size_quantities !== []) {
            $parts = [];

            foreach ($this->size_quantities as $size => $qty) {
                if ($qty !== null && $qty !== '') {
                    $parts[] = $size.': '.$qty;
                }
            }

            if ($parts !== []) {
                $sizeLine = "\nUkuran: ".implode(', ', $parts);
            }
        }

        $totalLine = $this->grandTotalPrice() !== null
            ? "\nTotal: Rp ".number_format($this->grandTotalPrice(), 0, ',', '.')
            : '';

        $dpLine = '';

        if ($this->designFee() !== null) {
            $dpLine .= "\nBiaya desain jersey: Rp ".number_format($this->designFee(), 0, ',', '.').' (di awal, revisi gratis).';
        }

        if ($this->grandTotalPrice() !== null) {
            $dpLine .= "\nDP 50%: Rp ".number_format((int) round($this->grandTotalPrice() / 2), 0, ',', '.');
        }

        $deadlineLine = $this->deadline
            ? "\nDeadline: ".$this->deadline->translatedFormat('d F Y')
            : '';

        return [
            [
                'key' => 'masuk',
                'label' => 'Balasan awal (mintai spesifikasi)',
                'text' => "Halo {$this->name}, terima kasih sudah memesan di Banjar Custome!\nMohon lengkapi spesifikasinya: jenis, bahan, warna, jumlah, ukuran, deadline, dan pengiriman (kirim/ambil).",
            ],
            [
                'key' => 'konfirmasi',
                'label' => 'Pesanan dikonfirmasi',
                'text' => "Halo {$this->name}, spesifikasi pesanan kami terima dan sudah dikonfirmasi. Pesanan akan kami teruskan ke designer.",
            ],
            [
                'key' => 'desain',
                'label' => 'Desain sedang dikerjakan',
                'text' => "Halo {$this->name}, pesanan Anda sedang dikerjakan designer. Estimasi desain selesai menyusul — mohon ditunggu.",
            ],
            [
                'key' => 'desain-selesai',
                'label' => 'Desain selesai (minta persetujuan)',
                'text' => "Halo {$this->name}, desain pesanan Anda sudah jadi. Silakan cek dan setujui (atau minta revisi) lewat halaman lacak:\n{$track}",
            ],
            [
                'key' => 'revisi',
                'label' => 'Revisi desain dikerjakan',
                'text' => "Halo {$this->name}, revisi desain sudah kami kerjakan. Silakan cek ulang di halaman lacak:\n{$track}",
            ],
            [
                'key' => 'nota-dp',
                'label' => 'Nota terbit + tagih DP',
                'text' => "Halo {$this->name}, desain sudah fix. Nota bisa dicetak/diunduh lewat halaman lacak: {$track}{$sizeLine}{$totalLine}{$dpLine}{$deadlineLine}\nMohon DP 50% ditransfer agar pesanan masuk antrian produksi.",
            ],
            [
                'key' => 'dp-diterima',
                'label' => 'DP diterima',
                'text' => "Halo {$this->name}, terima kasih DP-nya sudah kami terima. Pesanan masuk antrian produksi.",
            ],
            [
                'key' => 'selesai',
                'label' => 'Pesanan selesai (tagih pelunasan)',
                'text' => "Halo {$this->name}, pesanan Anda sudah selesai dan lolos QC. Pelunasan 50% bisa ditransfer sebelum pengambilan/pengiriman. Terima kasih!",
            ],
        ];
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        return ltrim($digits, '0');
    }

    public function matchesPhone(string $phone): bool
    {
        return self::normalizePhone($this->whatsapp_number) === self::normalizePhone($phone);
    }

    public function customerWhatsappLink(): string
    {
        $digits = '62'.self::normalizePhone($this->whatsapp_number);

        return 'https://wa.me/'.$digits;
    }

    public function successUrl(): string
    {
        return route('pesan.success', ['token' => $this->tracking_token]);
    }

    public function sourceLabel(): string
    {
        return self::SOURCE_LABELS[$this->source] ?? self::SOURCE_FORM;
    }

    public static function expressFeeLabel(): ?string
    {
        $fee = PriceCalculator::expressFeeNumber();

        return $fee !== null ? 'Rp '.number_format($fee, 0, ',', '.') : null;
    }

    public function resolvedTotalPrice(): ?int
    {
        if ($this->total_price !== null) {
            return $this->total_price;
        }

        // Rincian per ukuran (termasuk surcharge) lebih akurat daripada
        // satuan × jumlah ketika size_quantities terisi.
        $rowsSubtotal = $this->sizeRowsSubtotal();
        if ($rowsSubtotal !== null) {
            return $rowsSubtotal;
        }

        if ($this->unit_price !== null && $this->quantity) {
            return $this->unit_price * (int) $this->quantity;
        }

        return null;
    }

    public function expressFeeNumber(): ?int
    {
        if (! $this->is_express) {
            return null;
        }

        return PriceCalculator::expressFeeNumber();
    }

    /**
     * Harga satuan untuk satu ukuran: override admin (size_prices) lebih dulu,
     * lalu harga dasar + surcharge ukuran. Null bila belum ada harga.
     */
    public function sizePriceFor(string $size): ?int
    {
        $saved = $this->size_prices[$size] ?? null;

        if ($saved === null && is_array($this->size_prices)) {
            foreach ($this->size_prices as $key => $value) {
                if (strcasecmp((string) $key, $size) === 0) {
                    $saved = $value;
                    break;
                }
            }
        }

        if ($saved !== null && $saved !== '' && is_numeric($saved)) {
            return (int) $saved;
        }

        if ($this->unit_price !== null) {
            return $this->unit_price + SizeSurcharge::of($size);
        }

        return null;
    }

    /**
     * Baris rincian per ukuran untuk nota (null bila tanpa data ukuran).
     * Tiap baris: size, qty, unit (harga efektif), total (qty × unit).
     *
     * @return array<int, array{size: string, qty: int, unit: int|null, total: int|null}>|null
     */
    public function sizeRows(): ?array
    {
        if ($this->size_quantities === null || $this->size_quantities === []) {
            return null;
        }

        $rows = [];

        foreach ($this->size_quantities as $size => $qty) {
            if ($qty === null || $qty === '') {
                continue;
            }

            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            $unit = $this->sizePriceFor((string) $size);
            $rows[] = [
                'size' => (string) $size,
                'qty' => $qty,
                'unit' => $unit,
                'total' => $unit !== null ? $unit * $qty : null,
            ];
        }

        return $rows !== [] ? $rows : null;
    }

    /**
     * Σ (qty × harga) dari baris ukuran — null bila belum semua baris berharga.
     */
    public function sizeRowsSubtotal(): ?int
    {
        $rows = $this->sizeRows();

        if ($rows === null) {
            return null;
        }

        $subtotal = 0;
        foreach ($rows as $row) {
            if ($row['total'] === null) {
                return null;
            }
            $subtotal += $row['total'];
        }

        return $subtotal;
    }

    public function grandTotalPrice(): ?int
    {
        $subtotal = $this->resolvedTotalPrice();

        if ($subtotal === null) {
            return null;
        }

        return $subtotal + ($this->expressFeeNumber() ?? 0);
    }

    public function whatsappLink(): string
    {
        $lines = [
            'Halo Banjar Custome!',
            'Saya *'.$this->name.'* baru mengirim pesanan via website (Order #'.$this->id.').',
            'Produk: '.($this->product?->title ?? 'Custom'),
        ];

        if ($this->quantity) {
            $lines[] = 'Jumlah: '.$this->quantity.' pcs';
        }

        if ($this->size_quantities !== null && $this->size_quantities !== []) {
            $sizes = collect($this->size_quantities)
                ->filter(fn ($qty) => $qty !== null && $qty !== '')
                ->map(fn ($qty, $size) => $size.': '.$qty)
                ->implode(', ');

            if ($sizes !== '') {
                $lines[] = 'Ukuran: '.$sizes;
            }
        }

        if ($this->deadline) {
            $lines[] = 'Deadline: '.$this->deadline->translatedFormat('d F Y');
        }

        if ($this->is_express) {
            $express = 'Pesanan: EXPRESS (butuh cepat)';
            $fee = self::expressFeeLabel();

            if ($fee !== null) {
                $express .= ' - biaya tambahan '.$fee;
            }

            $lines[] = $express;
        }

        if ($this->service_type) {
            $lines[] = 'Jenis: '.$this->service_type;
        }

        if ($this->delivery_method) {
            $lines[] = 'Pengiriman: '.($this->delivery_method === 'ambil' ? 'Ambil sendiri' : 'Dikirim');
        }

        if ($this->address) {
            $lines[] = 'Alamat: '.$this->address;
        }

        $lines[] = 'WhatsApp saya: '.$this->whatsapp_number;

        if ($this->order_details) {
            $lines[] = 'Detail: '.$this->order_details;
        }

        if ($this->notes) {
            $lines[] = 'Catatan: '.$this->notes;
        }

        $lines[] = 'Mohon dikonfirmasi ya. Terima kasih.';

        return config('banjarcustom.whatsapp_link').'?text='.rawurlencode(implode("\n", $lines));
    }
}
