<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JerseyData extends Model
{
    use HasFactory;

    public const STATUS_PROSES_DESAIN = 'Proses Desain';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PROSES_DESAIN,
        'Revisi',
        'Layout',
        'Printing',
        'Jahit',
        'Done',
        'Cancel',
    ];

    protected $table = 'jersey_data';

    protected $fillable = [
        'tanggal_masuk',
        'desainer',
        'customer',
        'deadline',
        'jenis_kerah',
        'jenis_bahan',
        'model',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'deadline' => 'date',
        ];
    }

    /**
     * Selisih hari dari hari ini ke deadline (bukan kolom DB).
     * Melewati deadline -> string "TERLAMBAT"; belum ada deadline -> null.
     */
    public function getSisaHariAttribute(): ?int
    {
        if (! $this->deadline) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->deadline->startOfDay(), false);
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->deadline !== null && $this->sisa_hari < 0;
    }

    public function scopeSearch(Builder $query, string $q): Builder
    {
        return $query->where(function (Builder $sub) use ($q) {
            $sub->where('customer', 'like', "%{$q}%")
                ->orWhere('desainer', 'like', "%{$q}%")
                ->orWhere('catatan', 'like', "%{$q}%");
        });
    }

    public function scopeFilterDesainer(Builder $query, ?string $desainer): Builder
    {
        return $desainer ? $query->where('desainer', $desainer) : $query;
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSort(Builder $query, string $sort, string $dir): Builder
    {
        $allowed = [
            'tanggal_masuk' => 'tanggal_masuk',
            'deadline' => 'deadline',
            'customer' => 'customer',
            'desainer' => 'desainer',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        return $query->orderBy($allowed[$sort] ?? 'tanggal_masuk', $dir === 'desc' ? 'desc' : 'asc');
    }
}
