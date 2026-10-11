<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['medicine_id', 'batch_number', 'quantity', 'cost_price', 'manufactured_at', 'expires_at'])]
class Batch extends Model
{
    use HasFactory;

    public const EXPIRY_WARNING_DAYS = 60;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'cost_price' => 'decimal:2',
            'manufactured_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /** Batches with stock that can still be sold (not expired). */
    public function scopeSellable(Builder $query): Builder
    {
        return $query->whereDate('expires_at', '>=', today());
    }

    /** Batches with stock that expire within the warning window. */
    public function scopeExpiringSoon(Builder $query, int $days = self::EXPIRY_WARNING_DAYS): Builder
    {
        return $query
            ->where('quantity', '>', 0)
            ->whereDate('expires_at', '>=', today())
            ->whereDate('expires_at', '<=', today()->addDays($days));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('expires_at', '<', today());
    }

    public function expiryStatus(): string
    {
        return match (true) {
            $this->expires_at->isBefore(today()) => 'expired',
            $this->expires_at->lte(today()->addDays(self::EXPIRY_WARNING_DAYS)) => 'expiring',
            default => 'ok',
        };
    }
}
