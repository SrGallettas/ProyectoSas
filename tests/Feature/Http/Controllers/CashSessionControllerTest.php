<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\CashMovement;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_cash_session_and_register_movements(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->post(route('cash-sessions.store'), ['opening_cash' => '100.00'])->assertSessionHasNoErrors();
        $this->actingAs($user)->withSession($session)->post(route('cash-movements.store'), ['type' => CashMovement::TYPE_OUTPUT, 'amount' => '25.00', 'reason' => 'Pago proveedor'])->assertSessionHasNoErrors();

        $cashSession = $business->cashSessions()->sole();
        $this->assertSame('100.00', $cashSession->opening_cash);
        $this->assertDatabaseHas('cash_movements', ['cash_session_id' => $cashSession->id, 'user_id' => $user->id, 'type' => 'output', 'amount' => 25, 'reason' => 'Pago proveedor']);
        $this->assertDatabaseHas('audit_logs', ['business_id' => $business->id, 'action' => 'cash_session.opened']);
        $this->assertDatabaseHas('audit_logs', ['business_id' => $business->id, 'action' => 'cash_movement.created']);
    }

    public function test_expected_cash_combines_opening_sales_inputs_and_outputs(): void
    {
        $this->travelTo('2026-09-30 08:00:00');
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $session = ['active_business_id' => $business->id];
        $this->actingAs($user)->withSession($session)->post(route('cash-sessions.store'), ['opening_cash' => '100.00']);
        Sale::factory()->for($business)->create(['payment_method' => Sale::PAYMENT_CASH, 'total' => '50.00', 'sold_at' => now()->addHour()]);
        Sale::factory()->for($business)->create(['payment_method' => Sale::PAYMENT_CARD, 'total' => '80.00', 'sold_at' => now()->addHour()]);
        $this->actingAs($user)->withSession($session)->post(route('cash-movements.store'), ['type' => 'input', 'amount' => '20.00', 'reason' => 'Cambio']);
        $this->actingAs($user)->withSession($session)->post(route('cash-movements.store'), ['type' => 'output', 'amount' => '10.00', 'reason' => 'Retirada']);
        $this->travel(2)->hours();

        $this->actingAs($user)->withSession($session)->get(route('cash-sessions.index'))
            ->assertViewHas('expectedCash', 160.0);
    }

    public function test_business_cannot_have_two_open_sessions(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->post(route('cash-sessions.store'), ['opening_cash' => '20.00']);
        $this->actingAs($user)->withSession($session)->post(route('cash-sessions.store'), ['opening_cash' => '30.00'])->assertSessionHasErrors('opening_cash');
        $this->assertSame(1, $business->cashSessions()->count());
    }

    public function test_employee_can_close_session_and_movements_are_then_blocked(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $session = ['active_business_id' => $business->id];
        $this->actingAs($user)->withSession($session)->post(route('cash-sessions.store'), ['opening_cash' => '100.00']);
        Sale::factory()->for($business)->create(['payment_method' => Sale::PAYMENT_CASH, 'total' => '40.00', 'sold_at' => now()]);

        $this->actingAs($user)->withSession($session)->post(route('cash-sessions.close'), ['closing_cash' => '138.00'])->assertSessionHasNoErrors();

        $cashSession = $business->cashSessions()->sole();
        $this->assertSame('closed', $cashSession->status);
        $this->assertSame('140.00', $cashSession->expected_cash);
        $this->assertSame('138.00', $cashSession->closing_cash);
        $this->assertSame('-2.00', $cashSession->difference);
        $this->assertTrue($cashSession->closedBy->is($user));
        $this->assertDatabaseHas('audit_logs', ['business_id' => $business->id, 'action' => 'cash_session.closed', 'subject_id' => $cashSession->id]);

        $this->actingAs($user)->withSession($session)->post(route('cash-movements.store'), ['type' => 'input', 'amount' => '10.00', 'reason' => 'No permitido'])->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('cash_movements', ['reason' => 'No permitido']);
    }
}
