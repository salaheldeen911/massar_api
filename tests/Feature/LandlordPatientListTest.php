<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\PatientProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordPatientListTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlordUser;

    protected Center $center1;

    protected Center $center2;

    protected User $therapist1;

    protected User $therapist2;

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

        $this->center1 = Center::create([
            'name' => 'Cairo Physical Therapy Center',
            'phone' => '+201011112222',
            'city' => 'Cairo',
            'status' => 'active',
        ]);

        $this->center2 = Center::create([
            'name' => 'Alexandria Rehab Center',
            'phone' => '+201011113333',
            'city' => 'Alexandria',
            'status' => 'active',
        ]);

        $this->therapist1 = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Dr. Ahmed Therapist',
            'phone' => '+201011114444',
            'email' => 'ahmed@center1.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapist1->assignRole('therapist');

        $this->therapist2 = User::create([
            'center_id' => $this->center2->id,
            'name' => 'Dr. Mona Therapist',
            'phone' => '+201011115555',
            'email' => 'mona@center2.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->therapist2->assignRole('therapist');
    }

    public function test_landlord_can_list_all_patients_paginated(): void
    {
        $patientUser = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Kareem Patient',
            'phone' => '+201011116666',
            'email' => 'kareem@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser->assignRole('patient');

        PatientProfile::create([
            'user_id' => $patientUser->id,
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'birth_date' => '1995-05-15',
            'current_week' => 4,
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson('/api/landlord/patients');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Kareem Patient')
            ->assertJsonPath('data.0.therapist.name', 'Dr. Ahmed Therapist');
    }

    public function test_landlord_can_filter_patients_by_center_id(): void
    {
        $patientUser1 = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Cairo Patient',
            'phone' => '+201011117777',
            'email' => 'cairo@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser1->assignRole('patient');

        PatientProfile::create([
            'user_id' => $patientUser1->id,
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'birth_date' => '1990-01-01',
            'current_week' => 2,
        ]);

        $patientUser2 = User::create([
            'center_id' => $this->center2->id,
            'name' => 'Alex Patient',
            'phone' => '+201011118888',
            'email' => 'alex@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser2->assignRole('patient');

        PatientProfile::create([
            'user_id' => $patientUser2->id,
            'center_id' => $this->center2->id,
            'therapist_id' => $this->therapist2->id,
            'birth_date' => '1998-03-10',
            'current_week' => 1,
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/patients?center_id={$this->center1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Cairo Patient');
    }

    public function test_landlord_can_filter_patients_by_therapist_id(): void
    {
        $patientUser1 = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Therapist 1 Patient',
            'phone' => '+201011119999',
            'email' => 'p1@center1.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser1->assignRole('patient');

        PatientProfile::create([
            'user_id' => $patientUser1->id,
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'birth_date' => '1992-07-20',
            'current_week' => 5,
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/patients?therapist_id={$this->therapist1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Therapist 1 Patient');
    }

    public function test_landlord_can_show_single_patient_details(): void
    {
        $patientUser = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Detailed Patient',
            'phone' => '+201011110000',
            'email' => 'detailed@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser->assignRole('patient');

        $profile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'birth_date' => '2000-01-01',
            'current_week' => 10,
            'chief_complain' => 'Lower back pain',
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->getJson("/api/landlord/patients/{$profile->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $profile->id)
            ->assertJsonPath('data.name', 'Detailed Patient')
            ->assertJsonPath('data.chief_complain', 'Lower back pain');
    }

    public function test_landlord_can_create_patient_in_center_assigned_to_therapist(): void
    {
        $payload = [
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'name' => 'New Center Patient',
            'phone' => '+201011112345',
            'email' => 'newpatient@center1.com',
            'password' => 'password123',
            'birth_date' => '1996-08-12',
            'current_week' => 1,
            'chief_complain' => 'Shoulder pain',
        ];

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->postJson('/api/landlord/patients', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Center Patient')
            ->assertJsonPath('data.therapist.name', 'Dr. Ahmed Therapist')
            ->assertJsonPath('data.center.id', $this->center1->id);

        $this->assertDatabaseHas('users', [
            'name' => 'New Center Patient',
            'email' => 'newpatient@center1.com',
            'center_id' => $this->center1->id,
        ]);

        $this->assertDatabaseHas('patient_profiles', [
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'chief_complain' => 'Shoulder pain',
        ]);
    }

    public function test_landlord_can_update_patient_details_and_reassign_therapist(): void
    {
        $patientUser = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Updatable Patient',
            'phone' => '+201011113456',
            'email' => 'updatable@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser->assignRole('patient');

        $profile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'birth_date' => '1994-04-04',
            'current_week' => 2,
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->putJson("/api/landlord/patients/{$profile->id}", [
                'name' => 'Updated Patient Name',
                'therapist_id' => $this->therapist2->id,
                'current_week' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Patient Name')
            ->assertJsonPath('data.therapist.id', $this->therapist2->id)
            ->assertJsonPath('data.current_week', 5);

        $this->assertDatabaseHas('patient_profiles', [
            'id' => $profile->id,
            'therapist_id' => $this->therapist2->id,
            'current_week' => 5,
        ]);
    }

    public function test_landlord_can_delete_patient(): void
    {
        $patientUser = User::create([
            'center_id' => $this->center1->id,
            'name' => 'Patient To Delete',
            'phone' => '+201011114567',
            'email' => 'todelete@patient.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $patientUser->assignRole('patient');

        $profile = PatientProfile::create([
            'user_id' => $patientUser->id,
            'center_id' => $this->center1->id,
            'therapist_id' => $this->therapist1->id,
            'birth_date' => '1999-09-09',
            'current_week' => 1,
        ]);

        $response = $this->actingAs($this->landlordUser, 'sanctum')
            ->deleteJson("/api/landlord/patients/{$profile->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Patient deleted successfully.');

        $this->assertDatabaseMissing('patient_profiles', [
            'id' => $profile->id,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $patientUser->id,
        ]);
    }

    public function test_non_landlord_cannot_access_landlord_patients(): void
    {
        $response = $this->actingAs($this->therapist1, 'sanctum')
            ->getJson('/api/landlord/patients');

        $response->assertStatus(403);
    }
}
