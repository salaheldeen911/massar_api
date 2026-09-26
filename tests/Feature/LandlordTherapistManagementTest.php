<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;

use App\Models\Center;
use App\Models\TherapistProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordTherapistManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlordUser;
    protected Center $centerA;
    protected Center $centerB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->landlordUser = User::create([
            'name' => 'Super Landlord',
            'phone' => '+201099999999',
            'email' => 'landlord@massar.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'center_id' => null,
        ]);
        $this->landlordUser->assignRole('landlord');

        $this->centerA = Center::create([
            'name' => 'Alpha Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->centerB = Center::create([
            'name' => 'Beta Center',
            'phone' => '+201033334444',
            'city' => 'Giza',
            'status' => 'active',
        ]);
    }

    public function test_landlord_can_list_therapists_with_filters(): void
    {
        $therapistA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Ahmed Ali',
            'phone' => '+201011110001',
            'email' => 'ahmed@alpha.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $therapistA->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapistA->id,
            'specialization' => 'Orthopedic Physical Therapy',
            'license_no' => 'LIC-1001',
        ]);

        $therapistB = User::create([
            'center_id' => $this->centerB->id,
            'name' => 'Dr. Sarah Smith',
            'phone' => '+201011110002',
            'email' => 'sarah@beta.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $therapistB->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapistB->id,
            'specialization' => 'Pediatric Physical Therapy',
            'license_no' => 'LIC-2002',
        ]);

        // 1. List all
        $responseAll = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/therapists');

        $responseAll->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        // 2. Filter by center_id
        $responseFilterCenter = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/therapists?center_id={$this->centerA->id}");

        $responseFilterCenter->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Dr. Ahmed Ali');

        // 3. Search by name or specialization
        $responseSearch = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/therapists?search=Pediatric');

        $responseSearch->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Dr. Sarah Smith');
    }

    public function test_landlord_can_create_therapist_for_specific_center_without_role_parameter(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $payload = [
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Mahmoud Hassan',
            'email' => 'mahmoud@alpha.com',
            'phone' => '+201011118888',
            'password' => 'secret1234',
            'status' => 'active',
            'specialization' => 'Sports Rehabilitation',
            'license_no' => 'LIC-9988',
            'bio' => 'Senior Sports Physiotherapist with 10+ years experience.',
            'avatar' => $avatar,
        ];

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson('/api/landlord/therapists', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Dr. Mahmoud Hassan')
            ->assertJsonPath('data.email', 'mahmoud@alpha.com')
            ->assertJsonPath('data.center_id', $this->centerA->id)
            ->assertJsonPath('data.role', 'therapist')
            ->assertJsonPath('data.specialization', 'Sports Rehabilitation')
            ->assertJsonPath('data.license_no', 'LIC-9988');

        $createdUserId = $response->json('data.id');
        $createdUser = User::find($createdUserId);

        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('therapist'));
        $this->assertEquals($this->centerA->id, $createdUser->center_id);

        $this->assertDatabaseHas('therapist_profiles', [
            'user_id' => $createdUserId,
            'specialization' => 'Sports Rehabilitation',
            'license_no' => 'LIC-9988',
        ]);
    }

    public function test_landlord_can_view_single_therapist_details(): void
    {
        $therapist = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Omar Khaled',
            'phone' => '+201011117777',
            'email' => 'omar@alpha.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist->id,
            'specialization' => 'Neurological Rehabilitation',
            'license_no' => 'LIC-7777',
            'bio' => 'Neuro therapist bio',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/therapists/{$therapist->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $therapist->id)
            ->assertJsonPath('data.name', 'Dr. Omar Khaled')
            ->assertJsonPath('data.specialization', 'Neurological Rehabilitation');
    }

    public function test_landlord_can_update_therapist_profile_and_user_fields(): void
    {
        $therapist = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. Old Name',
            'phone' => '+201011116666',
            'email' => 'old@alpha.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist->id,
            'specialization' => 'Old Specialization',
            'license_no' => 'LIC-OLD',
        ]);

        $updatePayload = [
            'name' => 'Dr. Updated Name',
            'email' => 'updated@alpha.com',
            'specialization' => 'Updated Specialization',
            'license_no' => 'LIC-NEW',
        ];

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->putJson("/api/landlord/therapists/{$therapist->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Dr. Updated Name')
            ->assertJsonPath('data.email', 'updated@alpha.com')
            ->assertJsonPath('data.specialization', 'Updated Specialization');

        $this->assertDatabaseHas('users', [
            'id' => $therapist->id,
            'name' => 'Dr. Updated Name',
            'email' => 'updated@alpha.com',
            'center_id' => $this->centerA->id, // Center remained untouched
        ]);

        $this->assertDatabaseHas('therapist_profiles', [
            'user_id' => $therapist->id,
            'specialization' => 'Updated Specialization',
            'license_no' => 'LIC-NEW',
        ]);
    }

    public function test_landlord_can_delete_therapist(): void
    {
        $therapist = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Dr. To Delete',
            'phone' => '+201011115555',
            'email' => 'delete@alpha.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $therapist->assignRole('therapist');
        TherapistProfile::create([
            'user_id' => $therapist->id,
            'specialization' => 'Temp Spec',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->deleteJson("/api/landlord/therapists/{$therapist->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $therapist->id]);
        $this->assertDatabaseMissing('therapist_profiles', ['user_id' => $therapist->id]);
    }

    public function test_non_landlord_cannot_access_landlord_therapist_management(): void
    {
        $admin = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Regular Admin',
            'phone' => '+201011114444',
            'email' => 'admin@alpha.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/landlord/therapists');

        $response->assertStatus(403);
    }
}
