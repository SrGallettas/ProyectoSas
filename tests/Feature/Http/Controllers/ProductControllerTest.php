<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $product = Product::factory()->create();

        $this->get(route('products.index'))->assertRedirect(route('login'));
        $this->get(route('products.create'))->assertRedirect(route('login'));
        $this->post(route('products.store'))->assertRedirect(route('login'));
        $this->get(route('products.edit', $product))->assertRedirect(route('login'));
        $this->put(route('products.update', $product))->assertRedirect(route('login'));
        $this->delete(route('products.destroy', $product))->assertRedirect(route('login'));
    }

    public function test_user_only_sees_products_from_active_business(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $activeProduct = Product::factory()->for($activeBusiness)->create([
            'name' => 'Producto visible',
        ]);
        $hiddenProduct = Product::factory()->for($otherBusiness)->create([
            'name' => 'Producto oculto',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->get(route('products.index'));

        $response
            ->assertOk()
            ->assertSee($activeProduct->name)
            ->assertDontSee($hiddenProduct->name);
    }

    public function test_product_name_is_escaped_in_listing(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        Product::factory()->for($activeBusiness)->create([
            'name' => '<script>alert("xss")</script>',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->get(route('products.index'));

        $response
            ->assertSee('&lt;script&gt;', escape: false)
            ->assertDontSee('<script>alert("xss")</script>', escape: false);
    }

    public function test_authenticated_user_can_view_creation_form(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->get(route('products.create'));

        $response->assertOk()->assertSee('Nuevo producto');
    }

    public function test_product_is_created_for_active_business(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->post(route('products.store'), [
                'name' => 'Café molido',
                'price' => '8.95',
                'stock' => '12',
                'business_id' => $otherBusiness->id,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'business_id' => $activeBusiness->id,
            'name' => 'Café molido',
            'price' => 8.95,
            'stock' => 12,
        ]);
        $this->assertDatabaseMissing('products', [
            'business_id' => $otherBusiness->id,
            'name' => 'Café molido',
        ]);
    }

    public function test_user_can_upload_product_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->post(route('products.store'), [
                'name' => 'Café fotografiado',
                'price' => '1.80',
                'image' => UploadedFile::fake()->createWithContent(
                    'cafe.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
                ),
            ]);

        $response->assertSessionHasNoErrors();
        $product = $activeBusiness->products()->sole();
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_cannot_use_category_from_another_business(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $foreignCategory = Category::factory()->create();

        $response = $this->actingAs($user)->withSession(['active_business_id' => $activeBusiness->id])->post(route('products.store'), [
            'name' => 'Café', 'price' => '1.50', 'category_id' => $foreignCategory->id,
        ]);

        $response->assertSessionHasErrors(['category_id' => 'La categoría seleccionada no pertenece al comercio activo.']);
        $this->assertDatabaseEmpty('products');
    }

    public function test_name_and_price_are_required(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->from(route('products.create'))
            ->post(route('products.store'), []);

        $response
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors([
                'name' => 'El nombre del producto es obligatorio.',
                'price' => 'El precio es obligatorio.',
            ]);
        $this->assertDatabaseEmpty('products');
    }

    public function test_negative_price_and_stock_are_rejected(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'Producto inválido',
                'price' => '-1',
                'stock' => '-2',
            ]);

        $response
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors([
                'price' => 'El precio no puede ser negativo.',
                'stock' => 'El stock no puede ser negativo.',
            ]);
        $this->assertDatabaseEmpty('products');
    }

    public function test_price_precision_and_integer_stock_are_validated(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'Producto inválido',
                'price' => '4.999',
                'stock' => '1.5',
            ]);

        $response
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors([
                'price' => 'El precio puede tener como máximo 2 decimales.',
                'stock' => 'El stock debe ser un número entero.',
            ]);
        $this->assertDatabaseEmpty('products');
    }

    public function test_user_can_view_edit_form_for_active_business_product(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $product = Product::factory()->for($activeBusiness)->create([
            'name' => 'Producto original',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->get(route('products.edit', $product));

        $response
            ->assertOk()
            ->assertSee('Editar producto')
            ->assertSee('Producto original');
    }

    public function test_user_can_update_active_business_product(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $product = Product::factory()->for($activeBusiness)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->put(route('products.update', $product), [
                'name' => 'Producto actualizado',
                'price' => '19.50',
                'stock' => '8',
                'business_id' => $otherBusiness->id,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'business_id' => $activeBusiness->id,
            'name' => 'Producto actualizado',
            'price' => 19.50,
            'stock' => 8,
        ]);
    }

    public function test_invalid_update_does_not_change_product(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $product = Product::factory()->for($activeBusiness)->create([
            'name' => 'Producto original',
            'price' => '10.00',
            'stock' => 5,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->from(route('products.edit', $product))
            ->put(route('products.update', $product), [
                'name' => '',
                'price' => '-1',
                'stock' => '-1',
            ]);

        $response
            ->assertRedirect(route('products.edit', $product))
            ->assertSessionHasErrors(['name', 'price', 'stock']);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Producto original',
            'price' => 10.00,
            'stock' => 5,
        ]);
    }

    public function test_user_can_delete_active_business_product(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $product = Product::factory()->for($activeBusiness)->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_business_id' => $activeBusiness->id])
            ->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertModelMissing($product);
    }

    public function test_product_from_another_business_cannot_be_modified(): void
    {
        $user = User::factory()->create();
        $activeBusiness = Business::factory()->for($user)->create();
        $otherBusiness = Business::factory()->for($user)->create();
        $otherProduct = Product::factory()->for($otherBusiness)->create([
            'name' => 'Producto protegido',
        ]);
        $session = ['active_business_id' => $activeBusiness->id];

        $this->actingAs($user)
            ->withSession($session)
            ->get(route('products.edit', $otherProduct))
            ->assertNotFound();
        $this->actingAs($user)
            ->withSession($session)
            ->put(route('products.update', $otherProduct), [
                'name' => 'Producto manipulado',
                'price' => '1.00',
            ])
            ->assertNotFound();
        $this->actingAs($user)
            ->withSession($session)
            ->delete(route('products.destroy', $otherProduct))
            ->assertNotFound();

        $this->assertDatabaseHas('products', [
            'id' => $otherProduct->id,
            'name' => 'Producto protegido',
        ]);
    }
}
