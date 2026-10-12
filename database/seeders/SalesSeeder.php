<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);

        $cashier = User::first();
        $customers = ['Aarav Gupta', 'Diya Verma', 'Kabir Khan', 'Meera Iyer', 'Rahul Yadav', 'Sneha Kapoor', 'Arjun Reddy', 'Pooja Joshi', null, null, null];
        $methods = PaymentMethod::cases();

        // Two weeks of billing history, ending today.
        foreach (range(13, 0) as $daysAgo) {
            $day = today()->subDays($daysAgo);

            // Shop hours 9 AM to 9 PM; today's bills stop at the current time.
            $opens = $day->copy()->setTime(9, 0);
            $closes = $daysAgo === 0 ? now() : $day->copy()->setTime(21, 0);

            // Before opening time, show this morning's first bills anyway so "today" isn't empty.
            if ($closes->lessThanOrEqualTo($opens)) {
                $opens = $day->copy();
            }

            $span = max(1, (int) $opens->diffInMinutes($closes));

            // Sorted times keep invoice numbers in the same order as the clock.
            $times = collect(range(1, mt_rand(4, 9)))
                ->map(fn () => $opens->copy()->addMinutes(mt_rand(0, $span)))
                ->sort()
                ->values();

            foreach ($times as $soldAt) {
                $sale = Sale::create([
                    'user_id' => $cashier->id,
                    'customer_name' => $customers[array_rand($customers)],
                    'customer_phone' => mt_rand(0, 1) ? '98'.mt_rand(10000000, 99999999) : null,
                    'payment_method' => $methods[array_rand($methods)],
                    'sold_at' => $soldAt,
                ]);

                $picked = Medicine::inRandomOrder()->limit(mt_rand(1, 4))->get();

                foreach ($picked as $medicine) {
                    $available = $medicine->availableStock();

                    if ($available < 1) {
                        continue;
                    }

                    // Creating the item deducts stock and updates the bill total.
                    $sale->items()->create([
                        'medicine_id' => $medicine->id,
                        'quantity' => min(mt_rand(1, 3), $available),
                        'unit_price' => $medicine->selling_price,
                    ]);
                }

                if (! $sale->items()->exists()) {
                    $sale->delete();
                }
            }
        }
    }
}
