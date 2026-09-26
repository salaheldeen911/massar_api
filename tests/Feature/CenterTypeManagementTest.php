<?php

namespace Tests\Feature;

use App\Enums\CenterType;
use App\Models\Center;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CenterTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'therapist', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
    }

    public function test_can_fetch_registration_options(): void
    {
        $response = $this->getJson('/api/register/options');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.center_types.0.key', 'individual')
            ->assertJsonPath('data.center_types.1.key', 'institution');
    }

    public function test_can_register_individual_center_and_counts_are_forced_to_one(): void
    {
        $payload = [
            'center_name' => 'Solo Clinic',
            'type' => 'individual',
            'specialty' => 'Physical Therapy',
            'therapists_count' => 10,
            'branches_count' => 5,
            'terms_accepted' => true,
            'name' => 'Dr. Ahmed Solo',
            'email' => 'ahmed.solo@example.com',
            'phone' => '+201012345678',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.center.type', 'individual');

        $this->assertDatabaseHas('centers', [
            'name' => 'Solo Clinic',
            'type' => 'individual',
            'therapists_count' => 1,
            'branches_count' => 1,
        ]);
    }

    public function test_can_register_institution_center(): void
    {
        $payload = [
            'center_name' => 'Grand Hospital Center',
            'type' => 'institution',
            'specialty' => 'Rehabilitation',
            'therapists_count' => 8,
            'branches_count' => 2,
            'terms_accepted' => true,
            'name' => 'Manager Admin',
            'email' => 'manager@grandhospital.com',
            'phone' => '+201098765432',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.center.type', 'institution');

        $this->assertDatabaseHas('centers', [
            'name' => 'Grand Hospital Center',
            'type' => 'institution',
            'therapists_count' => 8,
            'branches_count' => 2,
        ]);
    }

    public function test_individual_center_cannot_create_additional_therapists(): void
    {
        $center = Center::create([
            'name' => 'Solo Center',
            'type' => CenterType::INDIVIDUAL,
            'phone' => '+201000000099',
            'status' => 'active',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Solo Owner',
            'email' => 'owner@solo.com',
            'phone' => '+201000000099',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum');

        $payload = [
            'name' => 'Second Specialist',
            'email' => 'therapist2@example.com',
            'phone' => '+201112223334',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/business/therapists', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('errors.therapist.0', 'Individual centers are not allowed to add additional therapists.');
    }

    public function test_individual_center_cannot_delete_therapist_or_self(): void
    {
        $center = Center::create([
            'name' => 'Solo Center 2',
            'type' => CenterType::INDIVIDUAL,
            'phone' => '+201000000098',
            'status' => 'active',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Solo Owner 2',
            'email' => 'owner2@solo.com',
            'phone' => '+201000000098',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum');

        $response = $this->deleteJson("/api/business/therapists/{$admin->id}");

        $response->assertStatus(422)
            ->assertJsonPath('errors.therapist.0', 'You cannot delete your own account.');
    }

    public function test_individual_center_patient_creation_auto_assigns_current_user_as_therapist(): void
    {
        $center = Center::create([
            'name' => 'Solo Center 3',
            'type' => CenterType::INDIVIDUAL,
            'phone' => '+201000000097',
            'status' => 'active',
        ]);

        $admin = User::create([
            'center_id' => $center->id,
            'name' => 'Solo Owner 3',
            'email' => 'owner3@solo.com',
            'phone' => '+201000000097',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum');

        $payload = [
            'name' => 'John Patient',
            'phone' => '+201233445566',
            'email' => 'patient.john@example.com',
            'password' => 'Password123!',
            'birth_date' => '1995-05-15',
        ];

        $response = $this->postJson('/api/business/patients', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('patient_profiles', [
            'center_id' => $center->id,
            'therapist_id' => $admin->id,
        ]);
    }

    public function test_landlord_can_create_and_filter_individual_center(): void
    {
        $landlord = User::create([
            'name' => 'Global Landlord',
            'email' => 'landlord.test@example.com',
            'phone' => '+201000000000',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord, 'sanctum');

        $payload = [
            'name' => 'Landlord Created Individual Clinic',
            'type' => 'individual',
            'phone' => '+201011223344',
            'admin_name' => 'Landlord Admin',
            'admin_phone' => '+201011223355',
            'admin_email' => 'landlordadmin@clinic.com',
            'admin_password' => 'Password123!',
        ];

        $createResponse = $this->postJson('/api/landlord/centers', $payload);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.type', 'individual')
            ->assertJsonPath('data.therapists_count', 1);

        $listResponse = $this->getJson('/api/landlord/centers?type=individual');

        $listResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $types = collect($listResponse->json('data'))->pluck('type');
        $this->assertContains('individual', $types);
    }
}
