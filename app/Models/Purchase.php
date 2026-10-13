<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'user_id',
        'supplier_document',
        'currency',
        'prices_include_tax',
        'taxable_amount',
        'exempt_amount',
        'unaffected_amount',
        'subtotal',
        'tax',
        'total',
        'status',
        'notes',
        'received_at',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'prices_include_tax' => 'boolean',
            'taxable_amount' => 'decimal:2',
            'exempt_amount' => 'decimal:2',
            'unaffected_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'received_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isOrdered(): bool
    {
        return $this->status === 'ordered';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
