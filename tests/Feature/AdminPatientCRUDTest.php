<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\PatientProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPatientCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected Center $centerA;
    protected User $adminUserA;
    protected User $therapistA;

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

        $this->therapistA = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Therapist Alpha',
            'phone' => '+201011113333',
            'email' => 'therapist@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapistA->assignRole('therapist');
    }

    public function test_admin_can_list_center_patients_paginated_with_filters(): void
    {
        $patient1 = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Patient One',
            'phone' => '+201011114444',
            'email' => 'patient1@alpha.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patient1->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->centerA->id,
            'user_id' => $patient1->id,
            'therapist_id' => $this->therapistA->id,
            'birth_date' => '1990-01-01',
            'current_week' => 2,
        ]);

        $patient2 = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Patient Two',
            'phone' => '+201011115555',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patient2->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->centerA->id,
            'user_id' => $patient2->id,
            'birth_date' => '1995-05-05',
            'current_week' => 1,
        ]);

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson('/api/business/patients?search=One');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Patient One');
    }

    public function test_admin_can_create_patient_without_email_using_phone_only(): void
    {
        Storage::fake('media');

        $payload = [
            'name' => 'Ahmed Hassan',
            'phone' => '+201098765432',
            'password' => 'password123',
            'birth_date' => '1992-04-10',
            'therapist_id' => $this->therapistA->id,
            'current_week' => 3,
            'patient_history' => 'No major surgeries.',
            'chief_complain' => 'Lower back pain.',
            'diagnosis' => 'Lumbar strain.',
        ];

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson('/api/business/patients', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Ahmed Hassan')
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', '+201098765432')
            ->assertJsonPath('data.current_week', 3);

        $this->assertDatabaseHas('users', [
            'name' => 'Ahmed Hassan',
            'phone' => '+201098765432',
            'email' => null,
            'center_id' => $this->centerA->id,
        ]);

        $this->assertDatabaseHas('patient_profiles', [
            'center_id' => $this->centerA->id,
            'therapist_id' => $this->therapistA->id,
            'chief_complain' => 'Lower back pain.',
        ]);
    }

    public function test_admin_can_create_patient_with_file_uploads(): void
    {
        Storage::fake('media');

        $specialTestFile = UploadedFile::fake()->create('knee_mri.pdf', 100, 'application/pdf');

        $payload = [
            'name' => 'Sara Ali',
            'phone' => '+201011223344',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'birth_date' => '1998-08-20',
            'special_tests_file' => $specialTestFile,
        ];

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->postJson('/api/business/patients', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Sara Ali')
            ->assertJsonPath('data.email', 'sara@example.com');

        $this->assertNotNull($response->json('data.special_tests_file_url'));
    }

    public function test_admin_can_show_update_and_delete_patient(): void
    {
        $patient = User::create([
            'center_id' => $this->centerA->id,
            'name' => 'Original Name',
            'phone' => '+201077778888',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patient->assignRole('patient');
        PatientProfile::create([
            'center_id' => $this->centerA->id,
            'user_id' => $patient->id,
            'birth_date' => '1991-01-01',
        ]);

        // Show
        $showResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson("/api/business/patients/{$patient->id}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.header_info.name', 'Original Name');

        // Update
        $updateResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->putJson("/api/business/patients/{$patient->id}", [
                'name' => 'Updated Name',
            ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        // Delete
        $deleteResponse = $this->actingAs($this->adminUserA, 'sanctum')
            ->deleteJson("/api/business/patients/{$patient->id}");
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $patient->id]);
    }

    public function test_admin_cannot_access_or_modify_patient_from_another_center(): void
    {
        $centerB = Center::create([
            'name' => 'Beta Center',
            'phone' => '+201099998888',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $patientB = User::create([
            'center_id' => $centerB->id,
            'name' => 'Beta Patient',
            'phone' => '+201055556666',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientB->assignRole('patient');
        PatientProfile::create([
            'center_id' => $centerB->id,
            'user_id' => $patientB->id,
            'birth_date' => '1990-01-01',
        ]);

        $response = $this->actingAs($this->adminUserA, 'sanctum')
            ->getJson("/api/business/patients/{$patientB->id}");

        $response->assertStatus(422);
    }
}
