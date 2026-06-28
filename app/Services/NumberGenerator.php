<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Estimate;
use App\Models\Invoice;

class NumberGenerator
{
    public function estimateNumber(Business $business): string
    {
        return $this->next($business, Estimate::class, 'estimate_number', 'EST');
    }

    public function invoiceNumber(Business $business): string
    {
        return $this->next($business, Invoice::class, 'invoice_number', 'INV');
    }

    private function next(Business $business, string $model, string $column, string $prefix): string
    {
        $latest = $model::query()
            ->withTrashed()
            ->forBusiness($business)
            ->where($column, 'like', $prefix.'-%')
            ->orderByDesc('id')
            ->value($column);

        $next = 1001;

        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $prefix.'-'.$next;
    }
}
