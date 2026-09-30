<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RefundControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_partially_refund_sale_and_restore_stock(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $business->members()->attach($manager, ['role' => Business::ROLE_MANAGER, 'is_active' => true]);
        $product = Product::factory()->for($business)->create(['price' => '2.50', 'stock' => 10]);
        $session = ['active_business_id' => $business->id];
        $this->actingAs($manager)->withSession($session)->post(route('sales.store'), ['checkout_token' => (string) Str::uuid(), 'payment_method' => Sale::PAYMENT_CASH, 'products' => [$product->id => 3]]);
        $sale = $business->sales()->with('lines')->sole();
        $line = $sale->lines->sole();

        $this->actingAs($manager)->withSession($session)->post(route('sales.refunds.store', $sale), ['lines' => [$line->id => 2], 'payment_method' => 'cash', 'reason' => 'Producto devuelto'])->assertSessionHasNoErrors();

        $refund = $sale->refunds()->with('lines')->sole();
        $this->assertSame('5.00', $refund->total);
        $this->assertSame(2, $refund->lines->sole()->quantity);
        $this->assertSame(9, $product->refresh()->stock);
        $this->assertDatabaseHas('audit_logs', ['business_id' => $business->id, 'action' => 'refund.created', 'subject_id' => $refund->id]);
        $this->actingAs($owner)->withSession($session)->get(route('dashboard'))->assertViewHas('revenue', 2.5);
    }

    public function test_sale_cannot_be_refunded_beyond_original_quantity(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $product = Product::factory()->for($business)->create(['stock' => 5]);
        $session = ['active_business_id' => $business->id];
        $this->actingAs($owner)->withSession($session)->post(route('sales.store'), ['checkout_token' => (string) Str::uuid(), 'payment_method' => 'card', 'products' => [$product->id => 1]]);
        $sale = $business->sales()->with('lines')->sole();
        $line = $sale->lines->sole();
        $payload = ['lines' => [$line->id => 1], 'payment_method' => 'card', 'reason' => 'Producto devuelto'];
        $this->actingAs($owner)->withSession($session)->post(route('sales.refunds.store', $sale), $payload)->assertSessionHasNoErrors();

        $this->actingAs($owner)->withSession($session)->post(route('sales.refunds.store', $sale), $payload)->assertSessionHasErrors("lines.{$line->id}");
        $this->assertSame(1, $sale->refunds()->count());
    }
}
