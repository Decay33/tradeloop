<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Database\Factories\EstimateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimate extends Model
{
    /** @use HasFactory<EstimateFactory> */
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'customer_id',
        'estimate_number',
        'service_type_id',
        'status',
        'subtotal_cents',
        'discount_cents',
        'tax_rate',
        'tax_cents',
        'total_cents',
        'notes',
        'terms',
        'expires_at',
        'sent_at',
        'accepted_at',
        'declined_at',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:4',
            'expires_at' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class)->orderBy('sort_order');
    }

    public function job(): HasOne
    {
        return $this->hasOne(Job::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
