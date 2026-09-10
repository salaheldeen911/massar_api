<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\DevSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FrontendIssuesFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DevSeeder::class);
    }

    public function test_user_resource_contains_id_and_center_id(): void
    {
        $user = User::where('email', 'admin@massar.com')->first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.center_id', $user->center_id);
    }

    public function test_center_creation_accepts_null_city(): void
    {
        $landlord = User::where('email', 'landlord@massar.com')->first();

        $response = $this->actingAs($landlord, 'sanctum')
            ->postJson('/api/landlord/centers', [
                'name' => 'City Null Test Center',
                'phone' => '+201111111111',
                'admin_name' => 'Admin Null',
                'admin_phone' => '+201222222222',
                'admin_email' => 'nulladmin@test.com',
                'admin_password' => 'password123',
                // city omitted
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.city', 'Cairo'); // fallback default
    }

    public function test_dev_seeder_populates_all_four_role_accounts(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'landlord@massar.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@massar.com']);
        $this->assertDatabaseHas('users', ['email' => 'therapist@massar.com']);
        $this->assertDatabaseHas('users', ['email' => 'patient@massar.com']);
    }

    public function test_storage_link_exists(): void
    {
        $storageLinkPath = public_path('storage');
        $this->assertTrue(File::exists($storageLinkPath) || is_link($storageLinkPath));
    }
}
