<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\AdjustmentRequest;
use App\Models\Business;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdjustmentRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_request_requires_manager_approval_before_sale_is_voided(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $business->members()->attach($staff, ['role' => Business::ROLE_STAFF, 'is_active' => true]);
        $sale = Sale::factory()->for($business)->create();
        $session = ['active_business_id' => $business->id];

        $this->actingAs($staff)->withSession($session)->post(route('adjustment-requests.store'), ['sale_id' => $sale->id, 'type' => 'void', 'reason' => 'Cobro duplicado'])->assertSessionHasNoErrors();
        $adjustment = $business->adjustmentRequests()->sole();
        $this->assertNull($sale->fresh()->voided_at);

        $this->actingAs($owner)->withSession($session)->post(route('sales.void', $sale), ['adjustment_request_id' => $adjustment->id, 'reason' => $adjustment->payload['reason']])->assertSessionHasNoErrors();

        $this->assertNotNull($sale->fresh()->voided_at);
        $adjustment->refresh();
        $this->assertSame('approved', $adjustment->status);
        $this->assertSame($owner->id, $adjustment->reviewed_by_user_id);
    }

    public function test_manager_can_reject_request_without_changing_sale(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $sale = Sale::factory()->for($business)->create();
        $adjustment = $business->adjustmentRequests()->create(['sale_id' => $sale->id, 'requested_by_user_id' => $owner->id, 'type' => 'void', 'payload' => ['reason' => 'Posible error'], 'status' => AdjustmentRequest::STATUS_PENDING]);

        $this->actingAs($owner)->withSession(['active_business_id' => $business->id])->post(route('adjustment-requests.reject', $adjustment), ['review_note' => 'Venta correcta'])->assertSessionHasNoErrors();

        $this->assertSame('rejected', $adjustment->fresh()->status);
        $this->assertNull($sale->fresh()->voided_at);
    }
}
