<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordCenterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlordUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->landlordUser = User::create([
            'name' => 'Global Landlord',
            'phone' => '+201099999999',
            'email' => 'landlord@massar.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'center_id' => null,
        ]);
        $this->landlordUser->assignRole('landlord');
    }

    public function test_landlord_can_list_pending_center_applications(): void
    {
        Center::create([
            'name' => 'Pending Center 1',
            'phone' => '+201011111111',
            'city' => 'Cairo',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/centers/pending');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Pending Center 1');
    }

    public function test_landlord_can_approve_pending_center(): void
    {
        $center = Center::create([
            'name' => 'Approve Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'pending',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Admin To Approve',
            'phone' => '+201011113333',
            'email' => 'admin@approve.com',
            'password' => bcrypt('password123'),
            'status' => 'pending',
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson("/api/landlord/centers/{$center->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('center.status', 'active')
            ->assertJsonPath('center.subscription_status', 'active');

        $this->assertDatabaseHas('centers', [
            'id' => $center->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => 'active',
        ]);
    }

    public function test_landlord_can_reject_pending_center(): void
    {
        $center = Center::create([
            'name' => 'Reject Center',
            'phone' => '+201011114444',
            'city' => 'Cairo',
            'status' => 'pending',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Admin To Reject',
            'phone' => '+201011115555',
            'email' => 'admin@reject.com',
            'password' => bcrypt('password123'),
            'status' => 'pending',
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson("/api/landlord/centers/{$center->id}/reject", [
                'reason' => 'Incomplete center verification.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('center.status', 'rejected');

        $this->assertDatabaseHas('centers', [
            'id' => $center->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => 'inactive',
        ]);
    }

    public function test_non_landlord_cannot_access_landlord_subscription_routes(): void
    {
        $center = Center::create([
            'name' => 'Some Center',
            'phone' => '+201011116666',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $normalUser = User::create([
            'center_id' => $center->id,
            'name' => 'Normal Admin',
            'phone' => '+201011117777',
            'email' => 'normal@admin.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $normalUser->assignRole('admin');

        $response = $this->actingAs($normalUser, 'sanctum')
            ->getJson('/api/landlord/centers/pending');

        $response->assertStatus(403);
    }
}
