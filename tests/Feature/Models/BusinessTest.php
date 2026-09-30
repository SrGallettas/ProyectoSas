<?php

namespace Tests\Feature\Models;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_multiple_businesses(): void
    {
        $user = User::factory()->create();
        $businesses = Business::factory()->count(2)->for($user)->create();

        $this->assertCount(2, $user->businesses);
        $this->assertTrue($businesses->every(
            fn (Business $business): bool => $business->user->is($user)
        ));
    }

    public function test_business_belongs_only_to_its_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $business = Business::factory()->for($owner)->create();

        $this->assertTrue($business->user->is($owner));
        $this->assertFalse($business->user->is($otherUser));
    }

    public function test_deleting_user_also_deletes_business(): void
    {
        $business = Business::factory()->create();

        $business->user->delete();

        $this->assertModelMissing($business);
    }
}
