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
            $billsToday = mt_rand(4, 9);

            for ($b = 0; $b < $billsToday; $b++) {
                $soldAt = today()->subDays($daysAgo)->setTime(mt_rand(9, 20), mt_rand(0, 59));

                if ($soldAt->isFuture()) {
                    $soldAt = now()->subMinutes(mt_rand(5, 120));
                }

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
