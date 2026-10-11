<?php

namespace Tests\Feature;

use App\Enums\MedicineForm;
use App\Enums\PaymentMethod;
use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\Medicines\MedicineResource;
use App\Filament\Resources\Sales\SaleResource;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAndBillingTest extends TestCase
{
    use RefreshDatabase;

    private function medicine(): Medicine
    {
        $category = Category::create(['name' => 'Pain & Fever', 'slug' => 'pain-fever']);

        return Medicine::create([
            'category_id' => $category->id,
            'name' => 'Paracetamol Tablets',
            'sku' => 'MED-TEST',
            'form' => MedicineForm::Tablet,
            'selling_price' => 30,
            'reorder_level' => 10,
        ]);
    }

    public function test_expired_batches_do_not_count_as_stock(): void
    {
        $medicine = $this->medicine();
        $medicine->batches()->create(['batch_number' => 'OLD', 'quantity' => 50, 'cost_price' => 20, 'expires_at' => today()->subDay()]);
        $medicine->batches()->create(['batch_number' => 'NEW', 'quantity' => 30, 'cost_price' => 20, 'expires_at' => today()->addYear()]);

        $this->assertSame(30, $medicine->availableStock());
    }

    public function test_a_sale_takes_stock_from_the_batch_that_expires_first(): void
    {
        $medicine = $this->medicine();
        $soon = $medicine->batches()->create(['batch_number' => 'SOON', 'quantity' => 5, 'cost_price' => 20, 'expires_at' => today()->addMonth()]);
        $later = $medicine->batches()->create(['batch_number' => 'LATER', 'quantity' => 20, 'cost_price' => 20, 'expires_at' => today()->addYear()]);

        $sale = Sale::create(['payment_method' => PaymentMethod::Cash]);
        $sale->items()->create(['medicine_id' => $medicine->id, 'quantity' => 8, 'unit_price' => 30]);

        $this->assertSame(0, $soon->fresh()->quantity);
        $this->assertSame(17, $later->fresh()->quantity);
        $this->assertEquals(240, $sale->fresh()->total);
        $this->assertStringStartsWith('INV-'.now()->format('Ymd'), $sale->invoice_number);
    }

    public function test_selling_more_than_is_in_stock_is_refused(): void
    {
        $medicine = $this->medicine();
        $medicine->batches()->create(['batch_number' => 'B1', 'quantity' => 3, 'cost_price' => 20, 'expires_at' => today()->addYear()]);

        $this->expectException(InsufficientStockException::class);

        Sale::create(['payment_method' => PaymentMethod::Upi])
            ->items()->create(['medicine_id' => $medicine->id, 'quantity' => 4, 'unit_price' => 30]);
    }

    public function test_low_stock_scope_finds_medicines_at_or_below_reorder_level(): void
    {
        $medicine = $this->medicine();
        $medicine->batches()->create(['batch_number' => 'B1', 'quantity' => 10, 'cost_price' => 20, 'expires_at' => today()->addYear()]);

        $this->assertTrue(Medicine::lowStock()->whereKey($medicine->id)->exists());
    }

    public function test_admin_pages_load_for_a_signed_in_user(): void
    {
        $this->medicine();
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertOk();
        $this->get(MedicineResource::getUrl('index'))->assertOk()->assertSee('Paracetamol Tablets');
        $this->get(SaleResource::getUrl('create'))->assertOk();
    }
}
