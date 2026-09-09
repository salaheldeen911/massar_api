<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\TherapistProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTherapistCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected Center $centerA;
    protected User $adminUserA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->centerA = Center::create([
            'name' => 'Alpha Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->adminUserA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Center Admin Alpha',
            'phone' => '+201011110000',
            'email' => 'admin@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->adminUserA->assignRole('admin');
    }

    public function test_admin_can_list_center_therapists_paginated_with_filters(): void
    {
        $therapist1 = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Ahmed Ali',
            'phone' => '+201011113333',
            'email' => 'ahmed@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist1->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist1->id,
            'specialization' => 'Orthopedic Physical Therapy',
        ]);

        $therapist2 = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Sara Mohamed',
            'phone' => '+201011114444',
            'email' => 'sara@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist2->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist2->id,
            'specialization' => 'Neurological Rehabilitation',
        ]);

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson('/api/business/therapists?search=Ahmed');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Dr. Ahmed Ali');
    }

    public function test_admin_can_create_therapist_with_profile_and_avatar(): void
    {
        Storage::fake('media');

        $avatarFile = UploadedFile::fake()->image('doctor_avatar.jpg');

        $payload = [
            'name' => 'Dr. Khaled Mahmoud',
            'email' => 'khaled@alpha.com',
            'phone' => '+201099887766',
            'password' => 'password123',
            'specialization' => 'Pediatric Physical Therapy',
            'license_no' => 'LIC-998877',
            'bio' => 'Experienced specialist in pediatric rehab.',
            'avatar' => $avatarFile,
        ];

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson('/api/business/therapists', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Dr. Khaled Mahmoud')
            ->assertJsonPath('data.email', 'khaled@alpha.com')
            ->assertJsonPath('data.specialization', 'Pediatric Physical Therapy');

        $this->assertDatabaseHas('users', [
            'name' => 'Dr. Khaled Mahmoud',
            'email' => 'khaled@alpha.com',
            'center_id' => $this->centerA->id,
        ]);

        $this->assertDatabaseHas('therapist_profiles', [
            'specialization' => 'Pediatric Physical Therapy',
            'license_no' => 'LIC-998877',
        ]);

        $this->assertNotNull($response->json('data.avatar_url'));
    }

    public function test_admin_can_show_update_and_delete_therapist(): void
    {
        $therapist = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Original Therapist Name',
            'phone' => '+201077778888',
            'email' => 'original@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist->id,
            'specialization' => 'General PT',
        ]);

        // Show
        $showResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson("/api/business/therapists/{$therapist->id}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Original Therapist Name');

        // Update
        $updateResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->putJson("/api/business/therapists/{$therapist->id}", [
                'name' => 'Updated Therapist Name',
                'specialization' => 'Sports Rehab',
            ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Therapist Name')
            ->assertJsonPath('data.specialization', 'Sports Rehab');

        // Delete
        $deleteResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->deleteJson("/api/business/therapists/{$therapist->id}");
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $therapist->id]);
    }

    public function test_admin_cannot_access_or_modify_therapist_from_another_center(): void
    {
        $centerB = Center::create([
            'name' => 'Beta Center',
            'phone' => '+201099998888',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $therapistB = User::create([
            'center_id' => $centerB->id,
            'name' => 'Therapist Beta',
            'phone' => '+201055556666',
            'email' => 'therapist@beta.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $therapistB->assignRole('therapist');

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson("/api/business/therapists/{$therapistB->id}");

        $response->assertStatus(422);
    }
}
