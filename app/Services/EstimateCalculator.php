<?php

namespace App\Services;

use App\Models\Estimate;
use App\Support\Money;

class EstimateCalculator
{
    public function __construct(private readonly LineItemCalculator $lineItems)
    {
    }

    public function sync(Estimate $estimate, array $items, mixed $discount, mixed $taxRate): Estimate
    {
        $normalized = $this->lineItems->normalize($items);
        $totals = $this->lineItems->totals($normalized, Money::fromInput($discount), (float) $taxRate);

        $estimate->items()->delete();

        foreach ($normalized as $item) {
            $estimate->items()->create([
                'business_id' => $estimate->business_id,
                ...$item,
            ]);
        }

        $estimate->forceFill($totals)->save();

        return $estimate->refresh();
    }
}
