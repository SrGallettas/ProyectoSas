<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('customers.index'))->assertRedirect(route('login'));
        $this->get(route('customers.create'))->assertRedirect(route('login'));
        $this->post(route('customers.store'))->assertRedirect(route('login'));
        $this->get(route('customers.edit', $customer))->assertRedirect(route('login'));
        $this->put(route('customers.update', $customer))->assertRedirect(route('login'));
        $this->delete(route('customers.destroy', $customer))->assertRedirect(route('login'));
    }

    public function test_user_only_sees_customers_from_active_business(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $visibleCustomer = Customer::factory()->for($activeBusiness)->create(['name' => 'Cliente visible']);
        $hiddenCustomer = Customer::factory()->for($otherBusiness)->create(['name' => 'Cliente oculto']);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->get(route('customers.index'));

        $response->assertOk()->assertSee($visibleCustomer->name)->assertDontSee($hiddenCustomer->name);
    }

    public function test_user_can_create_customer_for_active_business(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->post(route('customers.store'), [
            'name' => 'Ana García', 'email' => 'ana@example.com', 'phone' => '+34 600 123 123', 'business_id' => $otherBusiness->id,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['business_id' => $activeBusiness->id, 'name' => 'Ana García', 'email' => 'ana@example.com']);
        $this->assertDatabaseMissing('customers', ['business_id' => $otherBusiness->id, 'name' => 'Ana García']);
    }

    public function test_email_and_phone_are_optional(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->post(route('customers.store'), ['name' => 'Cliente sin contacto']);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['business_id' => $activeBusiness->id, 'name' => 'Cliente sin contacto', 'email' => null, 'phone' => null]);
    }

    public function test_customer_fields_are_validated(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->from(route('customers.create'))->post(route('customers.store'), [
            'name' => '', 'email' => 'correo-invalido', 'phone' => str_repeat('1', 31),
        ]);

        $response->assertRedirect(route('customers.create'))->assertSessionHasErrors([
            'name' => 'El nombre del cliente es obligatorio.',
            'email' => 'El correo electrónico no es válido.',
            'phone' => 'El teléfono no puede superar los 30 caracteres.',
        ]);
        $this->assertDatabaseEmpty('customers');
    }

    public function test_user_can_update_active_business_customer(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $customer = Customer::factory()->for($activeBusiness)->create();

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->put(route('customers.update', $customer), [
            'name' => 'Cliente actualizado', 'email' => 'nuevo@example.com', 'phone' => '911 222 333',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Cliente actualizado', 'email' => 'nuevo@example.com']);
    }

    public function test_user_can_delete_active_business_customer(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $customer = Customer::factory()->for($activeBusiness)->create();

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertModelMissing($customer);
    }

    public function test_customer_from_another_business_cannot_be_modified(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $otherCustomer = Customer::factory()->for($otherBusiness)->create(['name' => 'Cliente protegido']);
        $session = ['active_business_id' => $activeBusiness->id];

        $this->actingAs($user)->withSession($session)->get(route('customers.edit', $otherCustomer))->assertNotFound();
        $this->actingAs($user)->withSession($session)->put(route('customers.update', $otherCustomer), ['name' => 'Manipulado'])->assertNotFound();
        $this->actingAs($user)->withSession($session)->delete(route('customers.destroy', $otherCustomer))->assertNotFound();

        $this->assertDatabaseHas('customers', ['id' => $otherCustomer->id, 'name' => 'Cliente protegido']);
    }

    public function test_customer_content_is_escaped_in_listing(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        Customer::factory()->for($activeBusiness)->create(['name' => '<script>alert("xss")</script>']);

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->get(route('customers.index'));

        $response->assertSee('&lt;script&gt;', escape: false)->assertDontSee('<script>alert("xss")</script>', escape: false);
    }
}
