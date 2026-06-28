<?php

namespace App\Services;

use App\Support\Money;

class LineItemCalculator
{
    public function normalize(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item) => trim((string) ($item['description'] ?? '')) !== '')
            ->values()
            ->map(function (array $item, int $index): array {
                $quantity = (float) ($item['quantity'] ?? 1);
                $unitPriceCents = array_key_exists('unit_price_cents', $item)
                    ? (int) $item['unit_price_cents']
                    : Money::fromInput($item['unit_price'] ?? 0);

                return [
                    'description' => trim((string) $item['description']),
                    'quantity' => number_format($quantity, 2, '.', ''),
                    'unit_price_cents' => $unitPriceCents,
                    'line_total_cents' => (int) round($quantity * $unitPriceCents),
                    'sort_order' => $index + 1,
                ];
            })
            ->all();
    }

    public function totals(array $normalizedItems, int $discountCents, float $taxRate): array
    {
        $subtotal = array_sum(array_column($normalizedItems, 'line_total_cents'));
        $discount = min(max(0, $discountCents), $subtotal);
        $taxable = max(0, $subtotal - $discount);
        $tax = (int) round($taxable * $taxRate / 100);

        return [
            'subtotal_cents' => $subtotal,
            'discount_cents' => $discount,
            'tax_rate' => $taxRate,
            'tax_cents' => $tax,
            'total_cents' => $taxable + $tax,
        ];
    }
}
