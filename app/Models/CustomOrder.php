<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrder extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'name',
        'whatsapp_number',
        'quantity',
        'order_details',
        'design_file',
        'status',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PRODUCTION = 'production';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu Konfirmasi',
        self::STATUS_PRODUCTION => 'Sedang Diproses',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
        return $this->normalizePhone($this->whatsapp_number) === self::normalizePhone($phone);
    }

    public function customerWhatsappLink(): string
    {
        $digits = '62'.self::normalizePhone($this->whatsapp_number);

        return 'https://wa.me/'.$digits;
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

        $lines[] = 'WhatsApp saya: '.$this->whatsapp_number;
        $lines[] = 'Detail: '.$this->order_details;
        $lines[] = 'Mohon dikonfirmasi ya. Terima kasih.';

        return config('banjarcustom.whatsapp_link').'?text='.rawurlencode(implode("\n", $lines));
    }
}
