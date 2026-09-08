<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordCenterCRUDTest extends TestCase
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

    public function test_landlord_can_list_centers_paginated_with_search(): void
    {
        Center::create([
            'name' => 'Alpha Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        Center::create([
            'name' => 'Beta Center',
            'phone' => '+201011113333',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/centers?search=Alpha');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Alpha Center');
    }

    public function test_landlord_can_create_new_center_with_media(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('logo.png');
        $license = UploadedFile::fake()->create('license.pdf', 100, 'application/pdf');

        $payload = [
            'name' => 'Gamma Medical Center',
            'phone' => '+201011114444',
            'admin_name' => 'Gamma Admin User',
            'admin_phone' => '+201011115555',
            'admin_email' => 'admin@gammacenter.com',
            'admin_password' => 'password123',
            'specialty' => 'Pediatric Therapy',
            'country' => 'Egypt',
            'city' => 'Giza',
            'therapists_count' => 10,
            'branches_count' => 3,
            'referral_source' => 'Google Search',
            'status' => 'active',
            'subscription_status' => 'active',
            'logo' => $logo,
            'license_document' => $license,
        ];

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson('/api/landlord/centers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Gamma Medical Center')
            ->assertJsonPath('data.city', 'Giza')
            ->assertJsonPath('data.users.0.name', 'Gamma Admin User')
            ->assertJsonPath('data.users.0.roles.0', 'admin');

        $this->assertDatabaseHas('centers', [
            'name' => 'Gamma Medical Center',
            'city' => 'Giza',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Gamma Admin User',
            'email' => 'admin@gammacenter.com',
            'status' => 'active',
        ]);
    }

    public function test_landlord_can_show_single_center_details(): void
    {
        $center = Center::create([
            'name' => 'Delta Center',
            'phone' => '+201011115555',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/centers/{$center->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $center->id)
            ->assertJsonPath('data.name', 'Delta Center');
    }

    public function test_landlord_can_update_existing_center(): void
    {
        $center = Center::create([
            'name' => 'Old Name Center',
            'phone' => '+201011116666',
            'city' => 'Cairo',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->putJson("/api/landlord/centers/{$center->id}", [
                'name' => 'Updated Name Center',
                'phone' => '+201011116666',
                'status' => 'active',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name Center')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('centers', [
            'id' => $center->id,
            'name' => 'Updated Name Center',
            'status' => 'active',
        ]);
    }

    public function test_landlord_can_delete_center(): void
    {
        $center = Center::create([
            'name' => 'Center To Delete',
            'phone' => '+201011117777',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->deleteJson("/api/landlord/centers/{$center->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Center deleted successfully.');

        $this->assertDatabaseMissing('centers', [
            'id' => $center->id,
        ]);
    }

    public function test_landlord_can_get_center_staff_only(): void
    {
        $center = Center::create([
            'name' => 'Staff Center',
            'phone' => '+201011119000',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Center Admin User',
            'phone' => '+201011119001',
            'email' => 'admin@staffcenter.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $therapist = User::create([
            'center_id' => $center->id,
            'name' => 'Center Therapist User',
            'phone' => '+201011119002',
            'email' => 'therapist@staffcenter.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');

        $patient = User::create([
            'center_id' => $center->id,
            'name' => 'Center Patient User',
            'phone' => '+201011119003',
            'email' => 'patient@staffcenter.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patient->assignRole('patient');

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/centers/{$center->id}/staff");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_non_landlord_cannot_access_center_crud(): void
    {
        $center = Center::create([
            'name' => 'Protected Center',
            'phone' => '+201011118888',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $adminUser = User::create([
            'center_id' => $center->id,
            'name' => 'Regular Admin',
            'phone' => '+201011119999',
            'email' => 'admin@protected.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $adminUser->assignRole('admin');

        $response = $this->actingAs($adminUser, 'sanctum')
            ->getJson('/api/landlord/centers');

        $response->assertStatus(403);
    }
}
