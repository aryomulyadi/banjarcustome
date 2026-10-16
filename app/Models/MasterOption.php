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

    public const KINDS = [
        self::KIND_TYPE,
        self::KIND_MATERIAL,
        self::KIND_COLOR,
    ];

    protected $table = 'options';

    protected $fillable = [
        'kind',
        'name',
        'hex',
    ];

    public function scopeForKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind)->orderBy('name');
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
