<?php

namespace App\Models;

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
        'delivery_method',
        'is_express',
        'address',
        'size_quantities',
        'notes',
        'order_details',
        'design_file',
    ];

    protected $casts = [
        'deadline' => 'date',
        'size_quantities' => 'array',
        'is_express' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PRODUCTION = 'production';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu Konfirmasi',
        self::STATUS_PRODUCTION => 'Sedang Diproses',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
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
            $lines[] = 'Pesanan: EXPRESS (butuh cepat)';
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
