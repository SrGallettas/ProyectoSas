<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashClosureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_calculates_daily_cash_summary(): void
    {
        $this->travelTo('2026-09-28 20:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        Sale::factory()->for($business)->create(['total' => '15.00', 'payment_method' => Sale::PAYMENT_CASH, 'sold_at' => now()]);
        Sale::factory()->for($business)->create(['total' => '20.00', 'payment_method' => Sale::PAYMENT_CARD, 'sold_at' => now()]);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $business->id])->get(route('cash-closures.index'));

        $response->assertViewHas('summary', function (Sale $summary): bool {
            return (float) $summary->total_revenue === 35.0
                && (float) $summary->expected_cash === 15.0
                && (float) $summary->card_revenue === 20.0
                && (int) $summary->ticket_count === 2;
        });
    }

    public function test_user_can_save_and_correct_cash_closure(): void
    {
        $this->travelTo('2026-09-28 20:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        Sale::factory()->for($business)->create(['total' => '15.00', 'payment_method' => Sale::PAYMENT_CASH, 'sold_at' => now()]);
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->post(route('cash-closures.store'), ['business_date' => '2026-09-28', 'counted_cash' => '14.50'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('cash-closures.index', ['date' => '2026-09-28']));
        $this->actingAs($user)->withSession($session)->post(route('cash-closures.store'), ['business_date' => '2026-09-28', 'counted_cash' => '15.00'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('cash-closures.index', ['date' => '2026-09-28']));

        $closure = $business->cashClosures()->sole();
        $this->assertSame('15.00', $closure->expected_cash);
        $this->assertSame('15.00', $closure->counted_cash);
        $this->assertSame('0.00', $closure->difference);
        $this->assertTrue($closure->user->is($user));
        $this->assertDatabaseHas('audit_logs', ['business_id' => $business->id, 'user_id' => $user->id, 'action' => 'cash_closure.corrected', 'subject_id' => $closure->id]);
    }

    public function test_closures_are_isolated_by_business(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $otherBusiness->cashClosures()->create(['business_date' => now()->toDateString(), 'total_revenue' => 100, 'expected_cash' => 100, 'card_revenue' => 0, 'counted_cash' => 100, 'difference' => 0, 'ticket_count' => 1, 'closed_at' => now()]);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->get(route('cash-closures.index'));

        $response->assertViewHas('closures', fn ($closures): bool => $closures->isEmpty());
    }
}
