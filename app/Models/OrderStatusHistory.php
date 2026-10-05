<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';

    protected $fillable = [
        'custom_order_id',
        'from_status',
        'to_status',
        'changed_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function fromLabel(): string
    {
        return CustomOrder::STATUS_LABELS[$this->from_status] ?? $this->from_status;
    }

    public function toLabel(): string
    {
        return CustomOrder::STATUS_LABELS[$this->to_status] ?? $this->to_status;
    }
}
