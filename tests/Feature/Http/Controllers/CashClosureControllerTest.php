<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashClosureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_see_legacy_closures_read_only_in_professional_cash_page(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $business->cashClosures()->create(['user_id' => $owner->id, 'business_date' => '2026-09-28', 'total_revenue' => 100, 'expected_cash' => 80, 'card_revenue' => 20, 'counted_cash' => 79, 'difference' => -1, 'ticket_count' => 10, 'closed_at' => now()]);

        $this->actingAs($owner)->withSession(['active_business_id' => $business->id])->get(route('cash-sessions.index'))
            ->assertOk()->assertSee('Histórico anterior de cierres diarios')->assertSee('-1,00 €');
    }

    public function test_legacy_cash_closure_routes_no_longer_exist(): void
    {
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('cash-closures.index'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('cash-closures.store'));
    }
}
