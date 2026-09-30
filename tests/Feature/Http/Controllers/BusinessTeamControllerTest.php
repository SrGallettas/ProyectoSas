<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_user_who_accepts_with_matching_email(): void
    {
        $owner = User::factory()->create();
        $employee = User::factory()->create(['email' => 'equipo@example.com']);
        $business = Business::factory()->for($owner)->create();

        $response = $this->actingAs($owner)->withSession(['active_business_id' => $business->id])
            ->post(route('team.invitations.store'), ['email' => 'equipo@example.com', 'role' => Business::ROLE_STAFF]);

        $response->assertSessionHasNoErrors()->assertSessionHas('invitation_url');
        $invitationUrl = $response->getSession()->get('invitation_url');

        $this->actingAs($employee)->get($invitationUrl)->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('business_user', ['business_id' => $business->id, 'user_id' => $employee->id, 'role' => Business::ROLE_STAFF, 'is_active' => true]);
    }

    public function test_invitation_cannot_be_accepted_by_a_different_email(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $response = $this->actingAs($owner)->withSession(['active_business_id' => $business->id])
            ->post(route('team.invitations.store'), ['email' => 'expected@example.com', 'role' => Business::ROLE_MANAGER]);

        $this->actingAs($otherUser)->get($response->getSession()->get('invitation_url'))->assertForbidden();
        $this->assertDatabaseMissing('business_user', ['business_id' => $business->id, 'user_id' => $otherUser->id]);
    }

    public function test_staff_can_sell_but_cannot_manage_catalogue_cash_or_team(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $business->members()->attach($staff, ['role' => Business::ROLE_STAFF, 'is_active' => true]);
        $session = ['active_business_id' => $business->id];

        $this->actingAs($staff)->withSession($session)->get(route('sales.create'))->assertOk();
        $this->actingAs($staff)->withSession($session)->get(route('products.index'))->assertForbidden();
        $this->actingAs($staff)->withSession($session)->get(route('cash-closures.index'))->assertForbidden();
        $this->actingAs($staff)->withSession($session)->get(route('team.index'))->assertForbidden();
    }
}
