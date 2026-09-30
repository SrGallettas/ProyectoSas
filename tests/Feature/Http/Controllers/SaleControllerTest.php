<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_a_cash_sale(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $product = Product::factory()->for($business)->create(['name' => 'Café', 'price' => '1.50', 'stock' => 10]);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $business->id])->post(route('sales.store'), [
            'checkout_token' => (string) Str::uuid(),
            'payment_method' => Sale::PAYMENT_CASH,
            'products' => [$product->id => 2],
        ]);

        $sale = $business->sales()->sole();

        $response->assertSessionHasNoErrors()->assertRedirect(route('sales.show', $sale));
        $this->assertSame('3.00', $sale->total);
        $this->assertSame(Sale::PAYMENT_CASH, $sale->payment_method);
        $this->assertTrue($sale->user->is($user));
        $this->assertSame(8, $product->refresh()->stock);
        $this->assertDatabaseHas('sale_lines', ['sale_id' => $sale->id, 'product_name' => 'Café', 'quantity' => 2, 'line_total' => 3.00]);
    }

    public function test_user_can_complete_a_card_sale(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $product = Product::factory()->for($business)->create();

        $this->actingAs($user)->withSession(['active_business_id' => $business->id])->post(route('sales.store'), [
            'checkout_token' => (string) Str::uuid(),
            'payment_method' => Sale::PAYMENT_CARD,
            'products' => [$product->id => 1],
        ])->assertSessionHasNoErrors();

        $this->assertSame(Sale::PAYMENT_CARD, $business->sales()->sole()->payment_method);
    }

    public function test_payment_method_is_required_and_validated(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $product = Product::factory()->for($business)->create();
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->post(route('sales.store'), [
            'checkout_token' => (string) Str::uuid(),
            'products' => [$product->id => 1],
        ])->assertSessionHasErrors(['payment_method' => 'Selecciona efectivo o tarjeta para completar el cobro.']);

        $this->actingAs($user)->withSession($session)->post(route('sales.store'), [
            'checkout_token' => (string) Str::uuid(),
            'payment_method' => 'transfer',
            'products' => [$product->id => 1],
        ])->assertSessionHasErrors(['payment_method' => 'El método de pago seleccionado no es válido.']);

        $this->assertDatabaseEmpty('sales');
    }

    public function test_repeated_checkout_token_does_not_duplicate_sale_or_stock_discount(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $product = Product::factory()->for($business)->create(['stock' => 5]);
        $payload = [
            'checkout_token' => (string) Str::uuid(),
            'payment_method' => Sale::PAYMENT_CASH,
            'products' => [$product->id => 2],
        ];
        $session = ['active_business_id' => $business->id];

        $firstResponse = $this->actingAs($user)->withSession($session)->post(route('sales.store'), $payload);
        $secondResponse = $this->actingAs($user)->withSession($session)->post(route('sales.store'), $payload);
        $sale = $business->sales()->sole();

        $firstResponse->assertRedirect(route('sales.show', $sale));
        $secondResponse->assertRedirect(route('sales.show', $sale));
        $this->assertSame(1, $business->sales()->count());
        $this->assertSame(3, $product->refresh()->stock);
    }

    public function test_history_filters_sales_by_date_customer_payment_and_ticket(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $customer = Customer::factory()->for($business)->create();
        $matchingSale = Sale::factory()->for($business)->for($customer)->create(['payment_method' => Sale::PAYMENT_CARD, 'sold_at' => '2026-09-20 10:00:00']);
        Sale::factory()->for($business)->for($customer)->create(['payment_method' => Sale::PAYMENT_CASH, 'sold_at' => '2026-09-20 11:00:00']);
        Sale::factory()->for($business)->create(['payment_method' => Sale::PAYMENT_CARD, 'sold_at' => '2026-08-20 10:00:00']);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $business->id])->get(route('sales.index', [
            'date_from' => '2026-09-01',
            'date_to' => '2026-09-30',
            'customer_id' => $customer->id,
            'payment_method' => Sale::PAYMENT_CARD,
            'ticket_id' => $matchingSale->id,
        ]));

        $response->assertViewHas('sales', fn ($sales): bool => $sales->modelKeys() === [$matchingSale->id]);
    }

    public function test_filtered_sales_can_be_exported_as_csv_without_other_business_data(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $sale = Sale::factory()->for($business)->for($user)->create(['total' => '12.50', 'payment_method' => Sale::PAYMENT_CARD, 'sold_at' => '2026-09-20 10:00:00']);
        Sale::factory()->for($otherBusiness)->create(['total' => '999.00', 'sold_at' => '2026-09-20 10:00:00']);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $business->id])->get(route('sales.export', ['date_from' => '2026-09-01', 'date_to' => '2026-09-30']));

        $response->assertDownload('ventas-'.$business->id.'-'.now()->format('Y-m-d').'.csv');

        $content = $response->streamedContent();
        $this->assertStringContainsString((string) $sale->id, $content);
        $this->assertStringContainsString($user->name, $content);
        $this->assertStringContainsString('12,50', $content);
        $this->assertStringNotContainsString('999,00', $content);
    }
}
