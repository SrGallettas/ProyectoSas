<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InicioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_active_business_is_redirected_to_business_selection(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('businesses.index'));
    }

    public function test_panel_shows_zero_summary_when_business_has_no_sales_today(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create(['name' => 'Café Central']);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('dashboard'));

        $response
            ->assertViewHas('revenue', 0.0)
            ->assertViewHas('ticketCount', 0)
            ->assertViewHas('averageTicket', 0.0)
            ->assertSee('Café Central')
            ->assertSee('No hay ventas en este periodo');
    }

    public function test_panel_calculates_today_summary_and_payment_breakdown(): void
    {
        $this->travelTo('2026-09-28 14:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        Sale::factory()->for($business)->create([
            'total' => '12.50',
            'payment_method' => Sale::PAYMENT_CASH,
            'sold_at' => now()->subHour(),
        ]);
        Sale::factory()->for($business)->create([
            'total' => '7.50',
            'payment_method' => Sale::PAYMENT_CARD,
            'sold_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('dashboard'));

        $response
            ->assertViewHas('revenue', 20.0)
            ->assertViewHas('ticketCount', 2)
            ->assertViewHas('averageTicket', 10.0)
            ->assertViewHas('summary', function (Sale $summary): bool {
                return (float) $summary->cash_revenue === 12.5
                    && (float) $summary->card_revenue === 7.5
                    && (int) $summary->cash_ticket_count === 1
                    && (int) $summary->card_ticket_count === 1;
            })
            ->assertSee('20,00 €')
            ->assertSee('10,00 €')
            ->assertSee('Efectivo')
            ->assertSee('Tarjeta');
    }

    public function test_panel_excludes_sales_from_other_days_and_businesses(): void
    {
        $this->travelTo('2026-09-28 12:00:00');
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $visibleSale = Sale::factory()->for($activeBusiness)->create(['total' => '5.00', 'sold_at' => now()]);
        Sale::factory()->for($activeBusiness)->create(['total' => '90.00', 'sold_at' => now()->subDay()]);
        Sale::factory()->for($otherBusiness)->create(['total' => '200.00', 'sold_at' => now()]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->get(route('dashboard'));

        $response
            ->assertViewHas('revenue', 5.0)
            ->assertViewHas('ticketCount', 1)
            ->assertViewHas('recentSales', fn ($sales): bool => $sales->modelKeys() === [$visibleSale->id])
            ->assertSee('5,00 €')
            ->assertDontSee('90,00 €')
            ->assertDontSee('200,00 €');
    }

    public function test_panel_only_lists_ten_most_recent_sales(): void
    {
        $this->travelTo('2026-09-28 18:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $oldestSale = Sale::factory()->for($business)->create(['sold_at' => now()->subHours(11)]);
        Sale::factory()->count(10)->for($business)->sequence(
            fn ($sequence): array => ['sold_at' => now()->subHours(10 - $sequence->index)],
        )->create();

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('dashboard'));

        $response->assertViewHas('recentSales', function ($sales) use ($oldestSale): bool {
            return $sales->count() === 10 && ! in_array($oldestSale->id, $sales->modelKeys(), true);
        });
    }

    public function test_panel_can_show_complete_previous_month(): void
    {
        $this->travelTo('2026-10-15 12:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        Sale::factory()->for($business)->create(['total' => '10.00', 'sold_at' => '2026-09-01 08:00:00']);
        Sale::factory()->for($business)->create(['total' => '25.00', 'sold_at' => '2026-09-30 22:00:00']);
        Sale::factory()->for($business)->create(['total' => '100.00', 'sold_at' => '2026-10-01 08:00:00']);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('dashboard', ['period' => 'month', 'date' => '2026-09']));

        $response
            ->assertViewHas('period', 'month')
            ->assertViewHas('revenue', 35.0)
            ->assertViewHas('ticketCount', 2)
            ->assertSee('Septiembre de 2026')
            ->assertSee('35,00 €')
            ->assertDontSee('100,00 €');
    }

    public function test_invalid_period_parameters_fall_back_to_current_day(): void
    {
        $this->travelTo('2026-09-28 12:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        Sale::factory()->for($business)->create(['total' => '8.00', 'sold_at' => now()]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('dashboard', ['period' => 'unexpected', 'date' => 'not-a-date']));

        $response
            ->assertViewHas('period', 'day')
            ->assertViewHas('revenue', 8.0)
            ->assertViewHas('selectedDate', fn ($date): bool => $date->format('Y-m-d') === '2026-09-28');
    }

    public function test_panel_shows_top_products_for_period_and_current_low_stock(): void
    {
        $this->travelTo('2026-09-28 12:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $coffee = Product::factory()->for($business)->create(['name' => 'Café especial', 'stock' => 3]);
        Product::factory()->for($business)->create(['name' => 'Producto con stock', 'stock' => 6]);
        Product::factory()->for($otherBusiness)->create(['name' => 'Producto ajeno agotado', 'stock' => 0]);
        $sale = Sale::factory()->for($business)->create(['sold_at' => now()]);
        $sale->lines()->create([
            'product_id' => $coffee->id,
            'product_name' => $coffee->name,
            'quantity' => 4,
            'unit_price' => '2.00',
            'line_total' => '8.00',
        ]);
        $oldSale = Sale::factory()->for($business)->create(['sold_at' => now()->subDay()]);
        $oldSale->lines()->create([
            'product_id' => $coffee->id,
            'product_name' => $coffee->name,
            'quantity' => 20,
            'unit_price' => '2.00',
            'line_total' => '40.00',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('dashboard'));

        $response
            ->assertViewHas('topProducts', function ($products): bool {
                return $products->count() === 1
                    && $products->first()->product_name === 'Café especial'
                    && (int) $products->first()->units_sold === 4;
            })
            ->assertViewHas('lowStockProducts', fn ($products): bool => $products->modelKeys() === [$coffee->id])
            ->assertSee('Café especial')
            ->assertDontSee('Producto ajeno agotado');
    }
}
