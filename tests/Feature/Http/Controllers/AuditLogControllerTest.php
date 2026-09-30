<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_only_their_business_activity(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $otherBusiness = Business::factory()->create();
        $business->auditLogs()->create(['user_id' => $owner->id, 'action' => 'member.updated', 'before' => ['role' => 'staff', 'is_active' => true], 'after' => ['role' => 'manager', 'is_active' => false]]);
        $otherBusiness->auditLogs()->create(['action' => 'private.action']);

        $this->actingAs($owner)->withSession(['active_business_id' => $business->id])->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('Acceso modificado')
            ->assertSee('Rol: Camarero → Encargado')
            ->assertSee('Acceso desactivado.')
            ->assertDontSee('&quot;is_active&quot;', false)
            ->assertDontSee('private.action');
    }

    public function test_non_owner_cannot_view_activity(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $business = Business::factory()->for($owner)->create();
        $business->members()->attach($manager, ['role' => Business::ROLE_MANAGER, 'is_active' => true]);

        $this->actingAs($manager)->withSession(['active_business_id' => $business->id])->get(route('audit-logs.index'))->assertForbidden();
    }

    public function test_audit_records_cannot_be_updated_or_deleted(): void
    {
        $business = Business::factory()->create();
        $log = $business->auditLogs()->create(['action' => 'member.updated']);

        try {
            $log->update(['action' => 'changed']);
            $this->fail('The audit log was updated.');
        } catch (LogicException) {
            $this->assertSame('member.updated', $log->fresh()->action);
        }

        $this->expectException(LogicException::class);
        $log->delete();
    }
}
