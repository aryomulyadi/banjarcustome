<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOption extends Model
{
    use HasFactory;

    public const KIND_TYPE = 'type';

    public const KIND_MATERIAL = 'material';

    public const KIND_COLOR = 'color';

    public const KIND_COLLAR = 'collar';

    public const KIND_FABRIC = 'fabric';

    public const KIND_DESIGNER = 'designer';

    public const KIND_JERSEY_MODEL = 'jersey_model';

    public const KIND_JERSEY_STATUS = 'jersey_status';

    public const KIND_TARGET_LINE = 'target_line';

    public const KIND_TARGET_TYPE = 'target_type';

    public const KINDS = [
        self::KIND_TYPE,
        self::KIND_MATERIAL,
        self::KIND_COLOR,
        self::KIND_COLLAR,
        self::KIND_FABRIC,
        self::KIND_DESIGNER,
        self::KIND_JERSEY_MODEL,
        self::KIND_JERSEY_STATUS,
        self::KIND_TARGET_LINE,
        self::KIND_TARGET_TYPE,
    ];

    /** Jenis opsi jersey yang punya biaya upgrade & foto. */
    public const JERSEY_KINDS = [
        self::KIND_COLLAR,
        self::KIND_FABRIC,
    ];

    protected $table = 'options';

    protected $fillable = [
        'kind',
        'name',
        'hex',
        'fee',
        'image',
        'active',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'integer',
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeForKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind)->orderBy('name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('name');
    }

    public static function isJerseyKind(string $kind): bool
    {
        return in_array($kind, self::JERSEY_KINDS, true);
    }

    /** @return list<string> */
    public static function namesFor(string $kind): array
    {
        return static::query()
            ->where('kind', $kind)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
