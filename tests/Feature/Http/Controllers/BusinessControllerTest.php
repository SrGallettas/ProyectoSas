<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('businesses.index'))->assertRedirect(route('login'));
        $this->get(route('businesses.create'))->assertRedirect(route('login'));
        $this->post(route('businesses.store'), ['name' => 'Mi tienda'])
            ->assertRedirect(route('login'));
    }

    public function test_user_only_sees_their_businesses(): void
    {
        $user = User::factory()->create();
        $ownBusiness = Business::factory()->for($user)->create(['name' => 'Comercio propio']);
        $otherBusiness = Business::factory()->create(['name' => 'Comercio ajeno']);

        $response = $this->actingAs($user)->get(route('businesses.index'));

        $response
            ->assertOk()
            ->assertSee($ownBusiness->name)
            ->assertDontSee($otherBusiness->name);
    }

    public function test_authenticated_user_can_view_creation_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('businesses.create'));

        $response->assertOk()->assertSee('Nuevo comercio');
    }

    public function test_authenticated_user_can_create_business(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->post(route('businesses.store'), [
            'name' => 'Librería Central',
            'user_id' => $otherUser->id,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('active_business_id')
            ->assertRedirect(route('businesses.index'));

        $this->assertDatabaseHas('businesses', [
            'name' => 'Librería Central',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('businesses', [
            'name' => 'Librería Central',
            'user_id' => $otherUser->id,
        ]);

        $this->assertSame(
            $user->businesses()->sole()->id,
            session('active_business_id')
        );
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('businesses.create'))
            ->post(route('businesses.store'), ['name' => '']);

        $response
            ->assertRedirect(route('businesses.create'))
            ->assertSessionHasErrors(['name' => 'El nombre del comercio es obligatorio.']);
        $this->assertDatabaseEmpty('businesses');
    }

    public function test_name_cannot_exceed_255_characters(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('businesses.create'))
            ->post(route('businesses.store'), ['name' => str_repeat('a', 256)]);

        $response
            ->assertRedirect(route('businesses.create'))
            ->assertSessionHasErrors(['name' => 'El nombre del comercio no puede superar los 255 caracteres.']);
        $this->assertDatabaseEmpty('businesses');
    }
}
