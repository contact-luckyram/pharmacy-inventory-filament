<?php

namespace App\Models;

use App\Enums\MedicineForm;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id', 'supplier_id', 'name', 'generic_name', 'sku', 'form',
    'strength', 'selling_price', 'reorder_level', 'requires_prescription', 'is_active',
])]
class Medicine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'form' => MedicineForm::class,
            'selling_price' => 'decimal:2',
            'reorder_level' => 'integer',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Adds a `stock` column: units in batches that have not expired yet.
     */
    public function scopeWithSellableStock(Builder $query): Builder
    {
        return $query->withSum(
            ['batches as stock' => fn (Builder $q) => $q->sellable()],
            'quantity',
        );
    }

    /**
     * Medicines whose sellable stock is at or below their reorder level.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw(
            '(select coalesce(sum(quantity), 0) from batches where batches.medicine_id = medicines.id and batches.expires_at >= ?) <= medicines.reorder_level',
            [today()->toDateString()],
        );
    }

    public function availableStock(): int
    {
        return (int) $this->batches()->sellable()->sum('quantity');
    }

    public function displayName(): string
    {
        return trim("{$this->name} {$this->strength}");
    }
}
