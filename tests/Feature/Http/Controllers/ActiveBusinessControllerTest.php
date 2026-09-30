<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveBusinessControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $business = Business::factory()->create();

        $response = $this->post(route('businesses.select', $business));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_select_their_business(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('businesses.select', $business));

        $response
            ->assertRedirect(route('businesses.index'))
            ->assertSessionHas('active_business_id', $business->id);
    }

    public function test_user_cannot_select_another_users_business(): void
    {
        $user = User::factory()->create();
        $otherBusiness = Business::factory()->create();

        $response = $this->actingAs($user)->post(route('businesses.select', $otherBusiness));

        $response
            ->assertNotFound()
            ->assertSessionMissing('active_business_id');
    }
}
