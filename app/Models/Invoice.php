<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'customer_id',
        'estimate_id',
        'job_id',
        'invoice_number',
        'status',
        'subtotal_cents',
        'discount_cents',
        'tax_rate',
        'tax_cents',
        'total_cents',
        'amount_paid_cents',
        'balance_due_cents',
        'due_date',
        'sent_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:4',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && $this->balance_due_cents > 0
            && ! in_array($this->status, ['paid', 'void'], true);
    }
}
