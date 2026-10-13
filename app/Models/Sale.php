<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'idempotency_key',
        'user_id',
        'customer_id',
        'customer_name',
        'currency',
        'prices_include_tax',
        'taxable_amount',
        'exempt_amount',
        'unaffected_amount',
        'discount_total',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'status',
        'notes',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'prices_include_tax' => 'boolean',
            'taxable_amount' => 'decimal:2',
            'exempt_amount' => 'decimal:2',
            'unaffected_amount' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getTaxedSubtotalAttribute(): float
    {
        return (float) $this->taxable_amount;
    }

    public function getExemptSubtotalAttribute(): float
    {
        return (float) $this->exempt_amount;
    }

    public function getUnaffectedSubtotalAttribute(): float
    {
        return (float) $this->unaffected_amount;
    }
}
