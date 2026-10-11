<?php

namespace App\Models;

use App\Services\StockService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_id', 'medicine_id', 'quantity', 'unit_price', 'subtotal'])]
class SaleItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SaleItem $item): void {
            $item->subtotal = round($item->quantity * (float) $item->unit_price, 2);
        });

        // Take the sold units out of stock (earliest expiry first)
        // and keep the invoice total in sync.
        static::created(function (SaleItem $item): void {
            app(StockService::class)->deduct($item->medicine, $item->quantity);
            $item->sale->recalculateTotal();
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
