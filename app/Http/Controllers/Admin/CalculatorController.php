<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\JerseyCatalog;
use App\Support\PriceCalculator;
use App\Support\ServiceTypes;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('title')
            ->get(['id', 'title', 'base_price', 'price_tiers'])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'title' => $product->title,
                'basePrice' => $product->base_price,
                'tiers' => collect($product->price_tiers ?? [])
                    ->map(fn ($tier) => [
                        'minQty' => (int) ($tier['min_qty'] ?? 0),
                        'unitPrice' => isset($tier['unit_price']) && $tier['unit_price'] !== '' && $tier['unit_price'] !== null
                            ? (int) $tier['unit_price']
                            : null,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return view('admin.calculator', [
            'products' => $products,
            'expressFee' => PriceCalculator::expressFeeNumber(),
            'serviceTypes' => ServiceTypes::titles(),
            'sizeSurcharges' => config('banjarcustom.size_surcharges', []),
            'jersey' => JerseyCatalog::config(),
        ]);
    }

    public function print(Request $request): View
    {
        $text = static function (string $key) use ($request): string {
            $value = trim((string) $request->query($key, ''));

            return $value === '' ? '—' : mb_substr($value, 0, 120);
        };

        $qty = (int) $request->query('qty', 0);
        $qty = max(1, min($qty, 1000000));

        $unit = (int) $request->query('unit', 0);
        $unit = max(0, min($unit, 100000000000));

        $express = $request->query('express') === '1';
        $fee = $express ? PriceCalculator::expressFeeNumber() : null;

        // Biaya desain jersey (sekali per pesanan) dari panel jersey kalkulator.
        $designFee = $request->query('desain') === '1'
            ? (int) config('banjarcustom.jersey.design_fee', 50000)
            : null;

        // Baris per ukuran dari kalkulator: [{"s":"S","q":10,"u":45000}, ...].
        // Bounds ketat: JSON ≤ 4000 char, maks 30 baris, ukuran ≤ 12 char,
        // qty 1..1.000.000, harga 0..100 miliar.
        $sizeRows = [];
        $rawSizes = (string) $request->query('sizes', '');

        if (is_string($rawSizes) && $rawSizes !== '' && strlen($rawSizes) <= 4000) {
            $decoded = json_decode($rawSizes, true);

            if (is_array($decoded)) {
                foreach (array_slice($decoded, 0, 30) as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $size = trim((string) ($item['s'] ?? ''));
                    $rowQty = (int) ($item['q'] ?? 0);
                    $rowUnit = (int) ($item['u'] ?? 0);

                    if ($size === '' || strlen($size) > 12 || ! preg_match('/^[A-Za-z0-9 ]+$/', $size)) {
                        continue;
                    }

                    if ($rowQty < 1 || $rowQty > 1000000 || $rowUnit < 0 || $rowUnit > 100000000000) {
                        continue;
                    }

                    $sizeRows[] = ['size' => $size, 'qty' => $rowQty, 'unit' => $rowUnit];
                }
            }
        }

        if ($sizeRows !== []) {
            $qty = array_sum(array_column($sizeRows, 'qty'));
            $subtotal = array_sum(array_map(fn (array $row): int => $row['qty'] * $row['unit'], $sizeRows));
        } else {
            $sizeRows = null;
            $subtotal = $unit * $qty;
        }

        return view('admin.calculator-print', [
            'customer' => $text('customer'),
            'jenis' => $text('jenis'),
            'bahan' => $text('bahan'),
            'type' => $text('type'),
            'warna' => $text('warna'),
            'ukuran' => $text('ukuran'),
            'sizeRows' => $sizeRows,
            'qty' => $qty,
            'unit' => $unit,
            'subtotal' => $subtotal,
            'fee' => $fee,
            'designFee' => $designFee,
            'grandTotal' => $subtotal + ($fee ?? 0) + ($designFee ?? 0),
        ]);
    }
}
