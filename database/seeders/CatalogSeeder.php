<?php

namespace Database\Seeders;

use App\Enums\MedicineForm;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026);

        $categories = collect([
            'Pain & Fever' => 'Analgesics and antipyretics',
            'Antibiotics' => 'Prescription anti-infectives',
            'Allergy & Cold' => 'Antihistamines and decongestants',
            'Digestive Care' => 'Antacids, laxatives and probiotics',
            'Diabetes Care' => 'Oral anti-diabetic medicines',
            'Heart & BP' => 'Cardiac and blood-pressure medicines',
            'Vitamins & Supplements' => 'Multivitamins and minerals',
            'Skin Care' => 'Creams and ointments',
        ])->mapWithKeys(fn (string $description, string $name) => [
            $name => Category::create(['name' => $name, 'slug' => Str::slug($name), 'description' => $description]),
        ]);

        // Fictional distributors for the demo.
        $suppliers = collect([
            ['name' => 'Northwind Pharma Distributors', 'contact_person' => 'Rohit Mehta', 'phone' => '9810000001', 'email' => 'orders@northwind-pharma.test', 'gstin' => '09AAACN1234F1Z5', 'address' => 'Sector 63, Noida, UP'],
            ['name' => 'Bluecross Medical Supply', 'contact_person' => 'Anita Sharma', 'phone' => '9810000002', 'email' => 'sales@bluecross-supply.test', 'gstin' => '07AABCB5678K1Z2', 'address' => 'Okhla Phase II, New Delhi'],
            ['name' => 'Sunrise Healthcare Traders', 'contact_person' => 'Vikram Singh', 'phone' => '9810000003', 'email' => null, 'gstin' => null, 'address' => 'Raj Nagar, Ghaziabad, UP'],
            ['name' => 'Evergreen Generics', 'contact_person' => 'Priya Nair', 'phone' => '9810000004', 'email' => 'hello@evergreen-generics.test', 'gstin' => '27AAECE9012M1Z8', 'address' => 'Andheri East, Mumbai'],
        ])->map(fn (array $data) => Supplier::create($data));

        // [name, generic, category, form, strength, MRP, reorder level, Rx]
        $medicines = [
            ['Paracetamol Tablets', 'Paracetamol', 'Pain & Fever', MedicineForm::Tablet, '650 mg', 32.00, 50, false],
            ['Paracetamol Syrup', 'Paracetamol', 'Pain & Fever', MedicineForm::Syrup, '125 mg/5 ml', 45.00, 20, false],
            ['Ibuprofen Tablets', 'Ibuprofen', 'Pain & Fever', MedicineForm::Tablet, '400 mg', 28.50, 40, false],
            ['Diclofenac Gel', 'Diclofenac diethylamine', 'Pain & Fever', MedicineForm::Ointment, '1% w/w', 110.00, 15, false],
            ['Amoxicillin Capsules', 'Amoxicillin', 'Antibiotics', MedicineForm::Capsule, '500 mg', 96.00, 30, true],
            ['Azithromycin Tablets', 'Azithromycin', 'Antibiotics', MedicineForm::Tablet, '500 mg', 119.00, 25, true],
            ['Ciprofloxacin Tablets', 'Ciprofloxacin', 'Antibiotics', MedicineForm::Tablet, '500 mg', 64.00, 20, true],
            ['Cefixime Tablets', 'Cefixime', 'Antibiotics', MedicineForm::Tablet, '200 mg', 105.00, 20, true],
            ['Cetirizine Tablets', 'Cetirizine', 'Allergy & Cold', MedicineForm::Tablet, '10 mg', 18.00, 40, false],
            ['Levocetirizine Tablets', 'Levocetirizine', 'Allergy & Cold', MedicineForm::Tablet, '5 mg', 42.00, 30, false],
            ['Cough Relief Syrup', 'Dextromethorphan', 'Allergy & Cold', MedicineForm::Syrup, '100 ml', 89.00, 20, false],
            ['Saline Nasal Drops', 'Sodium chloride', 'Allergy & Cold', MedicineForm::Drops, '0.65%', 55.00, 15, false],
            ['Pantoprazole Tablets', 'Pantoprazole', 'Digestive Care', MedicineForm::Tablet, '40 mg', 78.00, 40, true],
            ['Omeprazole Capsules', 'Omeprazole', 'Digestive Care', MedicineForm::Capsule, '20 mg', 52.00, 30, false],
            ['Antacid Gel', 'Aluminium hydroxide + magnesium', 'Digestive Care', MedicineForm::Syrup, '170 ml', 98.00, 15, false],
            ['ORS Powder Sachets', 'Oral rehydration salts', 'Digestive Care', MedicineForm::Powder, '21 g', 22.00, 50, false],
            ['Metformin Tablets', 'Metformin', 'Diabetes Care', MedicineForm::Tablet, '500 mg', 34.00, 60, true],
            ['Glimepiride Tablets', 'Glimepiride', 'Diabetes Care', MedicineForm::Tablet, '2 mg', 88.00, 30, true],
            ['Insulin Glargine Pen', 'Insulin glargine', 'Diabetes Care', MedicineForm::Injection, '100 IU/ml', 690.00, 10, true],
            ['Amlodipine Tablets', 'Amlodipine', 'Heart & BP', MedicineForm::Tablet, '5 mg', 31.00, 50, true],
            ['Telmisartan Tablets', 'Telmisartan', 'Heart & BP', MedicineForm::Tablet, '40 mg', 95.00, 40, true],
            ['Atorvastatin Tablets', 'Atorvastatin', 'Heart & BP', MedicineForm::Tablet, '10 mg', 112.00, 30, true],
            ['Vitamin D3 Capsules', 'Cholecalciferol', 'Vitamins & Supplements', MedicineForm::Capsule, '60,000 IU', 128.00, 20, false],
            ['Multivitamin Tablets', 'Multivitamin + minerals', 'Vitamins & Supplements', MedicineForm::Tablet, null, 165.00, 20, false],
            ['Calcium + D3 Tablets', 'Calcium carbonate + vitamin D3', 'Vitamins & Supplements', MedicineForm::Tablet, '500 mg', 140.00, 25, false],
            ['Iron Syrup', 'Ferrous ascorbate', 'Vitamins & Supplements', MedicineForm::Syrup, '150 ml', 135.00, 10, false],
            ['Clotrimazole Cream', 'Clotrimazole', 'Skin Care', MedicineForm::Ointment, '1% w/w', 72.00, 15, false],
            ['Mupirocin Ointment', 'Mupirocin', 'Skin Care', MedicineForm::Ointment, '2% w/w', 148.00, 10, true],
            ['Calamine Lotion', 'Calamine', 'Skin Care', MedicineForm::Lotion, '100 ml', 85.00, 10, false],
            ['Lubricant Eye Drops', 'Carboxymethylcellulose', 'Skin Care', MedicineForm::Drops, '0.5%', 120.00, 10, false],
        ];

        foreach ($medicines as $i => [$name, $generic, $category, $form, $strength, $price, $reorder, $rx]) {
            $medicine = Medicine::create([
                'category_id' => $categories[$category]->id,
                'supplier_id' => $suppliers[$i % $suppliers->count()]->id,
                'name' => $name,
                'generic_name' => $generic,
                'sku' => 'MED-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'form' => $form,
                'strength' => $strength,
                'selling_price' => $price,
                'reorder_level' => $reorder,
                'requires_prescription' => $rx,
                'is_active' => true,
            ]);

            $this->seedBatches($medicine, $i);
        }
    }

    /**
     * Mix of healthy, low, expiring and expired stock so every dashboard
     * alert has something to show.
     */
    private function seedBatches(Medicine $medicine, int $index): void
    {
        $profile = match (true) {
            $index % 9 === 4 => 'low',        // a few medicines need reordering
            $index % 7 === 3 => 'expiring',   // a batch close to expiry
            $index % 11 === 6 => 'expired',   // an expired batch still on the shelf
            default => 'healthy',
        };

        $cost = round((float) $medicine->selling_price * 0.7, 2);
        $code = strtoupper(Str::substr(Str::slug($medicine->generic_name ?? $medicine->name, ''), 0, 3));

        $batches = match ($profile) {
            'low' => [[mt_rand(5, max(6, $medicine->reorder_level - 5)), mt_rand(200, 500)]],
            'expiring' => [[mt_rand(20, 60), mt_rand(10, 45)], [mt_rand(80, 200), mt_rand(300, 600)]],
            'expired' => [[mt_rand(10, 30), -mt_rand(5, 40)], [mt_rand(100, 250), mt_rand(250, 700)]],
            default => [[mt_rand(120, 300), mt_rand(150, 400)], [mt_rand(100, 250), mt_rand(400, 720)]],
        };

        foreach ($batches as $n => [$quantity, $daysToExpiry]) {
            $expires = today()->addDays($daysToExpiry);

            $medicine->batches()->create([
                'batch_number' => $code.$expires->format('ym').'-'.($n + 1),
                'quantity' => $quantity,
                'cost_price' => $cost,
                'manufactured_at' => $expires->copy()->subMonths(24),
                'expires_at' => $expires,
            ]);
        }
    }
}
