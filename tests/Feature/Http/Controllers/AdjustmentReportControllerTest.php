<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdjustmentReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_only_adjustments_from_active_business(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $otherBusiness = Business::factory()->create();
        $sale = Sale::factory()->for($business)->create(['voided_at' => now(), 'void_reason' => 'Error visible']);
        $otherSale = Sale::factory()->for($otherBusiness)->create();
        Refund::query()->create(['business_id' => $otherBusiness->id, 'sale_id' => $otherSale->id, 'total' => 10, 'payment_method' => 'cash', 'reason' => 'Dato privado', 'refunded_at' => now()]);

        $this->actingAs($owner)->withSession(['active_business_id' => $business->id])->get(route('adjustments.index'))
            ->assertOk()->assertSee('Error visible')->assertDontSee('Dato privado');
        $this->assertNotNull($sale->voided_at);
    }

    public function test_staff_cannot_view_adjustment_report(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $business->members()->attach($staff, ['role' => Business::ROLE_STAFF, 'is_active' => true]);

        $this->actingAs($staff)->withSession(['active_business_id' => $business->id])->get(route('adjustments.index'))->assertForbidden();
    }
}
