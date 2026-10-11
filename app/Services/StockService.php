<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Medicine;

class StockService
{
    /**
     * Remove units from a medicine's batches using FEFO
     * (first expiry, first out), so the oldest stock is sold first.
     *
     * @throws InsufficientStockException
     */
    public function deduct(Medicine $medicine, int $quantity): void
    {
        $available = $medicine->availableStock();

        if ($quantity > $available) {
            throw new InsufficientStockException($medicine, $quantity, $available);
        }

        $remaining = $quantity;

        $batches = $medicine->batches()
            ->sellable()
            ->where('quantity', '>', 0)
            ->orderBy('expires_at')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining === 0) {
                break;
            }

            $take = min($batch->quantity, $remaining);
            $batch->decrement('quantity', $take);
            $remaining -= $take;
        }
    }
}
