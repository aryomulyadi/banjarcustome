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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
