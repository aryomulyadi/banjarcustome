<?php

namespace App\Support;

class PriceCalculator
{
    public static function unitPrice(?int $basePrice, ?array $tiers, int $quantity): ?int
    {
        $eligible = [];

        foreach ($tiers ?? [] as $tier) {
            $price = isset($tier['unit_price']) && $tier['unit_price'] !== '' && $tier['unit_price'] !== null
                ? (int) $tier['unit_price']
                : null;
            $min = isset($tier['min_qty']) && $tier['min_qty'] !== '' && $tier['min_qty'] !== null
                ? (int) $tier['min_qty']
                : 0;

            if ($price !== null && $min <= $quantity) {
                $eligible[] = ['min' => $min, 'price' => $price];
            }
        }

        if ($eligible === []) {
            return $basePrice;
        }

        usort($eligible, static fn (array $a, array $b): int => $b['min'] <=> $a['min']);

        return $eligible[0]['price'];
    }

    public static function total(int $unitPrice, int $quantity, ?int $extraFee = null): int
    {
        return ($unitPrice * $quantity) + ($extraFee ?? 0);
    }

    public static function expressFeeNumber(): ?int
    {
        $raw = config('banjarcustom.express_fee');

        if ($raw === null || $raw === '') {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $raw);

        return $digits === '' ? null : (int) $digits;
    }
}
