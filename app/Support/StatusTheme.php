<?php

namespace App\Support;

/**
 * Peta warna status — satu sumber kebenaran utk komponen chip,
 * filter aktif, dan tint baris. Kelas memakai warna Tailwind v4
 * (scan otomatis membaca file PHP ini).
 */
final class StatusTheme
{
    public const FALLBACK = 'bg-primary/15 text-primary';

    /** @return array<string, string> */
    public static function palette(): array
    {
        return [
            // Data Jersey (label resmi JerseyData::STATUSES).
            'Proses Desain' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
            'Revisi' => 'bg-orange-500/15 text-orange-600 dark:text-orange-400',
            'Layout' => 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
            'Printing' => 'bg-violet-500/15 text-violet-600 dark:text-violet-400',
            'Jahit' => 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400',
            'Done' => 'bg-emerald-600/15 text-emerald-700 dark:text-emerald-400',
            'Cancel' => 'bg-destructive/15 text-destructive',
            // Pesanan (kunci CustomOrder::STATUS_LABELS — disjoint dgn jersey).
            'pending' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
            'confirmed' => 'bg-sky-500/15 text-sky-600 dark:text-sky-400',
            'design' => 'bg-violet-500/15 text-violet-600 dark:text-violet-400',
            'ready' => 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400',
            'production' => 'bg-orange-500/15 text-orange-600 dark:text-orange-400',
            'completed' => 'bg-emerald-600/15 text-emerald-700 dark:text-emerald-400',
            'cancelled' => 'bg-destructive/15 text-destructive',
        ];
    }

    public static function classes(?string $status): string
    {
        if ($status === null || $status === '') {
            return self::FALLBACK;
        }

        return self::palette()[$status] ?? self::FALLBACK;
    }
}
