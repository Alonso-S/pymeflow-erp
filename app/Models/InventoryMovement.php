<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type', // in, out, adjustment
        'quantity',
        'previous_stock',
        'new_stock',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'previous_stock' => 'integer',
            'new_stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeIn(Builder $query): Builder
    {
        return $query->where('type', 'in');
    }

    public function scopeOut(Builder $query): Builder
    {
        return $query->where('type', 'out');
    }

    public function scopeAdjustment(Builder $query): Builder
    {
        return $query->where('type', 'adjustment');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Entrada / Compra',
            'out' => 'Salida / Venta',
            'adjustment' => 'Ajuste de Stock',
            default => ucfirst($this->type),
        };
    }
}
