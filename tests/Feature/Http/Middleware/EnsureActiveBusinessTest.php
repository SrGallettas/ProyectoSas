<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureActiveBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_active_business_is_redirected_to_businesses(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('products.index'));

        $response
            ->assertRedirect(route('businesses.index'))
            ->assertSessionHas('status', 'Selecciona un comercio antes de continuar.');
    }

    public function test_foreign_business_cannot_be_used_as_active_business(): void
    {
        $user = User::factory()->create();
        $foreignBusiness = Business::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $foreignBusiness->id])
            ->get(route('products.index'));

        $response
            ->assertRedirect(route('businesses.index'))
            ->assertSessionMissing('active_business_id');
    }
}
