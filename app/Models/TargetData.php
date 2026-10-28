<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TargetData extends Model
{
    use HasFactory;

    public const OK = 'OK';

    public const NOK = 'NOK';

    /** @var list<string> */
    public const FLAGS = ['pesanan_bahan', 'progres_produksi', 'finish'];

    protected $table = 'target_data';

    protected $fillable = [
        'customer',
        'jenis',
        'waktu_pengambilan',
        'type',
        'bahan',
        'warna',
        'pesanan_bahan',
        'progres_produksi',
        'finish',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'waktu_pengambilan' => 'date',
            'total' => 'integer',
        ];
    }

    public static function okNokRule(): array
    {
        return ['required', Rule::in([self::OK, self::NOK])];
    }

    public function scopeSearch(Builder $query, string $q): Builder
    {
        return $query->where(function (Builder $sub) use ($q) {
            $sub->where('customer', 'like', "%{$q}%")
                ->orWhere('jenis', 'like', "%{$q}%")
                ->orWhere('bahan', 'like', "%{$q}%")
                ->orWhere('warna', 'like', "%{$q}%");
        });
    }

    public function scopeFilterJenis(Builder $query, ?string $jenis): Builder
    {
        return $jenis ? $query->where('jenis', $jenis) : $query;
    }

    public function scopeFilterFlag(Builder $query, string $column, ?string $value): Builder
    {
        return $value ? $query->where($column, $value) : $query;
    }

    public function scopeSort(Builder $query, string $sort, string $dir): Builder
    {
        $allowed = [
            'waktu_pengambilan' => 'waktu_pengambilan',
            'customer' => 'customer',
            'jenis' => 'jenis',
            'bahan' => 'bahan',
            'total' => 'total',
            'created_at' => 'created_at',
        ];

        return $query->orderBy($allowed[$sort] ?? 'waktu_pengambilan', $dir === 'desc' ? 'desc' : 'asc');
    }
}
