<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['invoice_number', 'user_id', 'customer_name', 'customer_phone', 'payment_method', 'total', 'sold_at'])]
class Sale extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'total' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Sale $sale): void {
            $sale->sold_at ??= now();
            $sale->invoice_number ??= static::nextInvoiceNumber($sale->sold_at);
        });
    }

    /** Invoice numbers restart every day: INV-20261012-0001, INV-20261012-0002, ... */
    public static function nextInvoiceNumber(?CarbonInterface $date = null): string
    {
        $prefix = 'INV-'.($date ?? now())->format('Ymd').'-';
        $last = static::where('invoice_number', 'like', $prefix.'%')->max('invoice_number');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->updateQuietly(['total' => $this->items()->sum('subtotal')]);
    }
}
