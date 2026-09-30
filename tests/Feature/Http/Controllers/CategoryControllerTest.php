<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_categories_in_active_business(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->post(route('categories.store'), ['name' => 'Bebidas'])->assertRedirect(route('categories.index'));
        $category = $business->categories()->sole();
        $this->actingAs($user)->withSession($session)->put(route('categories.update', $category), ['name' => 'Bebidas frías'])->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Bebidas frías']);

        $this->actingAs($user)->withSession($session)->delete(route('categories.destroy', $category))->assertRedirect(route('categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_category_from_another_business_cannot_be_modified(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $otherCategory = Category::factory()->create();
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->get(route('categories.edit', $otherCategory))->assertNotFound();
        $this->actingAs($user)->withSession($session)->delete(route('categories.destroy', $otherCategory))->assertNotFound();
        $this->assertModelExists($otherCategory);
    }

    public function test_deleting_category_keeps_products_without_category(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        $category = Category::factory()->for($business)->create();
        $product = Product::factory()->for($business)->create(['category_id' => $category->id]);

        $this->actingAs($user)->withSession(['active_business_id' => $business->id])->delete(route('categories.destroy', $category));

        $this->assertNull($product->refresh()->category_id);
    }

    public function test_category_name_is_required_and_unique_per_business(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->for($user)->create();
        Category::factory()->for($business)->create(['name' => 'Cafés']);
        $session = ['active_business_id' => $business->id];

        $this->actingAs($user)->withSession($session)->post(route('categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->actingAs($user)->withSession($session)->post(route('categories.store'), ['name' => 'Cafés'])->assertSessionHasErrors(['name' => 'Ya existe una categoría con ese nombre.']);
    }
}
